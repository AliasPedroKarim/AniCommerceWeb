<?php

namespace App\Controller\Admin;

use App\Entity\Adresse;
use App\Entity\HoraireMagasin;
use App\Entity\Image;
use App\Entity\Magasin;
use App\Form\AdresseType;
use App\Form\HoraireMagasinType;
use App\Form\ImageType;
use App\Form\MagasinType;
use App\Repository\MagasinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/magasin")
 */
class MagasinController extends AbstractController
{

    /**
     * @Route("/", name="admin_magasin_index", methods={"GET"})
     * @param MagasinRepository $magasinRepository
     * @return Response
     */
    public function index(MagasinRepository $magasinRepository): Response {
        $magasins = $magasinRepository->findAll();
        return $this->render('admin/magasin/index.html.twig', [
            'magasins' => $magasins,
            '_magasins' => entityToNewFormat($magasins),
        ]);
    }

    /**
     * @Route("/new", name="magasin_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response {
        $magasin = new Magasin();
        $adresse = new Adresse();
        $horaire = new HoraireMagasin();
        $image = new Image();

        $form = $this->createForm(MagasinType::class, $magasin);
        $form_adresse = $this->createForm(AdresseType::class, $adresse);
        $form_horaire = $this->createForm(HoraireMagasinType::class, $horaire);
        $form_image = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);
        $form_adresse->handleRequest($request);
        $form_horaire->handleRequest($request);
        $form_image->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form_adresse->isValid() && $form_image->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($adresse);
            $entityManager->persist($image);
            $entityManager->flush();

            $magasin->setIdAdresse($adresse);
            $magasin->setIdImage($image);

            $entityManager->persist($magasin);
            $entityManager->flush();

            // Horaire

            $listHoraires = $request->request->get('horaires');

            if (!empty($listHoraires)) {
                foreach ($listHoraires as $listHoraire) {
                    $horaires = $entityManager->getRepository(HoraireMagasin::class)->findBy([ 'id' => $listHoraire ]);
                    if (!empty($horaires)) {
                        foreach ($horaires as $horaire){
                            if (empty($horaire->getIdMagasin())) {
                                $entityManager->persist($horaire->setIdMagasin($magasin));
                                $entityManager->flush();
                            }
                        }

                    }
                }
            }

            $this->get('session')->getFlashBag()->set(
                'success',
                'Le magasin ' . $magasin->getNom() . ' a été créer !'
            );

            return $this->redirectToRoute('admin_magasin_index');
        }

        return $this->render('admin/magasin/new.html.twig', [
            'magasin' => $magasin,
            'form' => $form->createView(),
            'form_adresse' => $form_adresse->createView(),
            'form_horaire' => $form_horaire->createView(),
            'form_image' => $form_image->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_magasin_show", methods={"GET"})
     * @param Magasin $magasin
     * @return Response
     */
    public function show(Magasin $magasin): Response
    {
        return $this->render('admin/magasin/show.html.twig', [
            'magasin' => $magasin,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="magasin_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Magasin $magasin
     * @return Response
     */
    public function edit(Request $request, Magasin $magasin): Response {
        $form = $this->createForm(MagasinType::class, $magasin);
        $adresse = !empty($magasin->getIdAdresse()) ? $magasin->getIdAdresse() : new Adresse();
        $form_adresse = $this->createForm(AdresseType::class, $adresse);
        $form_horaire = $this->createForm(HoraireMagasinType::class, new HoraireMagasin());
        $image = !empty($magasin->getIdImage()) ? $magasin->getIdImage() : new Image();
        $form_image = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);
        $form_adresse->handleRequest($request);
        $form_horaire->handleRequest($request);
        $form_image->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();
        $horaires = $entityManager->getRepository(HoraireMagasin::class)->findBy([ 'idMagasin' => $magasin->getId() ]);

        if ($form->isSubmitted() && $form->isValid() && $form_adresse->isValid() && $form_image->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $entityManager->persist($adresse);
            $magasin->setIdAdresse($adresse);
            $entityManager->flush();

            $entityManager->persist($image);
            $magasin->setIdImage($image);
            $entityManager->flush();

            $entityManager->persist($magasin);

            $entityManager->flush();

            $this->get('session')->getFlashBag()->set(
                'success',
                'Le magasin ' . $magasin->getNom() . ' a été mise à jour !'
            );

            return $this->redirectToRoute('admin_magasin_index');
        }

        return $this->render('admin/magasin/edit.html.twig', [
            'magasin' => $magasin,
            'form' => $form->createView(),
            'form_adresse' => $form_adresse->createView(),
            'form_horaire' => $form_horaire->createView(),
            'horaires' => entityToNewFormat($horaires),
            '_horaires' => $horaires,
            'form_image' => $form_image->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="magasin_delete", methods={"DELETE"})
     * @param Request $request
     * @param Magasin $magasin
     * @return Response
     */
    public function delete(Request $request, Magasin $magasin): Response
    {
        if ($this->isCsrfTokenValid('delete'.$magasin->getId(), $request->request->get('_token'))) {
            $this->get('session')->getFlashBag()->set(
                'success',
                'Le magasin ' . $magasin->getNom() . ' a été supprimer !'
            );
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($entityManager->getRepository(HoraireMagasin::class)->findBy([ 'idMagasin' => $magasin->getId() ]) as $h) {
                $entityManager->remove($h);
            }
            $entityManager->flush();
            $entityManager->remove($magasin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_magasin_index');
    }
}
