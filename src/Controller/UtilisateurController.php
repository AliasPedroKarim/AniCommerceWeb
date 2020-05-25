<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurConfirmation;
use App\Form\ImageType;
use App\Form\UploadType;
use App\Form\UtilisateurType;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\UtilisateurTypeRepository;
use App\Service\UtilisateurService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UtilisateurController extends AbstractController {
    // En heure donc 24h
    const TIME_LINK_EXPIRED = 24;

    const ERROR_CODES = [
        'missing-input-secret' => [
            'en' => 'The secret parameter is missing.',
            'fr' => 'Le paramètre secret est absent.'
        ],
        'invalid-input-secret' => [
            'en' => 'The secret parameter is invalid or malformed.',
            'fr' => 'Le paramètre secret est invalide ou malformé.'
        ],
        'missing-input-response' => [
            'en' => 'The response parameter is missing.',
            'fr' => 'Le paramètre de réponse est manquant.'
        ],
        'invalid-input-response' => [
            'en' => 'The response parameter is invalid or malformed.',
            'fr' => 'Le paramètre de réponse est invalide ou malformé.'
        ],
        'bad-request' => [
            'en' => 'The request is invalid or malformed.',
            'fr' => 'La demande est invalide ou malformée.'
        ],
        'timeout-or-duplicate' => [
            'en' => 'The response is no longer valid: either is too old or has been used previously.',
            'fr' => 'La réponse n\'est plus valable : soit elle est trop ancienne, soit elle a été utilisée auparavant.'
        ]
    ];

    /**
     * @Route("/utilisateur", name="utilisateur_index", methods={"GET"})
     *
     * @param UtilisateurRepository $utilisateurRepository
     * @return Response
     */
    public function index(UtilisateurRepository $utilisateurRepository): Response {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    /**
     * @Route("/register", name="utilisateur_register", methods={"GET","POST"})
     *
     * @param Request $request
     * @param RoleRepository $roleRepository
     * @param UtilisateurService $utilisateurService
     * @param Swift_Mailer $mailer
     * @return Response
     */
    public function register(Request $request, RoleRepository $roleRepository, UtilisateurService $utilisateurService, Swift_Mailer $mailer): Response {
        if ($this->getUser()) {
            $this->get('session')->getFlashBag()->set(
                'success',
                'Tu es déjà connecter !'
            );

            return $this->redirectToRoute('home');
        }

        $utilisateur = new Utilisateur();
        $image = new Image();
        $form = $this->createForm(UtilisateurType::class, $utilisateur, [
            'validation_groups' => ['Default', 'Register']
        ]);
        $form_image = $this->createForm(ImageType::class, $image, [
            'validation_groups' => ['Default', 'Register']
        ]);
        $form->handleRequest($request);
        $form_image->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form_image->isValid()) {
            $captcha = checkCaptcha($request, 'register');

            if (isset($captcha['success']) && !empty($captcha['success']) && $captcha['success'] === true) {
                $utilisateur->setIdRole($roleRepository->findOneBy(['meta' => 'ROLE_USER']));
                $utilisateur->setPlainPassword($utilisateur->getMotDePasse());

                $ei = $this->getDoctrine()->getManager();
                $ei->persist($image);
                $ei->flush();

                $utilisateur->setIdImage($image);

                // Ici je sauvegarde avec le cryptage l'utilisateur
                $utilisateurService->save($utilisateur);

                $this->generateConfirmation($mailer, $utilisateur);

                $this->get('session')->getFlashBag()->set(
                    'success',
                    'Votre compte a bien été créer, maintenant veuillez consulter vos emails pour activer le compte.'
                );

                return $this->redirectToRoute('app_login');
            } else {
                $messageErrors = '';

                if (isset($captcha['messages']) && is_array($captcha['messages'])) {
                    foreach ($captcha['messages'] as $message) {
                        $messageErrors .= isset(self::ERROR_CODES[$message]) ? self::ERROR_CODES[$message]['fr'] . "\n" : '';
                    }
                }

                $this->get('session')->getFlashBag()->set(
                    'captcha_error',
                    !empty($messageErrors) ? $messageErrors : 'Le captcha n\'est pas validé !'
                );
            }
        }

        return $this->render('utilisateur/register.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
            'form_image' => $form_image->createView(),
            'hide' => [ 'description' ]
        ]);
    }

    public function generateConfirmation(Swift_Mailer $mailer, Utilisateur $utilisateur) {
        $entityManager = $this->getDoctrine()->getManager();

        // Ici je créer la verification (confimation du compte)
        $token = bin2hex(openssl_random_pseudo_bytes(70));
        $comfirmation = new UtilisateurConfirmation();
        $comfirmation->setToken($token)
            ->setCreatedat(new \DateTime())
            ->setIdUtilisateur($utilisateur);

        $entityManager->persist($comfirmation);
        $entityManager->flush();

        $link = $this->generateUrl(
            'utilisateur_confirmation', [
            'token'=> $token
        ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new \Swift_Message("Comfirmation de votre compte"))
            ->setFrom("no-reply@coucou.fr")
            ->setTo($utilisateur->getCourriel())
            ->setBody(
                $this->renderView("layout/confirmation_account.html.twig",
                    ['link' => $link, 'utilisateur' => $utilisateur]),
                'text/html'
            );

        $mailer->send($message);
    }

    /**
     *
     * @Route("/utilisateur/verifcaptcha", name="utilisateur_verifcaptcha", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function verifCaptcha(Request $request) {
        $response = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode(checkCaptcha($request, 'api_captcha')));

        return $response;
    }

    /**
     * @Route("/utilisateur/confirmation/{token}", name="utilisateur_confirmation", methods={"GET"})
     *
     * @param Request $request
     * @param string $token
     * @return Response
     * @throws Exception
     */
    public function verifAccount(Request $request, $token) {
        if (!empty($token)) {
            $entityManager = $this->getDoctrine()->getManager();
            $confimation = $entityManager->getRepository(UtilisateurConfirmation::class)->findOneBy([ 'token' => $token ]);
            if (!empty($confimation)) {
                $date = new \DateTime();
                if ($date->diff($confimation->getCreatedat())->h < self::TIME_LINK_EXPIRED) {
                    $confimation->setToken(null);
                    $entityManager->persist($confimation);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        'Votre compte a bien été activer, vous pouvez vous connectez maintenant.'
                    );

                    return $this->redirectToRoute('app_login');
                }else{

                    $link = $this->generateUrl(
                        'utilisateur_reload', [
                        'token'=> $token
                    ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    // si le token à expirer proposer d'en renvoyer un nouveau
                    return $this->render('layout/verification_invalid.html.twig', [
                        'message' => 'Ah on dirait que ce lien d\'activation de compte à expirer ! Voulez-vous le renouveler ?',
                        'link' => $link,
                        'text_link' => 'Renvoyer'
                    ]);
                }
            }

            // token n'existe pas
            return $this->render('layout/verification_invalid.html.twig', [
                'message' => 'Attention ! Ce lien de confirmation est invalide.',
                'link' => $this->generateUrl('home'),
                'text_link' => 'Acceuil'
            ]);
        }

        // token pas envoyer
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/utilisateur/reload/{token}", name="utilisateur_reload", methods={"GET"})
     *
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param $token
     * @return Response
     * @throws Exception
     */
    public function resendVerifAccount(Request $request, Swift_Mailer $mailer, $token) {
        if (!empty($token)) {
            $entityManager = $this->getDoctrine()->getManager();
            $confimation = $entityManager->getRepository(UtilisateurConfirmation::class)->findOneBy([ 'token' => $token ]);
            if (!empty($confimation)) {
                $date = new \DateTime();
                if ($date->diff($confimation->getCreatedat())->h > self::TIME_LINK_EXPIRED) {
                    $utilisateur = $confimation->getIdUtilisateur();
                    $entityManager->delete($confimation);
                    $entityManager->flush();

                    $this->generateConfirmation($mailer, $utilisateur);

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        'Un nouveau mail de confirmation vient de vous être envoyer !'
                    );

                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/utilisateur/linkbroken/{utilisateur}", name="utilisateur_linkbroken", methods={"GET"})
     *
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param Utilisateur $utilisateur
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function confirmationBroke(Request $request, Swift_Mailer $mailer, Utilisateur $utilisateur) {
        $confimation = $this->getDoctrine()->getManager()->getRepository(UtilisateurConfirmation::class)->findOneBy([ 'idUtilisateur' => $utilisateur ]);

        if (empty($confimation)) {
            $this->generateConfirmation($mailer, $utilisateur);

            $this->get('session')->getFlashBag()->set(
                'success',
                'Un nouveau mail de confirmation vient de vous être envoyer !'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/utilisateur/{id}", name="utilisateur_show", methods={"GET"})
     *
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * @Route("/profile", name="utilisateur_profile", methods={"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function profile(Request $request): Response {
        if ($this->getUser()->isDisabled()) {
            return $this->redirectToRoute('home');
        }

        //on crée le formulaire
        $form = $this->createForm(UploadType::class);

        return $this->render('utilisateur/profile.html.twig', [
            'form' => $form->createView(),
            'utlisateur_type' => $this->getDoctrine()
                ->getManager()
                ->getRepository(\App\Entity\UtilisateurType::class)
                ->findBy([ 'idUtilisateur' => $this->getUser()->getId() ])
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/edit", name="utilisateur_edit", methods={"GET","POST"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UtilisateurService $utilisateurService
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function edit(Request $request, AuthorizationCheckerInterface $authorizationChecker, UtilisateurService $utilisateurService, Utilisateur $utilisateur): Response
    {
        if ($authorizationChecker->isGranted('ROLE_ADMIN') || $utilisateur === $this->getUser()) {
            $form = $this->createForm(UtilisateurType::class, $utilisateur, [
                'validation_groups' => ['Default']
            ]);

            $image = !empty($utilisateur->getIdImage()) ? $utilisateur->getIdImage() : new Image();
            $form_image = $this->createForm(ImageType::class, $image, [
                'validation_groups' => ['Default']
            ]);
            $form->handleRequest($request);
            $form_image->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid() && $form_image->isValid()) {
                $utilisateur->setPlainPassword($utilisateur->getMotDePasse());

                $ei = $this->getDoctrine()->getManager();
                $ei->persist($image);
                $ei->flush();

                $utilisateur->setIdImage($image);

                $utilisateurService->save($utilisateur);

                if ($authorizationChecker->isGranted('ROLE_ADMIN') && $utilisateur !== $this->getUser()) {
                    return $this->redirectToRoute('utilisateur_index');
                }else{
                    return $this->redirectToRoute('utilisateur_profile');
                }
            }

            return $this->render('utilisateur/edit.html.twig', [
                'utilisateur' => $utilisateur,
                'form' => $form->createView(),
                'form_image' => $form_image->createView(),
                'hide' => [ 'description' ]
            ]);
        }

        $this->get('session')->getFlashBag()->set(
            'danger',
            'Oops ! vous n\'avez pas la permission pour faire cette action.'
        );

        $link = $this->generateUrl(
            'utilisateur_profile', [ ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->redirect(!empty($request->headers->get('referer')) ? $request->headers->get('referer') : $link);
    }

    /**
     * @Route("/utilisateur/disable/{id}", name="utilisateur_disable", methods={"POST"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return RedirectResponse
     */
    public function disable(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur) {
        if ($authorizationChecker->isGranted('ROLE_ADMIN') || $utilisateur === $this->getUser()) {
            if ($this->isCsrfTokenValid('disabled'.$utilisateur->getId(), $request->request->get('_token'))) {
                if ($utilisateur->isDisabled() === false) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $utilisateur->setDisabled(true);

                    $entityManager->persist($utilisateur);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        'Votre compte à été désactiver !'
                    );
                }else{
                    $this->get('session')->getFlashBag()->set(
                        'warning',
                        'Votre compte est déjà desactiver !'
                    );
                }
            }
        }else {
            $this->get('session')->getFlashBag()->set(
                'danger',
                'Oops ! vous n\'avez pas la permission pour faire cette action.'
            );
        }

        return $this->redirectToRoute('utilisateur_profile');
    }

    /**
     * @Route("/utilisateur/enable/{id}", name="utilisateur_enable", methods={"POST"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return RedirectResponse
     */
    public function enable(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur) {
        if ($authorizationChecker->isGranted('ROLE_ADMIN') || $utilisateur === $this->getUser()) {
            if ($this->isCsrfTokenValid('enabled'.$utilisateur->getId(), $request->request->get('_token'))) {
                if ($utilisateur->isDisabled() === true) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $utilisateur->setDisabled(false);

                    $entityManager->persist($utilisateur);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        'Votre compte à été réactiver !'
                    );
                }else{
                    $this->get('session')->getFlashBag()->set(
                        'warning',
                        'Votre compte est déjà activer !'
                    );
                }
            }
        }else{
            $this->get('session')->getFlashBag()->set(
                'danger',
                'Oops ! vous n\'avez pas la permission pour faire cette action.'
            );
        }

        return $this->redirectToRoute('utilisateur_profile');
    }

    /**
     * @Route("/utilisateur/{id}", name="utilisateur_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function delete(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur): Response
    {
        if ($authorizationChecker->isGranted('ROLE_ADMIN') || $utilisateur === $this->getUser()) {
            if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($utilisateur);
                $entityManager->flush();

                if ($authorizationChecker->isGranted('ROLE_ADMIN') && $utilisateur !== $this->getUser()) {
                    return $this->redirectToRoute('utilisateur_index');
                }else{

                    $this->get('security.token_storage')->setToken(null);
                    $request->getSession()->invalidate();

                    return $this->redirectToRoute('home');
                }
            }
        }

        $this->get('session')->getFlashBag()->set(
            'danger',
            'Oops ! vous n\'avez pas la permission pour faire cette action.'
        );

        $link = $this->generateUrl(
            'utilisateur_profile', [ ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->redirect(!empty($request->headers->get('referer')) ? $request->headers->get('referer') : $link);
    }
}
