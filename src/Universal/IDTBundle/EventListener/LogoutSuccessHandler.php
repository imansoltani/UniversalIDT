<?php

namespace Universal\IDTBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $targetUrl;
    protected $httpUtils;

    public function __construct(HttpUtils $httpUtils, $targetUrl = '/')
    {
        $this->httpUtils = $httpUtils;
        $this->targetUrl = $targetUrl;
    }

    public function onLogoutSuccess(Request $request)
    {
        $response = new RedirectResponse($this->httpUtils->generateUri($request, $this->targetUrl));

        $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
        $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

        return $response;
    }
}