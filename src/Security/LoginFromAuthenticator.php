<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Entity\UtilisateurConfirmation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFromAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    private $request;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $this->request = $request;
        $credentials = [
            'courriel' => $request->request->get('courriel'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['courriel']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['courriel' => $credentials['courriel']]);

        if (!$user) {
            // fail authentication with a custom error
            // throw new CustomUserMessageAuthenticationException('Courriel could not be found.');
            throw new CustomUserMessageAuthenticationException('Votre email n\'existe pas !');
        }

        $confirmation = $this->entityManager->getRepository(UtilisateurConfirmation::class)->findOneBy(['idUtilisateur' => $user]);
        $session = new Session();
        if (!empty($confirmation)) {
            if ($confirmation->getToken() !== null) {

                throw new CustomUserMessageAuthenticationException('Votre compte n\'a pas été activer, Veuillez consulter vos mails et utiliser le lien d\'activation');
            }
        }else if (empty($confirmation)){

            $link = $this->urlGenerator->generate(
                'utilisateur_linkbroken', [
                    'utilisateur'=> $user->getId()
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $session->getFlashBag()->set(
                'danger',
                "<a href='$link'>cliquez ici</a>"
            );

            throw new CustomUserMessageAuthenticationException("On dirait que votre lien de confirmation est cassé, cliquez le lien ci dessous pour le renoulever !", [ 'link' => $link ]);
        }

        $captcha = checkCaptcha($this->request, 'login');
        if (isset($captcha['success']) && $captcha['success'] != true) {
            throw new CustomUserMessageAuthenticationException('Le captcha n\'est pas valide ! Veuillez recharger la page et ensuite réessayer.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param $credentials
     * @return string|null
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // return new RedirectResponse($this->urlGenerator->generate('ligne_commande_check'));
        return new RedirectResponse($this->urlGenerator->generate('home'));
        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }
}
