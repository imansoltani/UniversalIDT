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
use Universal\IDTBundle\Idt\Log;

class CheckoutController extends Controller
{
    private $BASKET_BUY = "buy";
    private $BASKET_RECHARGE = "recharge";

    public function checkoutAction(Request $request)
    {
        return $this->render('UniversalIDTBundle:Checkout:checkout1.html.twig');

        $granted = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        if($granted)
            $breadcrumbs->addItem("Home", $this->get("router")->generate("user_home"));
        else
            $breadcrumbs->addItem("Home", $this->get("router")->generate("WebPage_main"));
        $breadcrumbs->addItem("Transactions", $this->get("router")->generate("user_orders"));
        $breadcrumbs->addItem("order details");

        if($request->query->has('account'))
        {
            switch($request->query->get('account')) {
                case "login": return $this->redirect($this->generateUrl('fos_user_security_login'));
                case "register": return $this->redirect($this->generateUrl('fos_user_registration_register'));
            }
        }

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

    public function ogoneResultAction(Request $request)
    {
        try {
            $orderDetail = $this->get('client_ogone')->processResult($request->query->all());

        Log::save($orderDetail->getId(),"order_id_after_ogone");

            try {
                if($orderDetail->getPaymentStatus() == PaymentStatusEnumType::STATUS_ACCEPTED)
                {
                    $idt = $this->get('idt');

                    $orderDetail = $idt->processOrder($orderDetail);

                    $result = "";
                    /** @var OrderProduct $orderProduct */
                    foreach($orderDetail->getOrderProducts() as $orderProduct) {
                        $result .=
                            " id: ". $orderProduct->getId()." - ".
                            " name: ". $orderProduct->getProduct()->getName()." - ".
                            " class_id: ". $orderProduct->getProduct()->getClassId()." - ".
                            " denom: ". $orderProduct->getPinDenomination()." - ".
                            " ctrlNumber: ". ($orderProduct->getCtrlNumber()?:"--") ." - ".
                            " pin: ". ($orderProduct->getPin()?:"--") ." - ".
                            " request type: ". $orderProduct->getRequestType()." - ".
                            " request status: ". $orderProduct->getRequestStatus()." - ".
                            "<br>";
                    }

                    return $this->forward("UniversalIDTBundle:Checkout:checkoutResult", array(
                            'result' => "succeed: <br>".$result
                        ));
                }
                else
                {
                    return $this->forward("UniversalIDTBundle:Checkout:checkoutResult", array(
                            'result' => "Payment failed: " . $orderDetail->getPaymentStatus()
                        ));
                }
            } catch (\Exception $e) {
                return $this->forward("UniversalIDTBundle:Checkout:checkoutResult", array(
                        'result' => $orderDetail->getPaymentStatus()
                    ));
            }

        } catch (\Exception $e) {
            return $this->forward("UniversalIDTBundle:Checkout:checkoutResult", array(
                    'result' => 'Error in process result of Ogone: '. $e->getMessage()
                ));
        }
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
    private function checkCookie(&$added_items, $added_items_currency, EntityManager $em)
    {
        if(is_null($added_items) || !is_array($added_items))
            return false;
//            die("is null");

        if(is_null($added_items_currency) || !is_string($added_items_currency) || strlen($added_items_currency) != 3)
            return false;
//            die("currency length");

        foreach($added_items as &$added_item) {
            if(
                !isset($added_item['id']) ||
                !isset($added_item['name']) ||
                !isset($added_item['image']) ||
                !isset($added_item['count']) ||
                !isset($added_item['denomination']) ||
                !in_array($added_item['type'], array($this->BASKET_BUY, $this->BASKET_RECHARGE))
            )
                return false;
//                die("field error");

            switch ($added_item['type']) {
                case $this->BASKET_BUY:
                    /** @var Product $row */
                    $row = $em->getRepository('UniversalIDTBundle:Product')->find($added_item['id']);
                    if(!$row || $row->getName() != $added_item['name'])
                        return false;
//                        die("not exist or error name");

                    $added_item['product'] = $row;

                    if($row->getCurrency() !== $added_items_currency)
                        return false;
//                        die("error currency");

                    if(!in_array($added_item['denomination'], $row->getDenominations()))
                        return false;
//                        die("error denom");
                    break;

                case $this->BASKET_RECHARGE:
                    /** @var OrderProduct $row */
                    $row = $em->getRepository('UniversalIDTBundle:OrderProduct')->find($added_item['id']);
                    if(!$row)
                        return false;
//                        die("not exist2");

                    $added_item['product'] = $row;

                    if($row->getProduct()->getCurrency() !== $added_items_currency)
                        return false;
//                        die("error currency2");

                    if(!in_array($added_item['denomination'], $row->getProduct()->getDenominations()))
                        return false;
//                        die("error denom");
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
            for($i=1; $i<=$row['count']; $i++) {
                $order_product = new OrderProduct();
                $order_product->setOrderDetail($order_detail);
                $order_detail->addOrderProduct($order_product);
                $order_product->setPinDenomination($row['denomination']);
                $order_product->setRequestStatus(RequestStatusEnumType::REGISTERED);
                switch ($row['type']) {
                    case $this->BASKET_BUY:
                        $order_product->setRequestType(RequestTypeEnumType::CREATION);
                        $order_product->setProduct(
                            $em->getRepository('UniversalIDTBundle:Product')->find($row['product'])
                        );
                        break;

                    case $this->BASKET_RECHARGE:
                        $order_product->setRequestType(RequestTypeEnumType::RECHARGE);
                        $old_order_product = $em->getRepository('UniversalIDTBundle:OrderProduct')->find(
                            $row['product']
                        );
                        $order_product->setProduct($old_order_product->getProduct());
                        $order_product->setPin($old_order_product->getPin());
                        $order_product->setCtrlNumber($old_order_product->getCtrlNumber());
                        break;
                }
                $em->persist($order_product);
            }
        }

        $em->flush();

        Log::save($order_detail->getId(),"order_id_before_ogone");

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

    public function checkoutResultAction($result)
    {
        $response = new Response();
//        $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
//        $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

        return $this->render('UniversalIDTBundle:Checkout:result.html.twig', array(
                'result' => $result
            ), $response);
    }
}
