<?php

use App\Service\ProduitSessionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\AsciiSlugger;

if(!function_exists('checkCaptcha')) {
    function checkCaptcha(Request $request, ?string $action = 'default') {
        if (!empty($request->get('g-recaptcha_response', []))) {
            $token = $request->get('g-recaptcha_response', []);

            $recaptcha = new \ReCaptcha\ReCaptcha($_ENV['GOOGLE_RECAPTCHA_SECRET']);
            $resp = $recaptcha
                ->setExpectedAction($action)
                ->verify($token);
            if ($resp->isSuccess()) {
                $response = [
                    'success' => true,
                ];
            } else {
                $response = [
                    'success' => false,
                    'messages' => $resp->getErrorCodes()
                ];
            }
        }else{
            $response = [
                'success' => false,
                'messages' => 'Token is not set !'
            ];
        }
        return $response;
    }
}

if(!function_exists('onlyUserOrAdmin')) {
    function onlyUserOrAdmin() {
        //
    }
}

if(!function_exists('entityToNewFormat')) {
    function entityToNewFormat($entity, $type = false, $format = 'json', $options = [ 'download' => false, 'filename' => 'file_name' ]) {
        $encoders = [new XmlEncoder(), new JsonEncoder(), new CsvEncoder(), new YamlEncoder()];

        // Here, i'm define configuration for handle entity with high deep circular > 1
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return method_exists($object, 'getNom') ? $object->getNom() :
                    method_exists($object, 'getId') ? $object->getId() :
                    'unresolved_propriety' ;
            },
        ];

        $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
        $serializer = new Serializer($normalizers, $encoders);

        $content = $serializer->serialize($entity, $format, [
            // Ignore proxy attributes field
            AbstractNormalizer::IGNORED_ATTRIBUTES => ["__initializer__", "__cloner__","__isInitialized__"]
        ]);

        // Pretty-Print format
        if ($format === 'json') {
            $json = json_decode($content);
            $content = json_encode($json, JSON_PRETTY_PRINT);
        }elseif ($format === 'xml') {
            $xml = simplexml_load_string($content);
            $xmlDocument = new DOMDocument('1.0');
            $xmlDocument->preserveWhiteSpace = false;
            $xmlDocument->formatOutput = true;
            $xmlDocument->loadXML($xml->asXML());

            $content = $xmlDocument->saveXML();
        }

        // Retourne une
        if ($type == "response") {
            $response = new Response();
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/' . $format);

            if(isset($options['download']) && !empty($options['download'])) {
                $response->headers->set('Content-Type', 'application/force-download');
                $response->headers->set('Content-Disposition','attachment; filename="' . $options['filename'] . '.' . $format . '"');
            }

            return $response;
        }
        return $content;
    }
}

if(!function_exists('randColor')) {
    function randColor() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}

if(!function_exists('handleSession')) {
    function handleSession(SessionInterface $session) {
        if(!$session->isStarted()) {
            $session->start();
        }
        new ProduitSessionService($session);
    }
}

if(!function_exists('slug')) {
    function slug(...$args) {
        return (new AsciiSlugger())->slug(join(" ", $args), '_')->toString();
    }
}

if(!function_exists('hydrate')) {
    function hydrate(array $donnees, $class) {
        foreach ($donnees as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set'.ucfirst($key);

            // Si le setter correspondant existe.
            if (method_exists($class, $method) && $value !== null && gettype($value) !== "array") {
                // On appelle le setter.
                $class->$method($value);
            }
        }

        return $class;
    }
}

if(!function_exists('sortDate')) {
    function sortDate($data) {
        function sortFunction( $a, $b ) {
            return strtotime($a) - strtotime($b);
        }
        usort($data, "sortFunction");

        return $data;
    }

}

