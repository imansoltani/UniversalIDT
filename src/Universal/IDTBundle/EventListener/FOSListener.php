<?php

namespace Universal\IDTBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
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
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
//            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationConfirm',
        );
    }

    public function onRegistrationInitialize(UserEvent $event)
    {
        $form_request = $event->getRequest()->request->get("fos_user_registration_form");
        if(isset($form_request["email"]) && "" != $email = $form_request["email"])
            $form_request["username"] = $email;

        $event->getRequest()->request->set("fos_user_registration_form", $form_request);
    }

    public function onRegistrationConfirm(FormEvent $event)
    {
        if (null !== $event->getRequest()->cookies->get("products") &&
            null !== $event->getRequest()->cookies->get("products_currency")
        ) {
            $event->setResponse(new RedirectResponse($this->router->generate('checkout_checkout')));
        }
    }
}