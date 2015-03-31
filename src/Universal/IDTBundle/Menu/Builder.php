<?php

namespace Universal\IDTBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory)
    {
        $granted = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');

        $menu = $factory->createItem('root', array('childrenAttributes' => array('class' => 'mainnav')));

        if($granted)
            $menu->addChild('Home', array(
                    'route' => 'user_home',
                    'extras' => array('icon' => 'home'),
                    'label' => $this->container->get('translator')->trans('menu.navbar.home',[],'application')
                ));
        else
            $menu->addChild('Home', array(
                    'route' => 'WebPage_main',
                    'extras' => array('icon' => 'home'),
                    'label' => $this->container->get('translator')->trans('menu.navbar.home',[],'application')
                ));

        if($granted) {
            $menu->addChild('My PINs', array(
                    'route' => 'user_pins',
                    'extras' => array('icon' => 'th'),
                    'label' => $this->container->get('translator')->trans('menu.navbar.my_pins',[],'application')
                ));
            $menu->addChild('Billing History', array(
                    'route' => 'user_orders',
                    'extras' => array('icon' => 'copy'),
                    'label' => $this->container->get('translator')->trans('menu.navbar.billing_history',[],'application')
                ));
        }

        $menu->addChild('Calling Cards', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'credit-card', 'route_label' => 'calling-cards'),
                'label' => $this->container->get('translator')->trans('menu.navbar.calling_cards',[],'application')
            ));
        $menu->addChild('Rates', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'external-link', 'route_label' => 'rates'),
                'label' => $this->container->get('translator')->trans('menu.navbar.rates',[],'application')
            ));
        $menu->addChild('Help', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'info-sign', 'route_label' => 'contact'),
                'label' => $this->container->get('translator')->trans('menu.navbar.help',[],'application')
            ));

        return $menu;
    }
}