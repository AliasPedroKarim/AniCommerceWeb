<?php


namespace App\Service;


use App\Utils\SessionKeys;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProduitSessionService {

    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;

        $this->init();
    }

    public function init(): void {
        if (null === $this->session->get(SessionKeys::FILTER_PRODUCT)) {
            $this->session->set(SessionKeys::FILTER_PRODUCT, []);
        }

        if (null === $this->session->get(SessionKeys::COMMAND_ROW)) {
            $this->session->set(SessionKeys::COMMAND_ROW, []);
        }
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session): void {
        $this->session = $session;
    }
    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

}