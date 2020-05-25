<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\Resider;
use App\Form\LigneCommandeType;
use App\Repository\LigneCommandeRepository;
use App\Utils\SessionKeys;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/lignecommande")
 */
class LigneCommandeController extends AbstractController
{
    /**
     * @Route("/", name="ligne_commande_index", methods={"GET"})
     * @param Request $request
     * @param LigneCommandeRepository $ligneCommandeRepository
     * @return Response
     */
    public function index(Request $request, LigneCommandeRepository $ligneCommandeRepository): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'idUtilisateur' => $this->getUser() ? $this->getUser()->getId() : null ]);
        $ligneCommandes = $ligneCommandeRepository->findBy([ 'idCommande' => !empty($commandeRepository) ? $commandeRepository->getId() : null ]);
        $forms = [];

        foreach($ligneCommandes as $ligneCommande) {
            $forms[$ligneCommande->getId()] = $this->createForm(LigneCommandeType::class, $ligneCommande)->handleRequest($request)->createView();
        }

        return $this->render('ligne_commande/index.html.twig', [
            'ligne_commandes' => $ligneCommandes,
            'commande' => $commandeRepository,
            'forms' => $forms
        ]);
    }

    /**
     * @Route("/check",name="ligne_commande_check",methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function checkLigneCommande(Request $request) {
        $session = $this->get('session');
        handleSession($session);
        $sLigneCommandes = $session->get(SessionKeys::COMMAND_ROW);

        if (!empty($this->getUser()) && !empty($sLigneCommandes)) {
            foreach ($sLigneCommandes as $sLigneCommande) {
                if (empty($this->productIntegrity($request, $sLigneCommande['produit'], true))) {
                    $this->handleLigneCommande($request, $sLigneCommande['produit'], $sLigneCommande['quantite'], true);
                }
            }

            $session->set(SessionKeys::COMMAND_ROW, []);
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * @param bool $silent
     * @return bool|RedirectResponse
     */
    private function productIntegrity(Request $request, Produit $produit, $silent = false) {
        if (empty($produit)) {
            if ($silent) {
                $this->get('session')->getFlashBag()->set(
                    'danger',
                    'Aucun produit n\'a était éléctionner!'
                );
            }

            return !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('produit_index');
        }

        if ($produit->getStock() <= 0) {
            if ($silent === false) {
                $this->get('session')->getFlashBag()->set(
                    'danger',
                    'Ce produit est en rupture de stock !'
                );
            }

            return !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('produit_index');
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * @param null $quantite
     * @param bool $silent
     * @return array
     * @throws \Exception
     */
    private function handleLigneCommande(Request $request, Produit $produit, $quantite = null, $silent = false) {
        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'dateLivraison' => null, 'idUtilisateur' => $this->getUser()->getId() ]);
        $RepositoryResider = $entityManager->getRepository(Resider::class)->findOneBy([ 'defaut' => true, 'idUtilisateur' => $this->getUser()->getId() ]);

        $commande = null;
        
        if($silent === true) {
            $produit = $entityManager->getRepository(Produit::class)->findOneBy([ 'id' => $produit->getId() ]);
        }

        if(!empty($commandeRepository)) {
            $commande = $commandeRepository;
        }else{
            $commande = new Commande();
            $commande->setIdAdresse($RepositoryResider ? $RepositoryResider->getIdAdresse() : null)
                ->setPanier(true)
                ->setIdUtilisateur($this->getUser())
                ->setDateCde(new \DateTime());

            $entityManager->persist($commande);
            $entityManager->flush();
        }

        $LCRepository = $entityManager->getRepository(LigneCommande::class)->findOneBy([ 'idProduit' => $produit->getId(), 'idCommande' => $commande->getId() ]);

        $oldQte = !empty($LCRepository) ? $LCRepository->getQuantite() : null;

        $ligneCommande = !empty($LCRepository) ? $LCRepository : new LigneCommande();
        $form = $this->createForm(LigneCommandeType::class, $ligneCommande);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || $produit) {
            $entityManager = $this->getDoctrine()->getManager();

            if (empty($ligneCommande->getIdCommande())) {
                $ligneCommande->setIdCommande($commande);
            }

            if (empty($ligneCommande->getIdProduit())) {
                $ligneCommande->setIdProduit($produit);
            }
            $ligneCommande->setPrixUnitaire($produit->getPrixHt());
            if (empty($ligneCommande->getQuantite())) {
                $number = !empty($quantite) ? $quantite : 1;
                $ligneCommande->setQuantite($number);
                $produit->setStock(abs($produit->getStock() - $number));
            }else{
                $number = !empty($quantite) ? $quantite : 1;
                $ligneCommande->setQuantite(!empty($LCRepository) ? $ligneCommande->getQuantite() + $number : $ligneCommande->getQuantite());
                $produit->setStock(
                    abs($produit->getStock() - (!empty($oldQte) ? abs($oldQte - $ligneCommande->getQuantite()) : 1))
                );
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            $entityManager->persist($ligneCommande);
            $entityManager->flush();

            if ($silent === false) {
                $this->get('session')->getFlashBag()->set(
                    'success',
                    'le produit <span class="font-weight-bold">' . $produit->getLibelle() . '</span> ajouter à votre panier !'
                );
            }

            return [
                'pass' => true,
                'response' => !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('produit_index')
            ];
        }

        return [
            'pass' => false,
            'ligneCommande' => $ligneCommande,
            'form' => $form
        ];
    }

    /**
     * @Route("/new/{produit}", name="ligne_commande_new", methods={"GET","POST"})
     * @param Request $request
     * @param Produit $produit
     * @return string|Response
     * @throws \Exception
     */
    public function new(Request $request, Produit $produit) {
        $session = $this->get('session');
        handleSession($session);

        $productIntegrity = $this->productIntegrity($request, $produit);
        if (!empty($productIntegrity)) {
            return $productIntegrity;
        }

        if (empty($this->getUser())) {
            $sLigneCommandes = $session->get(SessionKeys::COMMAND_ROW);
            if (null !== $sLigneCommandes && is_array($sLigneCommandes)) {
                $quantite = $request->request->get('quantite');
                if (isset($sLigneCommandes[$produit->getId()])) {

                    if($this->isCsrfTokenValid('ligne_commande_new'.$produit->getId(), $request->request->get('_token'))) {
                        $sLigneCommandes[$produit->getId()]['quantite'] = !empty($quantite) && is_numeric($quantite)
                            ? $quantite
                            : $sLigneCommandes[$produit->getId()]['quantite'] + 1;
                    }

                }else{

                    if($this->isCsrfTokenValid('ligne_commande_new'.$produit->getId(), $request->request->get('_token'))) {
                        $sLigneCommandes[$produit->getId()] = [
                            'quantite' => !empty($quantite) && is_numeric($quantite)
                                ? $quantite
                                : 1,
                            'produit' => $produit,
                            'image' => !$produit->getImages()->isEmpty() ? $produit->getImages()->get(0)->getIdImage()->getCheminImage() : 'https://images.assetsdelivery.com/compings_v2/pavelstasevich/pavelstasevich1902/pavelstasevich190200120.jpg'
                        ];
                    }

                }
                $session->set(SessionKeys::COMMAND_ROW, $sLigneCommandes);

                $link = $this->generateUrl(
                    'app_login', [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $this->get('session')->getFlashBag()->set(
                    'warning',
                    "Le produit a été rajouter dans votre panier !<br>Attention ! Pour passer la commande, vous devez être pas <a href='$link'>connecter</a>..."
                );
            } else {
                $this->get('session')->getFlashBag()->set(
                    'danger',
                    'Oops ! On dirait qu\'un problème s\'est produit lors de l\'ajout du produit dans votre panier...'
                );
            }

            return !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('produit_index');
        }

        $handleLigneCommande = $this->handleLigneCommande($request, $produit);

        if ($handleLigneCommande['pass'] === true) {
            return $handleLigneCommande['response'];
        }

        return $this->render('ligne_commande/new.html.twig', [
            'ligne_commande' => $handleLigneCommande['ligneCommande'],
            'form' => $handleLigneCommande['form'],
        ]);
    }

    /**
     * @Route("/{id}", name="ligne_commande_show", methods={"GET"})
     * @param LigneCommande $ligneCommande
     * @return Response
     */
    public function show(LigneCommande $ligneCommande): Response
    {
        return $this->render('ligne_commande/show.html.twig', [
            'ligne_commande' => $ligneCommande,
        ]);
    }

    /**
     * @Route("/session/{produit}/edit", name="ligne_commande_sedit", methods={"POST"})
     * @param Request $request
     * @param Produit $produit
     * @return RedirectResponse
     */
    public function editSessionLigneCommande(Request $request, Produit $produit) {
        $session = $this->get('session');
        handleSession($session);

        if (empty($this->getUser()) && $this->isCsrfTokenValid('session_edit'.$produit->getId(), $request->request->get('_token'))) {
            if (null !== $request->get('quantite') && is_numeric($request->get('quantite'))) {
                $sLigneCommandes = $session->get(SessionKeys::COMMAND_ROW);
                if (null !== $sLigneCommandes && is_array($sLigneCommandes)) {
                    if (isset($sLigneCommandes[$produit->getId()])) {

                        if ($request->get('quantite') <= 0) {
                            unset($sLigneCommandes[$produit->getId()]);
                        }else{
                            $sLigneCommandes[$produit->getId()]['quantite'] = $request->get('quantite');
                        }
                    }
                }
                $session->set(SessionKeys::COMMAND_ROW, $sLigneCommandes);
            }
        }

        return !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('ligne_commande_index');
    }

    /**
     * @Route("/session/{produit}/delete", name="ligne_commande_sdelete", methods={"DELETE"})
     * @param Request $request
     * @param Produit $produit
     * @return RedirectResponse
     */
    public function deleteSessionLigneCommande(Request $request, Produit $produit) {
        $session = $this->get('session');
        handleSession($session);

        if (empty($this->getUser()) && $this->isCsrfTokenValid('session_delete'.$produit->getId(), $request->request->get('_token'))) {
            $sLigneCommandes = $session->get(SessionKeys::COMMAND_ROW);
            if (null !== $sLigneCommandes && is_array($sLigneCommandes)) {
                if (isset($sLigneCommandes[$produit->getId()])) {
                    unset($sLigneCommandes[$produit->getId()]);
                }
            }
            $session->set(SessionKeys::COMMAND_ROW, $sLigneCommandes);
        }

        return !empty($request->headers->get('referer')) ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('ligne_commande_index');
    }

    /**
     * @Route("/{id}/edit", name="ligne_commande_edit", methods={"GET","POST"})
     * @param Request $request
     * @param LigneCommande $ligneCommande
     * @return Response
     */
    public function edit(Request $request, LigneCommande $ligneCommande): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $LCRepository = $entityManager->getRepository(LigneCommande::class)->findOneBy([ 'id' => $ligneCommande->getId() ]);

        $oldQte = $LCRepository->getQuantite();

        $form = $this->createForm(LigneCommandeType::class, $ligneCommande);
        $form->handleRequest($request);

        if ($ligneCommande->getQuantite() <= 0){
            $entityManager->remove($ligneCommande);
            $entityManager->flush();
            return $this->redirectToRoute('ligne_commande_index');
        }

        if ($form->isSubmitted() && ($form->isValid() || !empty($request->get('ligne_commande')))) {
            $rStock = 0;
            if ($ligneCommande->getQuantite() > $oldQte) {
                $rStock = $LCRepository->getIdProduit()->getStock() - ($ligneCommande->getQuantite() - $oldQte);
            }else{
                $rStock = $LCRepository->getIdProduit()->getStock() + ($oldQte - $ligneCommande->getQuantite());
            }

            $entityManager->persist($LCRepository->getIdProduit()->setStock($rStock));

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ligne_commande_index');
        }

        return $this->render('ligne_commande/edit.html.twig', [
            'ligne_commande' => $ligneCommande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/clear/{id}", name="ligne_commande_clear", methods={"POST"})
     * @Route("/clear", name="ligne_commande_clear_s", methods={"POST"})
     * @param Request $request
     * @param Commande $commande
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return Response
     */
    public function clear(Request $request, Commande $commande = null, AuthorizationCheckerInterface $authorizationChecker): Response {
        if (empty($this->getUser())) {
            $session = $this->get('session');
            handleSession($session);

            $session->set(SessionKeys::COMMAND_ROW, []);

            $this->get('session')->getFlashBag()->set(
                'success',
                'Votre panier a été vider.'
            );
        }else {
            if (!empty($commande)) {
                $permissions = AppController::handleAccess($this, $request, $authorizationChecker, $this->getUser());

                if ($this->isCsrfTokenValid('clear'.$commande->getId(), $request->request->get('_token')) && $permissions['permit']) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $lCommandes = $entityManager->getRepository(LigneCommande::class)->findBy([ 'idCommande' => $commande->getId() ]);
                    foreach ($lCommandes as $lCommande) {
                        $entityManager->remove($lCommande);
                    }

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        'Votre panier a été vider.'
                    );

                    $entityManager->flush();
                }
            }
        }

        return $this->redirectToRoute('ligne_commande_index');
    }

    /**
     * @Route("/{id}", name="ligne_commande_delete", methods={"DELETE"})
     * @param Request $request
     * @param LigneCommande $ligneCommande
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return Response
     */
    public function delete(Request $request, LigneCommande $ligneCommande, AuthorizationCheckerInterface $authorizationChecker): Response {
        $permissions = AppController::handleAccess($this, $request, $authorizationChecker, $this->getUser());

        if ($this->isCsrfTokenValid('delete'.$ligneCommande->getId(), $request->request->get('_token')) && $permissions['permit']) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ligneCommande);

            $this->get('session')->getFlashBag()->set(
                'success',
                'Le produit "<b>'. $ligneCommande->getIdProduit()->getLibelle() .'</b>" a été retiré de votre panier.'
            );

            $entityManager->flush();
        }

        return $this->redirectToRoute('ligne_commande_index');
    }
}
