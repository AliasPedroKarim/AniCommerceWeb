<?php


namespace App\Security;

use App\Entity\Image;
use App\Entity\Role;
use App\Entity\TypeCompte;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticator extends SocialAuthenticator {
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    const SERVICE = 'google';

    /**
     * GoogleAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request) {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    /**
     * @param Request $request
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request) {
        // this method is only called if supports() returns true

        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return Utilisateur|null|object|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider) {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $type = $this->em->getRepository(TypeCompte::class)->findOneBy([ 'libelle' => self::SERVICE ]);
        $email = $googleUser->getEmail();

        if (empty($type)) {
            $type = new TypeCompte();
            $type->setLibelle(self::SERVICE);
            $this->em->persist($type);
            $this->em->flush();
        }

        // 1) have they logged in with Discord before? Easy!
        $utilisateurType = $this->em->getRepository(UtilisateurType::class)
            ->findOneBy(['identificateur' => $googleUser->getId()]);

        if (!empty($utilisateurType)) {
            $user = $utilisateurType->getIdUtilisateur();
        }else{
            $user = $this->em->getRepository(Utilisateur::class)
                ->findOneBy(['courriel' => $email]);

            if (empty($user)) {
                /** @var Utilisateur $user */
                $user = new Utilisateur();

                $i = new Image();
                $i->setCheminImage($googleUser->getAvatar());

                $this->em->persist($i);
                $this->em->flush();

                $user->setIdImage($i);
                $user->setNom($googleUser->getFirstName());
                $user->setPrenom($googleUser->getLastName());
                $user->setIdRole($this->em->getRepository(Role::class)->findOneBy([ 'meta' => 'ROLE_USER']));
                $user->setCourriel($email);

                $this->em->persist($user);
            }

            $utilisateurType = new UtilisateurType();
            $utilisateurType->setIdUtilisateur($user);
            $utilisateurType->setIdTypeCompte($type);
            $utilisateurType->setIdentificateur($googleUser->getId());

            $this->em->persist($utilisateurType);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return OAuth2ClientInterface
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
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