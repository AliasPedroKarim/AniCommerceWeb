<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Magasin;
use App\Entity\Produit;
use App\Form\LigneCommandeType;
use App\Repository\MagasinRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MagasinController extends AbstractController {
    /**
     * @Route("/magasin", name="magasin_index")
     * @param MagasinRepository $magasinRepository
     * @return Response
     */
    public function index(MagasinRepository $magasinRepository) {
        $magasins = $magasinRepository->findAll();
        return $this->render('magasin/index.html.twig', [
            'magasins' => $magasins,
        ]);
    }

    /**
     * @Route("/magasin/{id}", name="magasin_show")
     * @param Request $request
     * @param Magasin $magasin
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function show(Request $request, Magasin $magasin, PaginatorInterface $paginator) {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository(Produit::class);

        $pagination = $paginator->paginate(
            $repository->applyFilter([ 'magasins' => [$magasin->getId()] ]),
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

        return $this->render('magasin/show.html.twig', [
            'magasin' => $magasin,
            'produits' => $pagination,
            'forms_ligne_commandes' => $forms,
            'ligneCommandes' => $ligneCommandes
        ]);
    }
}
