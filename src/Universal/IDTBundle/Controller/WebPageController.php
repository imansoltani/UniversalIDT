<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Entity\Rate;
use Universal\IDTBundle\Form\BasketType;
use Universal\IDTBundle\Form\ContactType;
use Universal\IDTBundle\Form\RatesType;

class WebPageController extends Controller
{
    public function mainAction()
    {
        return $this->render('UniversalIDTBundle:WebPage:layout.html.twig', array('main_nav_bar'=>''));
    }

    //---------------------------------

    public function sliderAction()
    {
        return $this->render('UniversalIDTBundle:WebPage:slider.html.twig');
    }

    public function callingCardsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $countries = $this->container->getParameter('countries');
        $defaultCountryISO = strtoupper($this->container->getParameter('country'));
        $defaultCountryName = $countries[$defaultCountryISO];

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
                'countries' => $countriesResult,
                'countryISO' => $defaultCountryISO,
                'countryName' => $defaultCountryName
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

    public function ratesAction()
    {
        $countries = $this->container->getParameter('countries');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var array $from */
        $fromEntity = $em->createQueryBuilder()
            ->select('DISTINCT product.countryISO')
            ->from('UniversalIDTBundle:Product', 'product')
            ->getQuery()->getArrayResult();

        $from_s = array();
        foreach($fromEntity as $from)
            $from_s [$from['countryISO']]= $countries[$from['countryISO']];


        /** @var Rate[] $destinations */
        $destinationsEntity = $em->createQueryBuilder()
            ->select('DISTINCT rate.countryIso', 'rate.countryName')
            ->from('UniversalIDTBundle:Rate', 'rate')
            ->getQuery()->getArrayResult();

        $destinations = array();
        foreach($destinationsEntity as $destination)
            $destinations [$destination['countryIso']]= $destination['countryName'];

        $form = $this->createForm(new RatesType($from_s, $destinations), array('type' => 'LAC'))
            ->add('search', 'submit', array(
                    "label"=>"home.rates.check",
                    'translation_domain' => 'website',
                ));

        return $this->render(
            'UniversalIDTBundle:WebPage:rates.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    public function ratesResultAction(Request $request)
    {
        $countries = $this->container->getParameter('countries');

        $from = $request->query->get('from');
        $destination = $request->query->get('destination');
        $type = $request->query->get('type');

        if(!$from || !isset($countries[$from]))
            throw $this->createNotFoundException('Invalid Country From.');

        if(!$destination)
            throw $this->createNotFoundException('Invalid Country Destination.');

        if(!$type || !in_array($type, array('LAC', 'TF', 'INT')))
            throw $this->createNotFoundException('Invalid Rate Type.');

        $type = ($type == 'INT') ? 'LAC' : $type;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $products = $em->createQueryBuilder()
            ->select('product as productRow', 'FirstNodeOfArray(product.denominations) as base')
            ->from('UniversalIDTBundle:Product', 'product')
            ->where('product.countryISO = :from')->setParameter('from', $from)
            ->getQuery()->getResult();
//die(var_dump($products));
        $class_ids = array();
        foreach($products as $product) {
            $class_id = $product['productRow']->getClassId();
            if(!isset($class_ids[$class_id]))
                $class_ids[$class_id] = array();
            $class_ids[$class_id] []= $product;
        }
//die(var_dump($class_ids));
        /** @var Rate[] $rates */
        $rates = $em->createQueryBuilder()
            ->select('rate')
            ->from('UniversalIDTBundle:Rate', 'rate')
            ->where('rate.type = :type')->setParameter('type', $type)
            ->andWhere('rate.countryIso = :destination_iso')->setParameter('destination_iso', $destination)
            ->andWhere('rate.classId IN (:class_ids)')->setParameter('class_ids', array_keys($class_ids))
            ->getQuery()->getResult();
//die(var_dump($rates));
        $result = array();
        foreach($rates as $rate) {
            $class_id = $rate->getClassId();
            foreach ($class_ids[$class_id] as $product_row) {
                $result []= array(
                    'product' => $product_row['productRow'],
                    'rate' => $rate,
                    'cost' => ($rate->getCost() * ($product_row['productRow']->getFreeAmountDenomination1() / $product_row['base']))
                );
            }
        }
//die(var_dump($result));
        usort($result, function ($a , $b) {
                if ($a['cost'] == $b['cost']) return 0;
                return ($a['cost'] > $b['cost']) ? -1 : 1;
            });

//        die(var_dump($result));

        return $this->render(
            'UniversalIDTBundle:WebPage:ratesResult.html.twig',
            array(
                'result' => $result,
                'countries' => $countries
            )
        );
    }

    public function applicationAction()
    {
        return $this->render(
            'UniversalIDTBundle:WebPage:application.html.twig'
        );
    }

    public function downloadAction()
    {
        return $this->render(
            'UniversalIDTBundle:WebPage:download.html.twig'
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

    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactType());

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                try {
                    $this->get('EmailService')->sendEmailMessage(
                        $this->render(
                            'UniversalIDTBundle:Mails:contact.email.html.twig',
                            array(
                                'name' => $form->get('name')->getData(),
                                'email' => $form->get('email')->getData(),
                                'message' => $form->get('message')->getData(),
                            )
                        )->getContent(),
                        $this->container->getParameter('mailer_sender_address'),
                        $this->container->getParameter('mailer_support')
                    );

                    $this->get('EmailService')->sendEmailMessage(
                        $this->render(
                            'UniversalIDTBundle:Mails:contact_confirm.email.html.twig',
                            array(
                                'name' => $form->get('name')->getData()
                            )
                        )->getContent(),
                        $this->container->getParameter('mailer_sender_address'),
                        $form->get('email')->getData()
                    );

                    return new Response('S');
                } catch (\Exception $e) {
                    return new Response('F');
                }
            }

            return new Response('F');
        }

        return $this->render('UniversalIDTBundle:WebPage:contact.html.twig', array(
                'form' => $form->createView()
            ));
    }
}
