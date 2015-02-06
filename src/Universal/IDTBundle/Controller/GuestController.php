<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Form\RatesType;

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

    public function ratesAction(Request $request)
    {
        $countries = $this->container->getParameter('countries');

        $form = $this->createForm(new RatesType($countries), array('from'=>'US'))
            ->add('search', 'submit');

        $result = array();

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $data = $form->getData();
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $products = $em->createQueryBuilder()
                    ->select("product")
                    ->from('UniversalIDTBundle:Product', 'product')
                    ->where('product.countryISO = :from')->setParameter('from', $data['from'])
                    ->getQuery()
                    ->getResult();

                /** @var Product $product */
                foreach($products as $product) {
                    foreach($product->getAllRates() as $rate) {
                        if(isset($rate['des']) && $rate['des'] == $data['destination'])
                            $result[] = array('product'=>$product, 'rate'=>$rate);
                    }
                }
            }
        }

        return $this->render(
            'UniversalIDTBundle:Guest:rates.html.twig',
            array(
                'form' => $form->createView(),
                'result' => $result
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
