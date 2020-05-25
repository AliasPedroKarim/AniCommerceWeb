<?php


namespace App\Security;

use App\Entity\Utilisateur as AppUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {

    private $router;

    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    /**
     * Checks the user account before authentication.
     *
     * @param UserInterface $user
     */
    public function checkPreAuth(UserInterface $user) {



    }

    /**
     * Checks the user account after authentication.
     *
     * @param UserInterface $user
     */
    public function checkPostAuth(UserInterface $user) {


    }
}