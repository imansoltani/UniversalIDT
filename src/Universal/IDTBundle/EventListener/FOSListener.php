<?php

namespace Universal\IDTBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Universal\IDTBundle\Entity\User;

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
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess',

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

    public function onProfileEditSuccess(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getData();
        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_profile_show', array('_locale'=>$user->getLanguage()))));
    }
}