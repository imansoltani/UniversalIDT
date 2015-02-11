<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\User;

class UserController extends Controller
{
    public function billingHistoryAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $orders = $em->getRepository('UniversalIDTBundle:OrderDetail')->findBy(array('user' => $user));

        return $this->render('UniversalIDTBundle:User:billingHistory.html.twig', array(
                'orders' => $orders
            ));    }

    public function orderDetailsAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $order = $em->createQueryBuilder()
            ->select('orderDetail')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $id)
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('orderDetail.orderProducts', 'order_products')
            ->innerJoin('order_products.product', 'product')
            ->getQuery()->getOneOrNullResult();

        if(!$order)
            throw $this->createNotFoundException('Order not found.');

        return $this->render('UniversalIDTBundle:User:orderDetails.html.twig', array(
                'order' => $order
            ));    }

    public function myPinsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $pins = $em->createQueryBuilder()
            ->select('order_product', 'orderDetail', 'product')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.requestType != :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->innerJoin('order_product.orderDetail', 'orderDetail')
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('order_product.product', 'product')
            ->getQuery()->getResult();

        return $this->render('UniversalIDTBundle:User:myPins.html.twig', array(
                'pins' => $pins
            ));    }

    public function pinDetailsAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var OrderProduct $pin */
        $pin = $em->createQueryBuilder()
            ->select('order_product', 'orderDetail', 'product')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.id = :id')->setParameter('id', $id)
            ->andWhere('order_product.requestType != :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->innerJoin('order_product.orderDetail', 'orderDetail')
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('order_product.product', 'product')
            ->getQuery()->getOneOrNullResult();

        if(!$pin)
            throw $this->createNotFoundException('Pin not found.');

        if(!$pin->getPin())
            throw $this->createNotFoundException('Pin is empty.');

        $call_details = $this->get('IDT')->getCallDetails($pin);

        /** @var OrderProduct[] $recharges */
        $recharges = $em->createQueryBuilder()
            ->select('order_product')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.pin = :pin')->set('pin', $pin->getPin())
            ->andWhere('order_product.requestType = :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->innerJoin('order_product.orderDetail', 'orderDetail')
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('order_product.product', 'product')
            ->getQuery()->getResult();

        return $this->render('UniversalIDTBundle:User:pinDetails.html.twig', array(
                'pin' => $pin,
                'call_details' => $call_details,
                'recharges' => $recharges
            ));    }

}
