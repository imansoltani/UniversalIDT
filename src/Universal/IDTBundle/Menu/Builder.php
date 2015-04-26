<?php

namespace Universal\IDTBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function userMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root', array('childrenAttributes' => array('class' => 'nav nav-pills nav-stacked')));

        $menu->addChild('Home', array(
                'route' => 'user_home',
                'extras' => array('icon' => 'home'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.home',[],'application')
            ));

        $menu->addChild('My PINs', array(
                'route' => 'user_pins',
                'extras' => array('icon' => 'th'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.my_pins',[],'application')
            ));

        $menu->addChild('Billing History', array(
                'route' => 'user_orders',
                'extras' => array('icon' => 'copy'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.billing_history',[],'application')
            ));

        $menu->addChild('Calling Cards', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'credit-card', 'route_label' => 'calling-cards'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.calling_cards',[],'application')
            ));

        $menu->addChild('Rates', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'external-link', 'route_label' => 'rates'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.rates',[],'application')
            ));

        $menu->addChild('Help', array(
                'route' => 'WebPage_main',
                'extras' => array('icon' => 'info-circle', 'route_label' => 'contact'),
                'label' => $this->container->get('translator')->trans('menu.sidebar.help',[],'application')
            ));

        return $menu;
    }

    public function mainMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root', array('childrenAttributes' => array('class' => 'nav navbar-nav navbar-right')));

        $menu->addChild('Home', array(
                'route' => 'WebPage_main',
                'label' => $this->container->get('translator')->trans('menu.navbar.home',[],'application')
            ));

        $menu->addChild('Calling Cards', array(
                'route' => 'WebPage_main',
                'extras' => array('route_label' => 'calling_cards'),
                'label' => $this->container->get('translator')->trans('menu.navbar.calling_cards',[],'application')
            ));

        $menu->addChild('Rates', array(
                'route' => 'WebPage_main',
                'extras' => array('route_label' => 'rates'),
                'label' => $this->container->get('translator')->trans('menu.navbar.rates',[],'application')
            ));

        $menu->addChild('Applications', array(
                'route' => 'WebPage_main',
                'extras' => array('route_label' => 'application'),
                'label' => $this->container->get('translator')->trans('menu.navbar.applications',[],'application')
            ));

        $menu->addChild('Basket', array(
                'route' => 'WebPage_main',
                'extras' => array('route_label' => 'basket'),
                'label' => $this->container->get('translator')->trans('menu.navbar.basket',[],'application')
            ));

        $menu->addChild('Contact', array(
                'route' => 'WebPage_main',
                'extras' => array('route_label' => 'contact'),
                'label' => $this->container->get('translator')->trans('menu.navbar.contact',[],'application')
            ));

        return $menu;
    }
}