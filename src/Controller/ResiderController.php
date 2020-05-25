<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Resider;
use App\Entity\Utilisateur;
use App\Form\AdresseType;
use App\Form\ResiderType;
use App\Repository\ResiderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResiderController extends AbstractController {
    public function handleDefaut(Resider $resider, Utilisateur $utilisateur) {
        if ($resider->getDefaut() === true) {
            $entityManager = $this->getDoctrine()->getManager();
            $residerRespository = $entityManager->getRepository(Resider::class);

            foreach ($residerRespository->findBy([ 'idUtilisateur' => $utilisateur ]) as $_resider) {
                $_resider->setDefaut(false);
                $entityManager->persist($_resider);
            }
            $entityManager->flush();
            $resider->setDefaut(true);
        }
    }

    public function handleAccess(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur) {
        if (!$authorizationChecker->isGranted('ROLE_ADMIN') && $utilisateur !== $this->getUser()) {
            $this->get('session')->getFlashBag()->set(
                'danger',
                AppController::NOT_PERMIT
            );

            $link = $this->generateUrl(
                'utilisateur_profile', [ ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return [ 'permit' => false, 'response' => $this->redirect(!empty($request->headers->get('referer')) ? $request->headers->get('referer') : $link) ];
        }

        return [ 'permit' => true ];
    }

    /**
     * @Route("/utilisateur/{utilisateur}/resider", name="resider_index", methods={"GET"})
     *
     * @param Request $request
     * @param ResiderRepository $residerRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function index(Request $request, ResiderRepository $residerRepository, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur): Response
    {
        $permit = $this->handleAccess($request, $authorizationChecker, $utilisateur);

        if ($permit['permit']) {
            return $this->render('resider/index.html.twig', [
                'residers' => $residerRepository->findBy([ 'idUtilisateur' => $utilisateur->getId() ]),
                'utilisateur' => $utilisateur
            ]);
        }

        return $permit['response'];
    }

    /**
     * @Route("/utilisateur/{utilisateur}/resider/new", name="resider_new", methods={"GET","POST"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function new(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur): Response {
        $permit = $this->handleAccess($request, $authorizationChecker, $utilisateur);

        if ($permit['permit']) {
            $resider = new Resider();
            $adresse = new Adresse();

            $form = $this->createForm(ResiderType::class, $resider);
            $form_adresse = $this->createForm(AdresseType::class, $adresse);

            $form->handleRequest($request);
            $form_adresse->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->persist($adresse);
                $entityManager->flush();

                $resider->setIdAdresse($adresse);
                $resider->setIdUtilisateur($utilisateur);

                $this->handleDefaut($resider, $utilisateur);

                $entityManager->persist($resider);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->set(
                    'success',
                    'Votre adresse a été ajouter.'
                );

                return $this->redirectToRoute('resider_index', [ 'utilisateur' => $utilisateur->getId() ]);
            }

            return $this->render('resider/new.html.twig', [
                'utilisateur' => $utilisateur,
                'resider' => $resider,
                'form' => $form->createView(),
                'form_adresse' => $form_adresse->createView(),
            ]);
        }

        return $permit['response'];
    }

    /**
     * @Route("/utilisateur/{utilisateur}/resider/{resider}", name="resider_show", methods={"GET"})
     * @param Request $request
     * @param Resider $resider
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @return Response
     */
    public function show(Request $request, AuthorizationCheckerInterface $authorizationChecker, Resider $resider, Utilisateur $utilisateur): Response
    {
        $permit = $this->handleAccess($request, $authorizationChecker, $utilisateur);

        if ($permit['permit']) {
            if ($utilisateur === $resider->getIdUtilisateur()){
                return $this->render('resider/show.html.twig', [
                    'resider' => $resider,
                    'utilisateur' => $utilisateur,
                ]);
            }

            return $this->redirectToRoute('home');
        }

        return $permit['response'];
    }

    /**
     * @Route("/utilisateur/{utilisateur}/resider/{resider}/edit", name="resider_edit", methods={"GET","POST"})
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @param Resider $resider
     * @return Response
     */
    public function edit(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur, Resider $resider): Response
    {
        $permit = $this->handleAccess($request, $authorizationChecker, $utilisateur);

        if ($permit['permit']) {
            if ($utilisateur === $resider->getIdUtilisateur()) {
                $adresse = $resider->getIdAdresse();

                $form = $this->createForm(ResiderType::class, $resider);
                $form_adresse = $this->createForm(AdresseType::class, $adresse);

                $form->handleRequest($request);
                $form_adresse->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $this->handleDefaut($resider, $utilisateur);

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirectToRoute('resider_index', [ 'utilisateur' => $utilisateur->getId() ]);
                }

                return $this->render('resider/edit.html.twig', [
                    'resider' => $resider,
                    'utilisateur' => $utilisateur,
                    'form' => $form->createView(),
                    'form_adresse' => $form_adresse->createView(),
                ]);
            }

            return $this->redirectToRoute('home');
        }

        return $permit['response'];
    }

    /**
     * @Route("/utilisateur/{utilisateur}/resider/{resider}", name="resider_delete", methods={"DELETE"})
     * @param Request $request
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Utilisateur $utilisateur
     * @param Resider $resider
     * @return Response
     */
    public function delete(Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur, Resider $resider): Response
    {
        $permit = $this->handleAccess($request, $authorizationChecker, $utilisateur);

        if ($permit['permit']) {
            if ($utilisateur === $resider->getIdUtilisateur()) {
                if ($this->isCsrfTokenValid('delete'.$resider->getId(), $request->request->get('_token'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($resider);
                    $entityManager->flush();
                }

                return $this->redirectToRoute('resider_index', ['utilisateur' => $utilisateur]);
            }
            
            return $this->redirectToRoute('home');
        }

        return $permit['response'];
    }
}
