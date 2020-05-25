<?php

namespace App\Controller;

use App\Entity\TypeReduction;
use App\Form\TypeReductionType;
use App\Repository\TypeReductionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/type/reduction")
 */
class TypeReductionController extends AbstractController
{
    /**
     * @Route("/", name="type_reduction_index", methods={"GET"})
     * @param TypeReductionRepository $typeReductionRepository
     * @return Response
     */
    public function index(TypeReductionRepository $typeReductionRepository): Response
    {
        return $this->render('type_reduction/index.html.twig', [
            'type_reductions' => $typeReductionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="type_reduction_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $typeReduction = new TypeReduction();
        $form = $this->createForm(TypeReductionType::class, $typeReduction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($typeReduction);
            $entityManager->flush();

            return $this->redirectToRoute('type_reduction_index');
        }

        return $this->render('type_reduction/new.html.twig', [
            'type_reduction' => $typeReduction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="type_reduction_show", methods={"GET"})
     * @param TypeReduction $typeReduction
     * @return Response
     */
    public function show(TypeReduction $typeReduction): Response
    {
        return $this->render('type_reduction/show.html.twig', [
            'type_reduction' => $typeReduction,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="type_reduction_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeReduction $typeReduction
     * @return Response
     */
    public function edit(Request $request, TypeReduction $typeReduction): Response
    {
        $form = $this->createForm(TypeReductionType::class, $typeReduction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('type_reduction_index');
        }

        return $this->render('type_reduction/edit.html.twig', [
            'type_reduction' => $typeReduction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="type_reduction_delete", methods={"DELETE"})
     * @param Request $request
     * @param TypeReduction $typeReduction
     * @return Response
     */
    public function delete(Request $request, TypeReduction $typeReduction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeReduction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($typeReduction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('type_reduction_index');
    }
}
