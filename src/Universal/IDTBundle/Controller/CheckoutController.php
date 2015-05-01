<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType;
use Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestsStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Form\CheckoutType;
use Universal\IDTBundle\Idt\Log;

class CheckoutController extends Controller
{
    const LAST_ORDER_COUNT_SHOWN = 3;
    const LAST_ORDER_TIME_LENGTH = 20;

    private $BASKET_BUY = "buy";
    private $BASKET_RECHARGE = "recharge";

    public function checkoutAction(Request $request)
    {
        $granted = $this->isGranted('IS_AUTHENTICATED_REMEMBERED');

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        if($granted)
            $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.checkout',[],'application'));


        if($request->query->has('account') && !$granted) {
            switch($request->query->get('account')) {
                case "login": return $this->redirect($this->generateUrl('fos_user_security_login'));
                case "register": return $this->redirect($this->generateUrl('fos_user_registration_register'));
            }
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new CheckoutType(), array('email' => $granted ? $this->getUser()->getEmail() : ""));

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                $formData = $form->getData();
                $formData['sms'] = "";
                return $this->forward("UniversalIDTBundle:Checkout:placeOrder", array('formData' => $formData, 'language' => $request->getLocale()));
            }
        }

        //read from cookie
        $added_items = json_decode(stripcslashes($request->cookies->get("products")), true);
        $added_items_currency = $request->cookies->get("products_currency");

        /** @var float $sum_total */
        $sum_total = 0.0;

        /** @var float $sum_vat */
        $sum_vat = 0.0;

        //check cookie validation
        if(is_null($added_items) || !is_array($added_items) || count($added_items) == 0) {
            $this->get('session')->getFlashBag()->add('warning','Basket is Empty.');
            $valid = false;
        }
        elseif (!$valid = $this->checkCookie($added_items, $added_items_currency, $em, $sum_total, $sum_vat)) {
            $this->get('session')->getFlashBag()->add('error','Error occurred in Cookies and Basket cleared.');
        }

        //unset cookie if not valid
        if(!$valid) {
            $response = new RedirectResponse($request->headers->get('referer')?:($this->generateUrl('WebPage_main')."#basket"));

            $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
            $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

            return $response;
        }

        //write in session
        $this->get('session')->set('basket', stripcslashes($request->cookies->get("products")));
        $this->get('session')->set('basket_currency', $added_items_currency);

        return $this->render('UniversalIDTBundle:Checkout:checkout.html.twig', array(
                'data' => $added_items,
                'form' => $form->createView(),
                'sum_total' => $sum_total,
                'sum_vat' => $sum_vat,
                'currency' => $added_items_currency
            ));
    }

    public function ogoneResultAction(Request $request)
    {
        try {
            $orderDetail = $this->get('client_ogone')->processResult($request->query->all());

            Log::save($orderDetail->getId(),"order_id_after_ogone");

        } catch (\Exception $e) {
            Log::save($e->getMessage(),"error_in_ogone");
            throw new \Exception('Error in process result of Ogone: '. $e->getMessage());
        }

        try {
            if($orderDetail->getPaymentStatus() == PaymentStatusEnumType::STATUS_ACCEPTED && $orderDetail->getRequestsStatus() == null) {
                $orderDetail = $this->get('idt')->processOrder($orderDetail);
            }
        } catch (\Exception $e) {
            Log::save($e->getMessage(), "error_in_idt");
        }


//        /** @var EntityManager $em */
//        $em = $this->getDoctrine()->getManager();
//        $orderDetail = $em->getRepository('UniversalIDTBundle:OrderDetail')->find(5);

        $response = new Response();
//        $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
//        $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

        if(!$orderDetail->getDelivered()) {
            if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $this->get('session')->set('last_order_id', $orderDetail->getId());
                $this->get('session')->set('last_order_count_shown', 0);
                $this->get('session')->set('last_order_start_time', new \DateTime());
            }
            if($orderDetail->getDeliveryEmail()) {
                $this->get('EmailService')->sendEmailMessage(
                    $this->render("UniversalIDTBundle:Mails:checkout.email.html.twig", array(
                            'order' =>  $orderDetail
                        ))->getContent(),
                    $this->container->getParameter('mailer_sender_address'),
                    $orderDetail->getDeliveryEmail()
                );
                $this->get('session')->getFlashBag()->add('success', "Email sent.");
            }

            $orderDetail->setDelivered(true);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->render('UniversalIDTBundle:Checkout:result.html.twig', array(
                'orderDetail' => $orderDetail,
                'maxCountShow' => CheckoutController::LAST_ORDER_COUNT_SHOWN,
                'maxMinutesShow' => CheckoutController::LAST_ORDER_TIME_LENGTH
            ), $response);
    }

    public function sofortResultAction(Request $request, $status)
    {
        try {
            $orderDetail = $this->get('client_sofort')->processResult($status, $request->query->get('trans'));

            Log::save($orderDetail->getId(),"order_id_after_sofort");

        } catch (\Exception $e) {
            Log::save($e->getMessage(),"error_in_Sofort");
            throw new \Exception('Error in process result of Sofort: '. $e->getMessage());
        }

        try {
            if($orderDetail->getPaymentStatus() == PaymentStatusEnumType::STATUS_ACCEPTED && $orderDetail->getRequestsStatus() == null) {
                $orderDetail = $this->get('idt')->processOrder($orderDetail);
            }
        } catch (\Exception $e) {
            Log::save($e->getMessage(), "error_in_idt");
        }


//        /** @var EntityManager $em */
//        $em = $this->getDoctrine()->getManager();
//        $orderDetail = $em->getRepository('UniversalIDTBundle:OrderDetail')->find(5);

        $response = new Response();
//        $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
//        $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

        if(!$orderDetail->getDelivered()) {
            if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $this->get('session')->set('last_order_id', $orderDetail->getId());
                $this->get('session')->set('last_order_count_shown', 0);
                $this->get('session')->set('last_order_start_time', new \DateTime());
            }
            if($orderDetail->getDeliveryEmail()) {
                $this->get('EmailService')->sendEmailMessage(
                    $this->render("UniversalIDTBundle:Mails:checkout.email.html.twig", array(
                            'order' =>  $orderDetail
                        ))->getContent(),
                    $this->container->getParameter('mailer_sender_address'),
                    $orderDetail->getDeliveryEmail()
                );
                $this->get('session')->getFlashBag()->add('success', "Email sent.");
            }

            $orderDetail->setDelivered(true);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->render('UniversalIDTBundle:Checkout:result.html.twig', array(
                'orderDetail' => $orderDetail,
                'maxCountShow' => CheckoutController::LAST_ORDER_COUNT_SHOWN,
                'maxMinutesShow' => CheckoutController::LAST_ORDER_TIME_LENGTH
            ), $response);
    }

    /**
     * @param array $added_items
     * @param string $added_items_currency
     * @param EntityManager $em
     * @param float $sum_total
     * @param float $sum_vat
     * @return bool
     */
    private function checkCookie(&$added_items, $added_items_currency, EntityManager $em, &$sum_total, &$sum_vat)
    {
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
                !isset($added_item['base']) ||
                !isset($added_item['free_amount']) ||
                !isset($added_item['vat']) ||
                !in_array($added_item['type'], array($this->BASKET_BUY, $this->BASKET_RECHARGE))
            )
                return false;
//                die("field error");

            if(!is_numeric($added_item['count']) )
                return false;

            switch ($added_item['type']) {
                case $this->BASKET_BUY:
                    /** @var Product $row */
                    $row = $em->getRepository('UniversalIDTBundle:Product')->find($added_item['id']);
                    if(!$row
                        || $row->getName() != $added_item['name']
                        || !in_array($added_item['denomination'], $row->getDenominations())
                        || $row->getDenominations()[0] != $added_item['base']
                        || $row->getFreeAmountDenomination1() != $added_item['free_amount']
                        || $this->get('OrderServices')->getVat($row->getCountryISO()) != $added_item['vat']
                        || $row->getCurrency() !== $added_items_currency
                    )
                        return false;
//                        die("not exist or error name");

                    $added_item['product'] = $row;
                    break;

                case $this->BASKET_RECHARGE:
                    /** @var OrderProduct $row */
                    $row = $em->getRepository('UniversalIDTBundle:OrderProduct')->find($added_item['id']);
                    if(!$row
                        || $row->getProduct()->getName() != $added_item['name']
                        || !in_array($added_item['denomination'], $row->getProduct()->getDenominations())
                        || $row->getProduct()->getDenominations()[0] != $added_item['base']
                        || $row->getProduct()->getFreeAmountDenomination1() != $added_item['free_amount']
                        || $this->get('OrderServices')->getVat($row->getProduct()->getCountryISO()) != $added_item['vat']
                        || $row->getProduct()->getCurrency() !== $added_items_currency
                    )
                        return false;
//                        die("not exist2 or error");

                    $added_item['product'] = $row->getProduct();
                    break;
            }

            $sum_vat += $added_item['count'] * $this->get('OrderServices')->calcVat($added_item['product'], $added_item['denomination']);

            $added_item['discount'] = $this->get('OrderServices')->calcDiscount($added_item['product'], $added_item['denomination']);
            $added_item['row_total'] =  $added_item['count'] * ($added_item['denomination'] - $added_item['discount']);
            $sum_total += $added_item['row_total'];
        }

        return true;
    }

    public function placeOrderAction(array $formData, $language)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //read for session
        $basket = json_decode($this->get('session')->get('basket'), true);
        $basket_currency = $this->get('session')->get('basket_currency');

        //calc sum amount
        $amount = 0;

        //create order_detail
        $order_detail = new OrderDetail();
        $order_detail->setCurrency($basket_currency);
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
                $order_product->setVat($row['vat']);
                $order_product->setRequestStatus(RequestStatusEnumType::REGISTERED);
                switch ($row['type']) {
                    case $this->BASKET_BUY:
                        $order_product->setRequestType(RequestTypeEnumType::CREATION);
                        $order_product->setProduct(
                            $em->getRepository('UniversalIDTBundle:Product')->find($row['id'])
                        );
                        break;

                    case $this->BASKET_RECHARGE:
                        $order_product->setRequestType(RequestTypeEnumType::RECHARGE);
                        $old_order_product = $em->getRepository('UniversalIDTBundle:OrderProduct')->find(
                            $row['id']
                        );
                        $order_product->setProduct($old_order_product->getProduct());
                        $order_product->setPin($old_order_product->getPin());
                        $order_product->setCtrlNumber($old_order_product->getCtrlNumber());
                        break;
                }
                $em->persist($order_product);

                $amount += $row['denomination'] - $this->get('OrderServices')->calcDiscount($order_product->getProduct(), $row['denomination']);
            }

        }

        $order_detail->setAmount($amount);

        $em->flush();

        Log::save($order_detail->getId(),"order_id_before_order");

        //go to redirect page for methods
        switch($order_detail->getPaymentMethod()) {
            case PaymentMethodEnumType::OGONE:
                $ogone = $this->get('client_ogone');
                return $this->render("UniversalIDTBundle:Checkout:redirectToOgone.html.twig", array(
                        'form' => $ogone->generateForm($order_detail)
                    ));
            case PaymentMethodEnumType::SOFORT:
                $sofort = $this->get('client_sofort');
                return $this->redirect($sofort->getPaymentUrl($order_detail,substr($language,0,2)));
            default:
                break;
        }

        return new Response("done");
    }

    public function GuestResultPrintAction()
    {
        $last_order_id = $this->get('session')->get('last_order_id');

        if($last_order_id == null) {
            $this->get('session')->remove('last_order_id');
            $this->get('session')->remove('last_order_count_show');

            throw $this->createNotFoundException('Last Order Not found.');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $orderDetail */
        $orderDetail = $em->createQueryBuilder()
            ->select('orderDetail')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $last_order_id)
            ->andWhere('orderDetail.user is null')
            ->getQuery()->getOneOrNullResult();

        if(!$orderDetail)
            throw $this->createNotFoundException('Order not found.');

        return $this->render("UniversalIDTBundle:Checkout:resultPrint.html.twig", array(
                'orderDetail' => $orderDetail
            ));
    }

    public function UserResultPrintAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $orderDetail */
        $orderDetail = $em->createQueryBuilder()
            ->select('orderDetail')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $id)
            ->andWhere('orderDetail.user = :user')->setParameter('user', $this->getUser())
            ->getQuery()->getOneOrNullResult();

        if(!$orderDetail)
            throw $this->createNotFoundException('Order not found.');

        return $this->render("UniversalIDTBundle:Checkout:resultPrint.html.twig", array(
                'orderDetail' => $orderDetail
            ));
    }

    public function orderDetailsAction()
    {
        $last_order_id = $this->get('session')->get('last_order_id');
        $last_order_count_shown = $this->get('session')->get('last_order_count_shown');
        $last_order_start_time = $this->get('session')->get('last_order_start_time');

        if($last_order_id == null
            || $last_order_count_shown >= CheckoutController::LAST_ORDER_COUNT_SHOWN
            || date_diff($last_order_start_time, new \DateTime())->i > CheckoutController::LAST_ORDER_TIME_LENGTH
        ) {
            $this->get('session')->remove('last_order_id');
            $this->get('session')->remove('last_order_count_show');

            throw $this->createNotFoundException('Last Order Not found.');
        }

        $this->get('session')->set('last_order_count_shown', ++$last_order_count_shown);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $order */
        $order = $em->createQueryBuilder()
            ->select('orderDetail', 'order_products', 'product')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $last_order_id)
            ->andWhere('orderDetail.user is null')
            ->innerJoin('orderDetail.orderProducts', 'order_products')
            ->innerJoin('order_products.product', 'product')
            ->getQuery()->getOneOrNullResult();

        if(!$order)
            throw $this->createNotFoundException('Order not found.');

        if($order->getRequestsStatus() != RequestsStatusEnumType::DONE)
            throw new \Exception('Error in IDT');

        return $this->render('UniversalIDTBundle:Orders:details.html.twig', array(
                'order' => $order
            ));
    }

    public function orderDetailsPrintAction()
    {
        $last_order_id = $this->get('session')->get('last_order_id');

        if($last_order_id == null) {
            $this->get('session')->remove('last_order_id');
            $this->get('session')->remove('last_order_count_show');

            throw $this->createNotFoundException('Last Order Not found.');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $order */
        $order = $em->createQueryBuilder()
            ->select('orderDetail', 'order_products', 'product')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $last_order_id)
            ->andWhere('orderDetail.user is null')
            ->innerJoin('orderDetail.orderProducts', 'order_products')
            ->innerJoin('order_products.product', 'product')
            ->getQuery()->getOneOrNullResult();

        if(!$order)
            throw $this->createNotFoundException('Order not found.');

        if($order->getRequestsStatus() != RequestsStatusEnumType::DONE)
            throw new \Exception('Error in IDT');

        return $this->render('UniversalIDTBundle:Checkout:detailsPrint.html.twig', array(
                'order' => $order
            ));    }
}
