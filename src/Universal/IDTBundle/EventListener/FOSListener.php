<?php

namespace Universal\IDTBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class FOSListener implements EventSubscriberInterface
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePassword',
        );
    }

    public function onRegistrationConfirm(GetResponseUserEvent $event)
    {
        if (null !== $event->getRequest()->cookies->get("products") &&
            null !== $event->getRequest()->cookies->get("products_currency")
        ) {
            $event->setResponse(new RedirectResponse($this->router->generate('user_checkout')));
        }
    }

    public function onChangePassword(FormEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_change_password')));
    }
}