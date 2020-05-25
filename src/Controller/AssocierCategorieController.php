<?php

namespace App\Controller;

use App\Entity\AssocierCategorie;
use App\Form\AssocierCategorieType;
use App\Repository\AssocierCategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/associer/categorie")
 */
class AssocierCategorieController extends AbstractController
{
    /**
     * @Route("/", name="associer_categorie_index", methods={"GET"})
     * @param AssocierCategorieRepository $associerCategorieRepository
     * @return Response
     */
    public function index(AssocierCategorieRepository $associerCategorieRepository): Response
    {
        return $this->render('associer_categorie/index.html.twig', [
            'associer_categories' => $associerCategorieRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="associer_categorie_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $associerCategorie = new AssocierCategorie();
        $form = $this->createForm(AssocierCategorieType::class, $associerCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($associerCategorie);
            $entityManager->flush();

            return $this->redirectToRoute('associer_categorie_index');
        }

        return $this->render('associer_categorie/new.html.twig', [
            'associer_categorie' => $associerCategorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="associer_categorie_show", methods={"GET"})
     * @param AssocierCategorie $associerCategorie
     * @return Response
     */
    public function show(AssocierCategorie $associerCategorie): Response
    {
        return $this->render('associer_categorie/show.html.twig', [
            'associer_categorie' => $associerCategorie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="associer_categorie_edit", methods={"GET","POST"})
     * @param Request $request
     * @param AssocierCategorie $associerCategorie
     * @return Response
     */
    public function edit(Request $request, AssocierCategorie $associerCategorie): Response
    {
        $form = $this->createForm(AssocierCategorieType::class, $associerCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('associer_categorie_index');
        }

        return $this->render('associer_categorie/edit.html.twig', [
            'associer_categorie' => $associerCategorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="associer_categorie_delete", methods={"DELETE"})
     * @param Request $request
     * @param AssocierCategorie $associerCategorie
     * @return Response
     */
    public function delete(Request $request, AssocierCategorie $associerCategorie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$associerCategorie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($associerCategorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('associer_categorie_index');
    }
}
