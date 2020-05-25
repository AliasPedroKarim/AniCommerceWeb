<?php

namespace App\Controller\Api;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    /**
     * @Route("/api/produit", name="api_produit",methods={"GET"})
     * @param ProduitRepository $repository
     * @return string|Response
     */
    public function index(ProduitRepository $repository) {
        return entityToNewFormat($repository->findAll(), 'response');
    }
}
