<?php

namespace Universal\IDTBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\LoggingTranslator;
use Universal\IDTBundle\Entity\User;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class FOSListener implements EventSubscriberInterface
{
    protected $router;

    protected $breadcrumbs;

    protected $translator;

    public function __construct(RouterInterface $router, Breadcrumbs $breadcrumbs, LoggingTranslator $translator)
    {
        $this->router = $router;
        $this->breadcrumbs = $breadcrumbs;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess',
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess',
            FOSUserEvents::CHANGE_PASSWORD_INITIALIZE => 'onChangePasswordInitialize',
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

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_change_password')));
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.index',[],'application'), $this->router->generate("user_home"));
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.user',[],'application'));
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.user_settings',[],'application'));
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getData();
        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_profile_show', array('_locale'=>$user->getLanguage()))));
    }

    public function onChangePasswordInitialize(GetResponseUserEvent $event)
    {
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.index',[],'application'), $this->router->generate("user_home"));
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.user',[],'application'));
        $this->breadcrumbs->addItem($this->translator->trans('menu.breadcrumbs.user_passwordChange',[],'application'));
    }
}