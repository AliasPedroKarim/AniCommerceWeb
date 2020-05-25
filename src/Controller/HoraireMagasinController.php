<?php

namespace App\Controller;

use App\Entity\HoraireMagasin;
use App\Entity\Magasin;
use App\Form\HoraireMagasinType;
use App\Repository\HoraireMagasinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/horaire/magasin")
 */
class HoraireMagasinController extends AbstractController
{

    const day = [
        'Lundi' => 'Lundi',
        'Mardi' => 'Mardi',
        'Mercredi' => 'Mercredi',
        'Jeudi' => 'Jeudi',
        'Vendredi' => 'Vendredi',
        'Samedi' => 'Samedi',
        'Dimanche' => 'Dimanche',
    ];

    /**
     * @Route("/", name="horaire_magasin_index", methods={"GET"})
     */
    public function index(HoraireMagasinRepository $horaireMagasinRepository): Response
    {
        return $this->render('horaire_magasin/index.html.twig', [
            'horaire_magasins' => $horaireMagasinRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="horaire_magasin_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response {
        $datas = null;
        $magasin = null;
        $entityManager = $this->getDoctrine()->getManager();

        if (!empty($request->getContent())) {
            try {
                $datas = json_decode($request->getContent(), true);

                // Patch add with magasin exist
                if (isset($datas['idMagasin']) && !empty($datas['idMagasin'])) {
                    $m = $entityManager->getRepository(Magasin::class)->findOneBy([ 'id' => $datas['idMagasin'] ]);
                    if (!empty($m)) {
                        $magasin = $m;
                    }

                    unset($datas['idMagasin']);
                }

            }catch (\Exception $e){
                $datas = [];
            }
        }
        if(!empty($datas)) {
            $request->request->set('horaire_magasin', $datas);
        }
        $horaireMagasin = new HoraireMagasin();
        $form = $this->createForm(HoraireMagasinType::class, $horaireMagasin);

        $form->handleRequest($request);

        $response = new Response();
        $json = $request->headers->get('Content-Type') == 'application/json';
        if ($json == true) {
            $response->headers->set('Content-Type', $request->headers->get('Content-Type'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $collectHoraireMagasin = [];
            if (is_array($horaireMagasin->getJour())) {
                foreach ($horaireMagasin->getJour() as $jour) {
                    // Faire attention ici, if faut bien verifier si la jour n'a pas déjà été definie
                    if (empty($entityManager->getRepository(HoraireMagasin::class)->findOneBy([ 'jour' => $jour ]))) {
                        $buffer = clone $horaireMagasin;
                        $buffer->setJour($jour);

                        if (!empty($magasin)) {
                            $buffer->setIdMagasin($magasin);
                        }

                        $entityManager->persist($buffer);
                        $entityManager->flush();

                        $collectHoraireMagasin[] = $buffer;
                    }
                }
            }else{
                $entityManager->persist($horaireMagasin);
                $entityManager->flush();
                $collectHoraireMagasin[] = $horaireMagasin;
            }

            return $json ? $response->setContent(json_encode([
                'error' => false,
                'data' => json_decode(entityToNewFormat($collectHoraireMagasin))
            ])) : $this->redirectToRoute('horaire_magasin_index');
        }

        return $json ? $response->setContent(json_encode([
            'error' => true,
            'message' => 'Un problème s\'est produit lors de la creation de de l\'horaire !',
            'data' => $form->getErrors()
        ])) : $this->render('horaire_magasin/new.html.twig', [
            'horaire_magasin' => $horaireMagasin,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="horaire_magasin_show", methods={"GET"})
     */
    public function show(HoraireMagasin $horaireMagasin): Response
    {
        return $this->render('horaire_magasin/show.html.twig', [
            'horaire_magasin' => $horaireMagasin,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="horaire_magasin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, HoraireMagasin $horaireMagasin): Response
    {
        $form = $this->createForm(HoraireMagasinType::class, $horaireMagasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('horaire_magasin_index');
        }

        return $this->render('horaire_magasin/edit.html.twig', [
            'horaire_magasin' => $horaireMagasin,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="horaire_magasin_delete", methods={"DELETE"})
     */
    public function delete(Request $request, HoraireMagasin $horaireMagasin): Response {
        $datas = null;
        $error = true;
        $idMagasin = null;
        if (!empty($request->getContent())) {
            try {
                $datas = json_decode($request->getContent(), true);
            }catch (\Exception $e){
                $datas = [];
            }
        }

        if(!empty($datas)) {
            $request->request->set('horaire_magasin', $datas);
        }

        $response = new Response();
        $json = $request->headers->get('Content-Type') == 'application/json';
        if ($json == true) {
            $response->headers->set('Content-Type', $request->headers->get('Content-Type'));
        }

        if ($this->isCsrfTokenValid('delete'.$horaireMagasin->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($horaireMagasin);
            $entityManager->flush();
            $error = false;
            $idMagasin = !empty($horaireMagasin->getIdMagasin()) ? $horaireMagasin->getIdMagasin()->getId() : null;
        }

        return !empty($idMagasin) ? $this->redirectToRoute('magasin_edit', [ 'id' => $idMagasin ]) : $this->redirectToRoute('horaire_magasin_index');
        /*return $json ? $response->setContent(json_encode([
            'error' => $error,
        ])) : $this->redirectToRoute('horaire_magasin_index');*/
    }
}
