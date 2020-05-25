<?php

namespace App\Controller;

use App\Entity\UtiliserPromo;
use App\Form\UtiliserPromoType;
use App\Repository\UtiliserPromoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/utiliser/promo")
 */
class UtiliserPromoController extends AbstractController
{
    /**
     * @Route("/", name="utiliser_promo_index", methods={"GET"})
     * @param UtiliserPromoRepository $utiliserPromoRepository
     * @return Response
     */
    public function index(UtiliserPromoRepository $utiliserPromoRepository): Response
    {
        return $this->render('utiliser_promo/index.html.twig', [
            'utiliser_promos' => $utiliserPromoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="utiliser_promo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $utiliserPromo = new UtiliserPromo();
        $form = $this->createForm(UtiliserPromoType::class, $utiliserPromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($utiliserPromo);
            $entityManager->flush();

            return $this->redirectToRoute('utiliser_promo_index');
        }

        return $this->render('utiliser_promo/new.html.twig', [
            'utiliser_promo' => $utiliserPromo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utiliser_promo_show", methods={"GET"})
     * @param UtiliserPromo $utiliserPromo
     * @return Response
     */
    public function show(UtiliserPromo $utiliserPromo): Response
    {
        return $this->render('utiliser_promo/show.html.twig', [
            'utiliser_promo' => $utiliserPromo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="utiliser_promo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UtiliserPromo $utiliserPromo): Response
    {
        $form = $this->createForm(UtiliserPromoType::class, $utiliserPromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('utiliser_promo_index');
        }

        return $this->render('utiliser_promo/edit.html.twig', [
            'utiliser_promo' => $utiliserPromo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utiliser_promo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UtiliserPromo $utiliserPromo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utiliserPromo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($utiliserPromo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('utiliser_promo_index');
    }
}
