<?php


namespace App\Security;


use App\Entity\Image;
use App\Entity\Role;
use App\Entity\TypeCompte;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\DiscordClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordAuthenticator extends SocialAuthenticator {
    private $clientRegistry;
    private $em;
    private $router;

    const SERVICE = 'discord';

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true

        // For Symfony lower than 3.4 the supports method need to be called manually here:
        // if (!$this->supports($request)) {
        //     return null;
        // }

        return $this->fetchAccessToken($this->getDiscordClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        /** @var DiscordResourceOwner $discordResourceOwner */
        $discordResourceOwner = $this->getDiscordClient()
            ->fetchUserFromToken($credentials);

        $type = $this->em->getRepository(TypeCompte::class)->findOneBy([ 'libelle' => self::SERVICE ]);
        $email = $discordResourceOwner->getEmail();

        if (empty($type)) {
            $type = new TypeCompte();
            $type->setLibelle(self::SERVICE);
            $this->em->persist($type);
            $this->em->flush();
        }

        // 1) have they logged in with Discord before? Easy!
        $utilisateurType = $this->em->getRepository(UtilisateurType::class)
            ->findOneBy(['identificateur' => $discordResourceOwner->getId()]);

        if (!empty($utilisateurType)) {
            $user = $utilisateurType->getIdUtilisateur();
        }else{
            $user = $this->em->getRepository(Utilisateur::class)
                ->findOneBy(['courriel' => $email]);

            if (empty($user)) {
                $i = new Image();
                $i->setCheminImage('https://cdn.discordapp.com/avatars/'. $discordResourceOwner->getId() .'/' . $discordResourceOwner->getAvatarHash() . '.png');

                $this->em->persist($i);
                $this->em->flush();

                $user = new Utilisateur();
                $user->setNom($discordResourceOwner->getUsername());
                $user->setCourriel($email);
                $user->setIdRole($this->em->getRepository(Role::class)->findOneBy([ 'meta' => 'ROLE_USER']));
                $user->setIdImage($i);

                $this->em->persist($user);
            }

            $utilisateurType = new UtilisateurType();
            $utilisateurType->setIdUtilisateur($user);
            $utilisateurType->setIdTypeCompte($type);
            $utilisateurType->setIdentificateur($discordResourceOwner->getId());

            $this->em->persist($utilisateurType);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return DiscordClient
     */
    private function getDiscordClient()
    {
        return $this->clientRegistry
            // "discord" is the key used in config/packages/knpu_oauth2_client.yaml
            ->getClient('discord');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('home');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

}