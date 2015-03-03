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
                    'route' => 'user_home', 'extras' => array('icon' => 'home')
                ));
        else
            $menu->addChild('Home', array(
                    'route' => 'WebPage_main', 'extras' => array('icon' => 'home')
                ));

        if($granted) {
            $menu->addChild('My PINs', array(
                    'route' => 'user_pins', 'extras' => array('icon' => 'th')
                ));
            $menu->addChild('Billing History', array(
                    'route' => 'user_orders', 'extras' => array('icon' => 'copy')
                ));
        }

        $menu->addChild('Calling Cards', array(
                'route' => 'WebPage_main', 'extras' => array('icon' => 'credit-card', 'route_label' => 'calling-cards')
            ));
        $menu->addChild('Rates', array(
                'route' => 'WebPage_main', 'extras' => array('icon' => 'external-link', 'route_label' => 'rates')
            ));
        $menu->addChild('Help', array(
                'route' => 'WebPage_main', 'extras' => array('icon' => 'info-sign', 'route_label' => 'contact')
            ));

        if($granted)
            $menu->addChild('Checkout', array('route' => 'user_checkout', 'extras' => array('icon' => 'check')));
        else
            $menu->addChild('Checkout', array('route' => 'WebPage_checkout', 'extras' => array('icon' => 'check')));

        return $menu;
    }
}