<?php

namespace Universal\IDTBundle\EventListener;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FOSListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
        );
    }

    public function onRegistrationInitialize(UserEvent $event)
    {
        $form_request = $event->getRequest()->request->get("fos_user_registration_form");
        if(isset($form_request["email"]) && "" != $email = $form_request["email"])
            $form_request["username"] = $email;

        $event->getRequest()->request->set("fos_user_registration_form", $form_request);
    }
}