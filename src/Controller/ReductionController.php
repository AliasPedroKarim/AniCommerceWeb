<?php

namespace App\Controller;

use App\Entity\Reduction;
use App\Form\ReductionType;
use App\Repository\ReductionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reduction")
 */
class ReductionController extends AbstractController
{
    /**
     * @Route("/", name="reduction_index", methods={"GET"})
     * @param ReductionRepository $reductionRepository
     * @return Response
     */
    public function index(ReductionRepository $reductionRepository): Response
    {
        return $this->render('reduction/index.html.twig', [
            'reductions' => $reductionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="reduction_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $reduction = new Reduction();
        $form = $this->createForm(ReductionType::class, $reduction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reduction);
            $entityManager->flush();

            return $this->redirectToRoute('reduction_index');
        }

        return $this->render('reduction/new.html.twig', [
            'reduction' => $reduction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reduction_show", methods={"GET"})
     * @param Reduction $reduction
     * @return Response
     */
    public function show(Reduction $reduction): Response
    {
        return $this->render('reduction/show.html.twig', [
            'reduction' => $reduction,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reduction_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Reduction $reduction
     * @return Response
     */
    public function edit(Request $request, Reduction $reduction): Response
    {
        $form = $this->createForm(ReductionType::class, $reduction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reduction_index');
        }

        return $this->render('reduction/edit.html.twig', [
            'reduction' => $reduction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reduction_delete", methods={"DELETE"})
     * @param Request $request
     * @param Reduction $reduction
     * @return Response
     */
    public function delete(Request $request, Reduction $reduction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reduction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reduction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reduction_index');
    }
}
