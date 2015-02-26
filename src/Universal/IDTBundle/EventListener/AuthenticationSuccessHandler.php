<?php

namespace Universal\IDTBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $router;

    public function __construct(RouterInterface $router, HttpUtils $httpUtils, array $options = array())
    {
        parent::__construct($httpUtils, $options);

        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if(null !== $request->cookies->get("products") && null !== $request->cookies->get("products_currency") )
            return new RedirectResponse($this->router->generate('user_checkout'));

        return parent::onAuthenticationSuccess($request, $token);
    }
}