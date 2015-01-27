<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GuestController extends Controller
{
    public function mainAction()
    {
        return $this->render('UniversalIDTBundle:Guest:main.html.twig');
    }

    public function callingCardsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $countries = $this->container->getParameter('countries');

        $countriesISO = $em->createQueryBuilder()
            ->select('product.countryISO')
            ->from('UniversalIDTBundle:Product', 'product')
            ->groupBy('product.countryISO')
            ->getQuery()->getScalarResult();

        $countriesResult = array();
        foreach($countriesISO as $countryISO)
        {
            $countriesResult[$countryISO['countryISO']] = $countries[$countryISO['countryISO']];
        }

        return $this->render(
            'UniversalIDTBundle:Guest:callingCards.html.twig',
            array(
                'countries' => $countriesResult
            )
        );
    }

    public function detailsAction($country)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $countries = $this->container->getParameter('countries');

        if(!isset($countries[$country]))
            throw $this->createNotFoundException('Invalid Country ISO.');

        $products = $em->getRepository('UniversalIDTBundle:Product')->findBy(array('countryISO'=>$country));

        return $this->render(
            'UniversalIDTBundle:Guest:details.html.twig',
            array(
                'products' => $products
            )
        );
    }

    public function ratesAction()
    {
        return $this->render(
            'UniversalIDTBundle:Guest:rates.html.twig',
            array(// ...
            )
        );
    }

    public function shoppingAction()
    {
        return $this->render(
            'UniversalIDTBundle:Guest:shopping.html.twig',
            array(// ...
            )
        );
    }

}
