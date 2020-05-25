<?php

namespace App\Controller;

use App\Entity\AssocierCategorie;
use App\Entity\Commande;
use App\Entity\HistoryCommand;
use App\Entity\LigneCommande;
use App\Entity\Resider;
use App\Form\CommandeType;
use App\Form\PaymentCheckoutType;
use App\Repository\CommandeRepository;
use App\Utils\Paypal\OrderPaypal;
use App\Utils\Paypal\PaypalClient;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ObjectManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("/commande")
 */
class CommandeController extends AbstractController
{
    private $projectDir;
    private $slugger;

    public function __construct(string $projectDir) {
        $this->projectDir = $projectDir;
        $this->slugger = new AsciiSlugger();
    }

    /**
     * @Route("/", name="commande_index", methods={"GET"})
     * @param CommandeRepository $commandeRepository
     * @return Response
     */
    public function index(CommandeRepository $commandeRepository): Response {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy([ 'idUtilisateur' => $this->getUser(), 'panier' => false ]),
        ]);
    }

    /**
     * @Route("/new", name="commande_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="commande_show", methods={"GET"})
     * @param Commande $commande
     * @return Response
     */
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="commande_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Commande $commande
     * @return Response
     */
    public function edit(Request $request, Commande $commande): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('commande_index');
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/facture",name="commande_facture",methods={"GET"})
     * @param Request $request
     * @param Commande $commande
     * @return Response
     */
    public function downloadFacture(Request $request, Commande $commande) {
        if ($commande->getIdUtilisateur() !== $this->getUser()) {
            $this->get('session')->getFlashBag()->set(
                'danger',
                'Oops ! vous n\'avez pas la permission pour faire cette action.'
            );
            return $this->redirectToRoute('commande_index');
        }

        try {
            $fileContent = file_get_contents($commande->getCheminFacture());
        }catch (\ErrorException $exception) {
            $fileContent = null;
        }

        if (empty($commande->getCheminFacture()) || empty($fileContent)) {
            $this->get('session')->getFlashBag()->set(
                'danger',
                'il n\'y a pas de facture sur cette commande !'
            );
            return $this->redirectToRoute('commande_index');
        }

        $response = new Response(file_get_contents($commande->getCheminFacture()));
        $path = explode('/', $commande->getCheminFacture());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $path[count($path) - 1]
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * @Route("/{id}", name="commande_delete", methods={"DELETE"})
     * @param Request $request
     * @param Commande $commande
     * @return Response
     */
    public function delete(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('commande_index');
    }

    /**
     * @Route("/paiement/step/{number}", name="commande_paiement_step",methods={"GET","POST"})
     *
     * @param Request $request
     * @param string $number
     * @return Response
     * @throws Exception
     */
    public function paymentStep(Request $request, string $number) {
        if ($this->getUser()->isDisabled()) {
            return $this->redirectToRoute('home');
        }

        if (empty($this->getUser())) {
            $link = $this->generateUrl(
                'utilisateur_register', [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $this->get('session')->getFlashBag()->set(
                'danger',
                "Vous devait être connecter pour pouvoir passer une commande !<br>Si vous n'avez pas de compte veuillez suivre le lien <a href='{$link}'>ci-contre</a>..."
            );

            return $this->redirectToRoute('ligne_commande_index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $commande = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'idUtilisateur' => $this->getUser()->getId() ]);

        if (empty($commande)) {
            return $this->redirectToRoute('ligne_commande_index');
        }

        $ligneCommandes = $entityManager->getRepository(LigneCommande::class)->findBy([ 'idCommande' => $commande->getId() ]);

        if (empty($ligneCommandes)) {
            return $this->redirectToRoute('ligne_commande_index');
        }

        $residerRepository = $entityManager->getRepository(Resider::class)->findBy(['idUtilisateur' => $this->getUser()->getId()]);

        $residerOption = $this->determinateResider();

        $defaut = [];
        if ($commande->getDateLivraison()) {
            $defaut['dateShipping'] = $commande->getDateLivraison();
        }

        $form = PaymentCheckoutType::build($this->createFormBuilder($defaut))->getForm();
        $form->handleRequest($request);

        switch ($number) {
            case '1':
                return $this->render('commande/paiement/step_1.html.twig', [
                    'commande' => $commande,
                    'ligne_commandes' => $ligneCommandes,
                    'resider' => $residerOption,
                    'residers' => entityToNewFormat($residerRepository),
                    'form' => $form->createView()
                ]);
            case '2':
                if ($form->isSubmitted() && $this->isCsrfTokenValid('commande.' . $commande->getId(), $request->request->get('_token'))) {
                    return $this->render('commande/paiement/step_2.html.twig', [
                        'commande' => $commande,
                        'old_data' => $form->getData(),
                        'ligne_commandes' => $ligneCommandes,
                        'form' => PaymentCheckoutType::build($this->createFormBuilder($form->getData()))->getForm()->createView()
                    ]);
                }
                break;
            case '3':
                if ($form->isSubmitted() && $form->isValid()) {
                    $commande->setMethodePaiement($form->getData()['paymentMethod']);
                    $commande->setDateLivraison($form->getData()['dateShipping']);
                    $entityManager->persist($commande);
                    $entityManager->flush();

                    return $this->render('commande/paiement/step_3.html.twig', [
                        'commande' => $commande,
                        'old_data' => $form->getData(),
                    ]);
                }
                break;
            case '4':
                if (!empty($commande)) {
                    $historyCommand = $entityManager->getRepository(HistoryCommand::class)
                        ->findWithUser(['idCommand' => $commande->getId(), 'idUtilisateur' => $this->getUser()->getId()]);

                    $orders = null;
                    if ($historyCommand->getIdCommand()->getMethodePaiement() == 'paypal') {
                        $orders = OrderPaypal::getOrder($historyCommand->getIdPayment(), 'response');
                    }

                    if (!empty($orders)) {
                        if (empty($commande->getMethodePaiement()) || $commande->getMethodePaiement() == 'paypal' && isset($orders->result) && isset($orders->result->status) && $orders->result->status != 'COMPLETED') {
                            return $this->redirectToRoute('ligne_commande_index');
                        }

                        if (!empty($historyCommand) && $historyCommand->getActivity() == true) {
                            $historyCommand->setActivity(false);

                            $commande->setPanier(false);
                            $commande->setCheminFacture($this->buildFacture($ligneCommandes, $historyCommand, $orders));

                            $entityManager->persist($commande);
                            $entityManager->persist($historyCommand);
                            $entityManager->flush();

                            return $this->render('commande/paiement/step_4.html.twig', [
                                'historyCommand' => $historyCommand
                            ]);
                        }
                    }
                }
                break;
            default:
        }

        return $this->redirectToRoute('ligne_commande_index');
    }

    /**
     * Cette function permet de générer le PDF de la facture et ensuite retourne en chaine de caractère le chemin de la sauvegarde
     *
     * @param array<LigneCommande> $ligneCommandes
     * @param HistoryCommand $historyCommand
     * @param $orders
     * @return string
     * @throws Exception
     */
    private function buildFacture(array $ligneCommandes, HistoryCommand $historyCommand, $orders) {
        $sourceDir = $this->projectDir . '/storage/app/public';

        $dompdf = new Dompdf(new Options([
            'defaultFont' => 'Arial'
        ]));

        $dompdf->loadHtml($this->renderView('commande/facture_pdf.html.twig', [
            'ligneCommandes' => $ligneCommandes,
            'historyCommand' => $historyCommand,
            'orders' => $orders
        ]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $path = $sourceDir . '/' .
            $this->slugger->slug('facture commande ' . $historyCommand->getIdCommand()->getId() . ' ' . $historyCommand->getIdCommand()->getDateCde()->format('Y-m-d'), '_')->toString() . '.pdf';

        file_put_contents($path, $dompdf->output());

        return $path;
    }

    /**
     * @Route("/paiement/{method}", name="commande_paiement",methods={"POST"})
     * @param Request $request
     * @param string $method
     * @return Response
     * @throws NonUniqueResultException
     */
    public function payment(Request $request, string $method = null) {
        switch ($method) {
            case "paypal":
                return $this->getOrCreatePaymentPaypal($request);
                break;
            default:
                return $this->redirectToRoute('ligne_commande_index');
        }
    }

    /**
     * @return Resider|bool|object|null
     */
    public function determinateResider() {
        $entityManager = $this->getDoctrine()->getManager();
        $residerRepository = $entityManager->getRepository(Resider::class)->findBy(['idUtilisateur' => $this->getUser()->getId()]);
        $residerOption = null;

        if (!empty($residerRepository)) {
            $counter = 0;

            foreach ($residerRepository as $resider) {
                if ($resider->getDefaut()) {
                    $residerOption = $resider;
                    break;
                }
                if ($counter === count($residerRepository) - 1) {
                    $residerOption = $residerRepository[0];
                }
                $counter++;
            }
            return $residerOption;
        }

        return false;
    }

    /**
     *  D'abord commande ensuite
     * @param ObjectManager $entityManager
     * @return array|bool
     * @throws NonUniqueResultException
     */
    public function checkPanier(ObjectManager $entityManager) {
        $commande = $entityManager->getRepository(Commande::class)->findOneBy([ 'panier' => true, 'idUtilisateur' => $this->getUser()->getId() ]);
        if (empty($commande)) {
            $this->get('session')->getFlashBag()->set(
                'danger',
                "Il semblerait n'avait aucun panier pour pouvoir passer une commande."
            );
            return false;
        }

        $ligneCommandes = $entityManager->getRepository(LigneCommande::class)->findBy([ 'idCommande' => $commande->getId() ]);
        if (empty($ligneCommandes)) {
            $this->get('session')->getFlashBag()->set(
                'danger',
                "Il semblerait qu'il n'y a aucun produit dans ce panier."
            );
            return false;
        }

        $historyCommand = $entityManager->getRepository(HistoryCommand::class)->findWithUser(['idCommand' => $commande->getId(), 'idUtilisateur' => $this->getUser()->getId()]);
        return [$commande, $ligneCommandes, $historyCommand];
    }

    /**
     * @see https://developer.paypal.com/docs/api/orders/v2/ for documentation
     *
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function getOrCreatePaymentPaypal(Request $request) {
        $response = new Response();
        $json = $request->headers->get('Content-Type') == 'application/json';
        if ($json == false) {
            $response->headers->set('Content-Type', $request->headers->get('Content-Type'));
        }

        // Fix la date
        $content = json_decode($request->getContent());
        if (isset($content->dateShipping) && !empty($content->dateShipping)) {
            $content->dateShipping = new \DateTime($content->dateShipping->date);
        }

        $form = PaymentCheckoutType::build($this->createFormBuilder($content))->getForm();
        if ($form->isSubmitted() && !$form->isValid()) {
            return $json ? $response->setContent(json_encode([ 'error' => true, 'message' => 'this payload is not defined !' ], JSON_PRETTY_PRINT)) : $this->redirectToRoute('ligne_commande_index');
        }

        // On commence la verification si il y a un panier valide

        $entityManager = $this->getDoctrine()->getManager();

        $check = $this->checkPanier($entityManager);

        if (empty($check)) {
            return $this->redirectToRoute('ligne_commande_index');
        }

        $commande = $check[0];
        $ligneCommandes = $check[1];
        $historyCommand = $check[2];

        $orders = null;

        if (!empty($historyCommand)) {
            if ($historyCommand->getActivity() === true) {
                $orders = OrderPaypal::getOrder($historyCommand->getIdPayment(), 'response');
            }else{
                return $this->redirectToRoute('ligne_commande_index');
            }
        } else {
            // Ici je commence la construction des données à envoyer à paypal
            $body = [
                'intent' => PaypalClient::INTENT_AUTHORIZE,
                'payer' => [
                    'name' => [
                        'given_name' => $content->firstName
                    ],
                    'email_address' => $content->email,
                    'address' => [
                        'address_line_1' => $content->address,
                        'address_line_2' => $content->compl,
                        'admin_area_2' => $content->country,
                        // 'admin_area_1' => 'CA',
                        'postal_code' => $content->zip,
                        'country_code' => 'FR',
                    ],
                ],
                'application_context' => [
                    'brand_name' => $_ENV['APP_NAME'],
                    'locale' => 'fr-FR',
                    'landing_page' => 'BILLING',
                    'shipping_preferences' =>  $this->generateUrl('home', [ ],UrlGeneratorInterface::ABSOLUTE_URL),
                    'user_action' => 'PAY_NOW', // CONTINUE
                    "cancel_url" => $this->generateUrl('commande_paiement_step', [ 'number' => 4 ], UrlGeneratorInterface::ABSOLUTE_URL),
                    "return_url" => $this->generateUrl('commande_paiement_step', [ 'number' => 4 ], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                'purchase_units' => [
                    0 => [
                        'reference_id' => 'Commande' . $commande->getId(),
                        'description' => 'Produits dérivés de manga',
                        'custom_id' => $this->slugger->slug($_ENV['APP_NAME'])->toString(),
                        'soft_descriptor' => 'Produits dérivés',
                        'items' =>[ ],
                        'shipping' => [
                            'method' => 'MangaAnimation Postal Service',
                            'address' => [
                                'address_line_1' => $content->address,
                                'address_line_2' => $content->compl,
                                'admin_area_2' => $content->country,
                                // 'admin_area_1' => 'CA',
                                'postal_code' => $content->zip,
                                'country_code' => 'FR',
                            ],
                        ],
                    ],
                ],
            ];

            $total = 0;

            foreach ($ligneCommandes as $ligneCommande) {
                $ACRepository = $entityManager->getRepository(AssocierCategorie::class)->findOneBy([ 'idProduit' => $ligneCommande->getIdProduit()->getId() ]);
                $temp = [
                    'name' => $ligneCommande->getIdProduit()->getLibelle(),
                    'description' => substr($ligneCommande->getIdProduit()->getDescription(), 0, 124) . '...',
                    // 'sku' => 'sku01',
                    'unit_amount' => [
                        'currency_code' => 'EUR',
                        'value' => $ligneCommande->getIdProduit()->getPrixHt(),
                    ],
                    'tax' => [
                        'currency_code' => 'EUR',
                        'value' => '00.00',
                    ],
                    'quantity' => $ligneCommande->getQuantite(),
                ];

                if (!empty($ACRepository)) {
                    // PHYSICAL_GOODS | DIGITAL_GOODS
                    $temp['category'] = "PHYSICAL_GOODS";
                }
                $body['purchase_units'][0]['items'][] = $temp;

                $total += $ligneCommande->getPrixUnitaire() * $ligneCommande->getQuantite();
            }

            $body['purchase_units'][0]['amount'] = [
                'currency_code' => 'EUR', // EUR
                'value' => $total,
                'breakdown' => [
                    'item_total' =>[
                        'currency_code' => 'EUR',
                        'value' => $total,
                    ],
                    'shipping' => [
                        'currency_code' => 'EUR',
                        'value' => '00.00',
                    ],
                    'handling' => [
                        'currency_code' => 'EUR',
                        'value' => '00.00',
                    ],
                    'tax_total' => [
                        'currency_code' => 'EUR',
                        'value' => '00.00',
                    ],
                    'shipping_discount' => [ // Frais de port
                        'currency_code' => 'EUR',
                        'value' => '00.00',
                    ],
                ],
            ];

            $orders = OrderPaypal::createOrder($body, 'response');

            $historyCommand = new HistoryCommand();
            $historyCommand->setIdCommand($commande)
                ->setIdPayment($orders->result->id);
            $entityManager->persist($historyCommand);
            $entityManager->flush();
        }

        if(isset($orders) && !empty($orders) && !empty($orders->result)) {
            return $json ? $response->setContent(json_encode($orders->result, JSON_PRETTY_PRINT)) : $this->redirectToRoute('commande_paiement_step', ['number' => 4]);
        }

        return $this->redirectToRoute('ligne_commande_index');
    }

    /**
     * @Route("/paypal-transaction-complete",name="paypal_transaction_complete",methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function paypalCompletePayment(Request $request) {
        $response = new Response();
        $json = $request->headers->get('Content-Type') == 'application/json';
        if ($json == false) {
            $response->headers->set('Content-Type', $request->headers->get('Content-Type'));
        }

        $entityManager = $this->getDoctrine()->getManager();

        $check = $this->checkPanier($entityManager);

        // On doit recevoir 2 informations ici orderID, authorizationID
        $content = json_decode($request->getContent());

        if (empty($check) && !isset($content->orderID) && !isset($content->authorizationID) && empty($content->orderID) && isset($content->authorizationID)) {
            return $json ? $response->setContent(json_encode([ 'error' => true, 'message' => 'this payload is not defined !' ], JSON_PRETTY_PRINT)) : $this->redirectToRoute('ligne_commande_index');
        }

        $captureAuthorisation = OrderPaypal::captureAuth($content->authorizationID, 'response');

        if (!empty($captureAuthorisation) && ( $captureAuthorisation->result && $captureAuthorisation->result->status == 'COMPLETED' || $captureAuthorisation->error == true && $captureAuthorisation->cause == "AUTHORIZATION_ALREADY_CAPTURED" )) {
            return $json ? $response->setContent(json_encode([ 'error' => false, 'message' => 'Payment complete !', 'data' => $captureAuthorisation->result], JSON_PRETTY_PRINT)) : $this->redirectToRoute('ligne_commande_index');
        }

        return $json ? $response->setContent(json_encode([ 'error' => true, 'message' => 'this payload is not defined !' ], JSON_PRETTY_PRINT)) : $this->redirectToRoute('ligne_commande_index');
    }

    public static function getCA($commandes) {
        $price = 0;
        foreach ($commandes as $commande) {
            $price += array_reduce($commande->getLigneCommandes()->map(function($ligneCommande) { return $ligneCommande->getPrixUnitaire(); })->toArray(),
                function ($ac, $prix) {
                    return $ac + $prix;
                }, 0);
        }
        return $price;
    }

}
