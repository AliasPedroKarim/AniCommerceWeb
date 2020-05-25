<?php

namespace App\Controller;

use App\Entity\AssocierCategorie;
use App\Entity\Commande;
use App\Entity\Image;
use App\Entity\LigneCommande;
use App\Entity\Presenter;
use App\Entity\Produit;
use App\Form\FilterProduitType;
use App\Form\ImageType;
use App\Form\LigneCommandeType;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\ProduitSessionService;
use App\Utils\SessionKeys;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController {

    /**
     * Merge objects
     * Allow to manage object by doctrine when using stored (eg. in session data values)
     * @param $data_array - list of form fields
     * @return mixed
     */
    public function manageObjects($data_array = []) {
        if (!empty($data_array)) {
            foreach ($data_array as $key => $value) {
                // for multi choices
                if ($value instanceof ArrayCollection) {
                    $data_array[$key] = $this->manageObjects($value);
                }
                // commit dateTime object
                elseif ($value instanceof \DateTime) {

                }
                elseif (is_object($value)) {
                    $data_array[$key] = $this->getDoctrine()->getManager()->merge($value);
                }
            }
        }
        return $this->manageEmptyObjects($data_array) === true ? [] : $data_array;
    }

    public function manageEmptyObjects($data_array) {
        if (!empty($data_array)) {
            foreach ($data_array as $key => $value) {
                if (empty($value) || $value instanceof ArrayCollection && $value->isEmpty()) {
                    unset($data_array[$key]);
                }
            }
        }
        return empty($data_array);
    }

    /**
     *
     * @Route("/produit/", name="produit_index", methods={"GET","POST"})
     *
     * @param ProduitRepository $produitRepository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(ProduitRepository $produitRepository, Request $request, PaginatorInterface $paginator): Response {
        $session = $this->get('session');
        handleSession($session);
        $sessionFilter = $this->manageObjects($session->get(SessionKeys::FILTER_PRODUCT));

        $form = $this->createForm(FilterProduitType::class, $sessionFilter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $session->set(SessionKeys::FILTER_PRODUCT, $this->manageObjects($form->getData()));
        }

        $pagination = $paginator->paginate(
            $produitRepository->applyFilter($session->get(SessionKeys::FILTER_PRODUCT)),
            $request->query->getInt('page', 1),
            10
        );



        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'idUtilisateur' => $this->getUser() ? $this->getUser()->getId() : null ]);
        $ligneCommandes = $entityManager->getRepository(LigneCommande::class)->findBy([ 'idCommande' => !empty($commandeRepository) ? $commandeRepository->getId() : null ]);
        $forms = [];

        foreach($ligneCommandes as $ligneCommande) {
            $forms[$ligneCommande->getId()] = $this->createForm(LigneCommandeType::class, $ligneCommande)->handleRequest($request)->createView();
        }



        return $this->render('produit/index.html.twig', [
            'produits' => $pagination,
            'form' => $form->createView(),
            'forms_ligne_commandes' => $forms,
            'ligneCommandes' => $ligneCommandes
        ]);
    }

    /**
     *
     * @Route("/admin/produit/", name="admin_produit_index", methods={"GET"})
     *
     * @param ProduitRepository $produitRepository
     * @return Response
     */
    public function adminIndex(ProduitRepository $produitRepository): Response {
        $produits = $produitRepository->findAll();

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
            '_produits' => entityToNewFormat($produits),
        ]);
    }

    /**
     * @Route("/produit/filter/clear",name="produit_filter_clear", methods={"GET"})
     * @param SessionInterface $session
     * @return Response
     */
    public function clear(SessionInterface $session): Response {
        $session->set(SessionKeys::FILTER_PRODUCT, []);
        return $this->redirectToRoute('produit_index');
    }

    public function fixAndSaveProduit(Produit $produit) {
        $entityManager = $this->getDoctrine()->getManager();
        $associerCategories = $produit->getAssocierCategories()->toArray();
        $produit->getAssocierCategories()->clear();

        // Fix
        $entityManager->persist($produit);
        $entityManager->flush();

        // Fix
        foreach($associerCategories as $value) {
            $associerCategorie = new AssocierCategorie();
            $associerCategorie->setIdProduit($produit);
            $associerCategorie->setIdCategorie($value);

            $produit->getAssocierCategories()->add($associerCategorie);
        }
    }

    public function handleImage(Request $request, Produit $produit) {
        $entityManager = $this->getDoctrine()->getManager();

        if (null !== $request->get('images') && is_array($request->get('images'))) {
            foreach ($request->get('images') as $value) {
                if (!isset($value['id_produit'])) {
                    $image = new Image();
                    if (!empty($value['image'])) {
                        $image->setCheminImage($value['image']);
                    }
                    if (!empty($value['description'])) {
                        $image->setDescription($value['description']);
                    }
                    $entityManager->persist($image);

                    $presenter = new Presenter();
                    $presenter->setIdImage($image);
                    $presenter->setIdProduit($produit);

                    $entityManager->persist($presenter);
                }
            }
        }
    }

    /**
     * @Route("/admin/produit/new", name="admin_produit_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response {
        $produit = new Produit();
        $image = new Image();

        $form = $this->createForm(ProduitType::class, $produit);
        $form_image = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);
        $form_image->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleImage($request, $produit);

            $this->fixAndSaveProduit($produit);

            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
            'form_image' => $form_image->createView(),
        ]);
    }

    /**
     * @Route("/produit/{id}", name="produit_show", methods={"GET"})
     * @param Produit $produit
     * @return Response
     */
    public function show(Produit $produit): Response {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            '_produits' => entityToNewFormat($produit),
        ]);
    }

    /**
     * @Route("/admin/produit/{id}/edit", name="admin_produit_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Produit $produit
     * @return Response
     */
    public function edit(Request $request, Produit $produit): Response {
        $image = new Image();

        $form = $this->createForm(ProduitType::class, $produit);
        $form_image = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);
        $form_image->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$this->getDoctrine()->getManager()->flush();

            $this->handleImage($request, $produit);

            $this->fixAndSaveProduit($produit);

            $this->get('session')->getFlashBag()->set(
                'success',
                'Le produit <b>' . $produit->getLibelle() . '</b> à été mise à jour !'
            );

            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
            'form_image' => $form_image->createView()
        ]);
    }

    /**
     * @Route("/admin/produit/{id}", name="admin_produit_delete", methods={"DELETE"})
     * @param Request $request
     * @param Produit $produit
     * @return Response
     */
    public function delete(Request $request, Produit $produit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->set(
                'success',
                'Le produit <b>' . $produit->getLibelle() . '</b> à été supprimer !'
            );

        }

        return $this->redirectToRoute('admin_produit_index');
    }
}
