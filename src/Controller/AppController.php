<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Magasin;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Utils\SessionKeys;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AppController extends AbstractController {
    const NOT_PERMIT = 'Oops ! vous n\'avez pas la permission pour faire cette action.';

    /**
     * @Route("/", name="home")
     */
    public function home() {






        // J'AIME PAS CA DU TOUT

        $session = $this->get('session');
        handleSession($session);
        $sLigneCommandes = $session->get(SessionKeys::COMMAND_ROW);

        if (!empty($this->getUser()) && !empty($sLigneCommandes)) {
            return $this->redirectToRoute('ligne_commande_check');
        }








        return $this->render('app/index.html.twig', [

        ]);
    }



    public static function handleAccess($self, Request $request, AuthorizationCheckerInterface $authorizationChecker, Utilisateur $utilisateur) {
        if (!$authorizationChecker->isGranted('ROLE_ADMIN') && $utilisateur !== $self->getUser()) {
            $self->get('session')->getFlashBag()->set(
                'danger',
                self::NOT_PERMIT
            );

            $link = $self->generateUrl(
                'home', [ ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return [ 'permit' => false, 'response' => $self->redirect(!empty($request->headers->get('referer')) ? $request->headers->get('referer') : $link) ];
        }

        return [ 'permit' => true ];
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin() {
        $em = $this->getDoctrine()->getManager();
        $magasinRepository = $em->getRepository(Magasin::class);
        $produitRepository = $em->getRepository(Produit::class);

        $commandes = $em->getRepository(Commande::class)->applyFilter([ 'panier' => 0 ])->getQuery()->getResult();
        $annualy = CommandeController::getCA($commandes);

        $months = [
            'January' => 0,
            'February' => 0,
            'March' => 0,
            'April' => 0,
            'May' => 0,
            'June' => 0,
            'July ' => 0,
            'August' => 0,
            'September' => 0,
            'October' => 0,
            'November' => 0,
            'December' => 0];

        foreach ($commandes as $commande) {
            $months[$commande->getDateLivraison()->format('F')] += CommandeController::getCA([$commande]);
        }

        return $this->render('admin/index.html.twig', [
            'stats' => [
                'montly' => CommandeController::getCA($em->getRepository(Commande::class)->applyFilter([ 'montly' => true, 'panier' => 0 ])->getQuery()->getResult()),
                'annualy' => $annualy,
                'months' => $months,

                // Fake subvention for simulate source revenue
                'subvention' => ($annualy / 2)
            ],
            'produits' => $produitRepository->findAll(),
            'magasins' => $magasinRepository->findAll(),
            'magasinsCA' => $magasinRepository->applyFilter([ 'magasinCA' => true, 'limit' => 6 ])->getQuery()->getResult()
        ]);
    }

    public function navbar($pathInfo) {
        $prepareDatas = [];
        $entityManager = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        handleSession($session);

        if (!empty($this->getUser())) {
            $commande = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'idUtilisateur' => $this->getUser()->getId() ]);

            $prepareDatas['ligneCommandes'] = !empty($commande) ? $entityManager->getRepository(LigneCommande::class)->findBy([ 'idCommande' => $commande->getId() ]) : [];
        }else{
            $prepareDatas['produits'] = $session->get(SessionKeys::COMMAND_ROW);
        }

        $prepareDatas['pathInfo'] = $pathInfo;

        return $this->render('layout/navbar.html.twig', $prepareDatas);
    }

    /**
     * @Route("search", name="app_search", methods={"GET"})
     *
     * @param Request $request
     * @return false|string|Response
     */
    public function search(Request $request) {

        // Formatte une réponse en json
        $response = new Response();
        $json = $request->headers->get('Content-Type') == 'application/json';
        if ($json) {
            $response->headers->set('Content-Type', $request->headers->get('Content-Type'));
        }

        $em = $this->getDoctrine()->getManager();

        $query = $request->query->get('q');
        if (empty($query)) {
            return $json ? $response->setStatusCode('404')->setContent(json_encode([
                'error' => true,
                'message' => 'Query argument is missing.'
            ])) : $this->render('app/search.html.twig');
        }

        // Handle scope
        $scopes = !empty($request->query->get('scope')) ? explode(',', $request->query->get('scope')) : null;
        $results = [];

        $limitstring = $request->query->get('limit');
        $limit = !empty($limitstring) && is_numeric($limitstring) && intval($limitstring) > 0 ? $request->query->get('limit') : 5;

        if (empty($scopes) || in_array('produit', $scopes)) {
            $produits = $em->getRepository(Produit::class)->applyFilter([ 'wordKeys' => $query, 'limit' => $limit ]);
            $results['produits'] = $json ? $produits->getQuery()->getResult() : entityToNewFormat($produits->getQuery()->getResult());
        }

        if (empty($scopes) || in_array('magasin', $scopes)) {
            $magasins = $em->getRepository(Magasin::class)->applyFilter([ 'wordKeys' => $query, 'limit' => $limit ]);
            $results['magasins'] = $json ? $magasins->getQuery()->getResult() : entityToNewFormat($magasins->getQuery()->getResult());
        }

        return $json ? $response->setContent(entityToNewFormat($results)) : $this->render('app/search.html.twig', $results);
    }

}
