<?php

namespace Malkos\UserBundle\Handler;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OAuthFailureHandler implements AuthenticationFailureHandlerInterface {

    private $router;

    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    public function onAuthenticationFailure( Request $request, AuthenticationException $exception) {

        if ( !$exception instanceof AccountNotLinkedException ) {
            throw $exception;
        }

        return new RedirectResponse( $this->router->generate('malkos_user_servicelogin') );

    }

}
