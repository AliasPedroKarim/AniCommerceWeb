<?php

namespace App\Controller;

use App\Form\UploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportExportController extends AbstractController {

    /**
     * @Route("/import/", name="import", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function import(Request $request) {
        $form = $this->createForm(UploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $upload = $form->get('upload')->getData();

            if ($upload) {
                try {
                    $newFilename = slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' .$upload->getClientOriginalExtension();

                    // ECRIT LE DANS DOCS QUE TU AS BESOIN D'UN PATH ABSOLU
                    $upload->move($request->server->get('DOCUMENT_ROOT') . '/ppe_4/storage', $newFilename);

                    //ouvrir le fichier en lecture (qui doit être du json)
                    //lire son contenu dans la variable $importJSON
                    $content = file_get_contents($request->server->get('DOCUMENT_ROOT') . '/ppe_4/storage/' . $newFilename);

                    //parser le contenu avec json_decode($importJSON) dans la variable $lesClients

                    if (in_array($upload->getClientMimeType(), ['text/xml', 'application/xml', 'application/xml+html'])) {
                        $xml = simplexml_load_string($content);
                        $content = json_encode($xml);
                    }

                    $data = json_decode($content, true);

                    $em = $this->getDoctrine()->getManager();

                    $dataUpdate = hydrate($data, $this->getUser());
                    if (isset($data['idGenre'])) {
                        $dataUpdate->setIdGenre(hydrate($data['idGenre'], $dataUpdate->getIdGenre()));
                    }
                    if (isset($data['idImage'])) {
                        $dataUpdate->setIdImage(hydrate($data['idImage'], $dataUpdate->getIdImage()));
                    }
                    if (isset($data['idRole'])) {
                        $dataUpdate->setIdRole(hydrate($data['idRole'], $dataUpdate->getIdRole()));
                    }

                    $em->persist($dataUpdate);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set(
                        'success',
                        "Votre fichier à bien été prise en compte, et le modification requis on était appliqué."
                    );
                } catch (FileException $ex) {
                    $this->get('session')->getFlashBag()->set(
                        'danger',
                        "Une erreur s'est produite lors de l'importation du fichier, réessayer et si le problème persiste, veuillez s'il vous plaît contacter un administrateur et lui signaler cette défaillance !"
                    );
                }
            }
        } else {
            $this->get('session')->getFlashBag()->set(
                'danger',
                "Ohh ! Il semblerait que votre fichier n'est pas valide."
            );
        }

        return $this->redirectToRoute('utilisateur_profile');
    }

    /**
     * @Route("/export/{_format}", name="export", defaults={"_format"="xml"}, requirements={"_format"="xml|json|csv|yaml"}, methods={"GET"})
     * @param string $_format
     * @return string|Response
     */
    public function export(string $_format) {
        return entityToNewFormat($this->getUser(), 'response', $_format, [ 'download' => true, 'filename' => slug($this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() . ' profile') ]);
    }
}
