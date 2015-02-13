<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType;
use Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Form\CheckoutType;

class CheckoutController extends Controller
{
    private $BASKET_BUY = "buy";
    private $BASKET_RECHARGE = "recharge";

    public function basketAction()
    {
        return $this->render('UniversalIDTBundle:Checkout:basket.html.twig');
    }

    public function checkoutAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new CheckoutType());

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                $formData = $form->getData();
                return $this->forward("UniversalIDTBundle:Checkout:placeOrder", array('formData' => $formData));
            }
        }

        //read from cookie
        $added_items = json_decode(stripcslashes($request->cookies->get("products")), true);
        $added_items_currency = $request->cookies->get("products_currency");

        //check cookie validation
        $valid = $this->checkCookie($added_items, $added_items_currency, $em);

        //unset cookie if not valid
        if(!$valid) {
            $response = new Response("Error in cookies and cleared.");
            $this->get('session')->getFlashBag()->add('error','Error occurred in Cookies.');
            $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
            $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

            return $response;
        }

        //write in session
        $this->get('session')->set('basket', stripcslashes($request->cookies->get("products")));
        $this->get('session')->set('basket_currency', $added_items_currency);

        return $this->render('UniversalIDTBundle:Checkout:checkout.html.twig', array(
                'data' => $added_items,
                'form' => $form->createView()
            ));
    }

    public function ogoneResultAction()
    {
        return new Response('returned from ogone');
    }

    public function sofortResultAction()
    {
        return new Response('returned from sofort');
    }

    /**
     * @param array $added_items
     * @param string $added_items_currency
     * @param EntityManager $em
     * @return bool
     */
    private function checkCookie(array &$added_items, $added_items_currency, EntityManager $em)
    {
        if(is_null($added_items_currency) || !is_string($added_items_currency) || strlen($added_items_currency) != 3)
            return false;

        if(is_null($added_items) || !is_array($added_items))
            return false;
//        die("is null");

        foreach($added_items as &$added_item) {
            if(
                !isset($added_item['product']) ||
                !isset($added_item['count']) ||
                !isset($added_item['denomination']) ||
                !in_array($added_item['type'], array($this->BASKET_BUY, $this->BASKET_RECHARGE))
            )
                return false;
//                die("field error");

            switch ($added_item['type']) {
                case $this->BASKET_BUY:
                    /** @var Product $row */
                    $row = $em->getRepository('UniversalIDTBundle:Product')->find($added_item['product']);
                    if(!$row) return false;

                    $added_item['product'] = $row;

                    if($row->getCurrency() !== $added_items_currency) return false;

                    if(!in_array($added_item['denomination'], $row->getDenominations())) return false;
                    break;

                case $this->BASKET_RECHARGE:
                    /** @var OrderProduct $row */
                    $row = $em->getRepository('UniversalIDTBundle:OrderProduct')->find($added_item['product']);
                    if(!$row) return false;

                    $added_item['product'] = $row;

                    if($row->getProduct()->getCurrency() !== $added_items_currency) return false;

                    if(!in_array($added_item['denomination'], $row->getProduct()->getDenominations())) return false;
                    break;
            }
        }

        return true;
    }

    public function placeOrderAction(array $formData)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //read for session
        $basket = json_decode($this->get('session')->get('basket'), true);
        $basket_currency = $this->get('session')->get('basket_currency');

        //calc sum amount
        $amount = 0;
        foreach($basket as $row)
            $amount += $row['count'] * $row['denomination'];

        //create order_detail
        $order_detail = new OrderDetail();
        $order_detail->setCurrency($basket_currency);
        $order_detail->setAmount($amount);
        $order_detail->setDeliveryEmail($formData['email']);
        $order_detail->setDeliverySMS($formData['sms']);
        $order_detail->setPaymentMethod($formData['method']);
        $order_detail->setPaymentStatus(PaymentStatusEnumType::STATUS_PENDING);
        if($this->getUser()) $order_detail->setUser($this->getUser());
        $em->persist($order_detail);

        //create order_products
        foreach($basket as $row) {
            $order_product = new OrderProduct();
            $order_product->setCount($row['count']);
            $order_product->setOrderDetail($order_detail);
            $order_product->setPinDenomination($row['denomination']);
            $order_product->setRequestStatus(RequestStatusEnumType::PENDING);
            switch ($row['type']) {
                case $this->BASKET_BUY:
                    $order_product->setRequestType(RequestTypeEnumType::ACTIVATION);
                    $order_product->setProduct($em->getRepository('UniversalIDTBundle:Product')->find($row['product']));
                    break;

                case $this->BASKET_RECHARGE:
                    $order_product->setRequestType(RequestTypeEnumType::RECHARGE);
                    $old_order_product = $em->getRepository('UniversalIDTBundle:OrderProduct')->find($row['product']);
                    $order_product->setProduct($old_order_product->getProduct());
                    $order_product->setPin($old_order_product->getPin());
                    break;
            }
            $em->persist($order_product);
        }

        $em->flush();

        //go to redirect page for methods
        switch($order_detail->getPaymentMethod()) {
            case PaymentMethodEnumType::OGONE:
                $client = $this->get('client_ogone');
                return $this->render("UniversalIDTBundle:Checkout:redirectToOgone.html.twig", array(
                        'form' => $client->generateForm($order_detail)
                    ));
            case PaymentMethodEnumType::SOFORT:
                break;
            default:
                break;
        }

        return new Response("done");
    }
}
