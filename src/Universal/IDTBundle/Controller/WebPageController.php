<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Entity\Rate;
use Universal\IDTBundle\Form\BasketType;
use Universal\IDTBundle\Form\RatesType;

class WebPageController extends Controller
{
    public function mainAction()
    {
        return $this->render('UniversalIDTBundle:WebPage:layout.html.twig');
    }

    public function sliderAction()
    {
        return $this->render('UniversalIDTBundle:WebPage:slider.html.twig');
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
            'UniversalIDTBundle:WebPage:callingCards.html.twig',
            array(
                'countries' => $countriesResult
            )
        );
    }

    public function cardsListAction(Request $request)
    {
        $country = strtoupper($request->query->get('country'));

        if(strlen($country) != 2)
            throw $this->createNotFoundException('Invalid Country ISO.');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $countries = $this->container->getParameter('countries');

        if(!isset($countries[$country]))
            throw $this->createNotFoundException('Invalid Country ISO.');

        $products = $em->getRepository('UniversalIDTBundle:Product')->findBy(array('countryISO'=>$country));

        return $this->render(
            'UniversalIDTBundle:WebPage:cardsList.html.twig',
            array(
                'products' => $products
            )
        );
    }

    public function cardDetailsAction(Request $request)
    {
        $id = $request->query->get('id');

        if(!is_numeric($id))
            throw $this->createNotFoundException('Invalid Card ID.');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('UniversalIDTBundle:Product')->find($id);

        if(!$product)
            throw $this->createNotFoundException('Invalid Card ID.');

        return $this->render(
            'UniversalIDTBundle:WebPage:cardDetails.html.twig',
            array(
                'product' => $product
            )
        );
    }

    public function ratesAction(Request $request)
    {
        $countries = $this->container->getParameter('countries');

        $form = $this->createForm(new RatesType($countries), array('from'=>'US'))
            ->add('search', 'submit');

        /** @var Rate[] $rates */
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

                    /** @var Rate[] $rates */
                    $rates = $em->createQueryBuilder()
                        ->select('rate', 'destination')
                        ->from('UniversalIDTBundle:Rate', 'rate')
                        ->where('rate.classId = :class_id')->setParameter('class_id', $product->getClassId())
                        ->innerJoin('rate.destination', 'destination')
                        ->getQuery()->getResult();

                    $result = array_merge($result, $rates);
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

    public function basketAction()
    {
        $form = $this->createForm(new BasketType($this->getUser()), null, array(
                'method' => 'get',
                'action' => $this->getUser() ? $this->generateUrl('user_checkout') : $this->generateUrl('WebPage_checkout')
            ));

        return $this->render('UniversalIDTBundle:WebPage:basket.html.twig', array(
                'form' => $form->createView()
            ));
    }
}
