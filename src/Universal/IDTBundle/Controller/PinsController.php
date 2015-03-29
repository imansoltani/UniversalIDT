<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\User;

class PinsController extends Controller
{
    public function indexAction()
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Home", $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem("My PINs", $this->get("router")->generate("user_pins"));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $pins = $em->createQueryBuilder()
            ->select('order_product', 'orderDetail', 'product')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.requestType = :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->orderBy('order_product.id', 'asc')
            ->groupBy('order_product.pin')
            ->innerJoin('order_product.orderDetail', 'orderDetail')
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('order_product.product', 'product')
            ->getQuery()->getResult();

        return $this->render('UniversalIDTBundle:Pins:index.html.twig', array(
                'pins' => $pins
            ));    }

    public function detailsAction($id)
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Home", $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem("My PINs", $this->get("router")->generate("user_pins"));
        $breadcrumbs->addItem("PIN details");

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var OrderProduct $pin */
        $pin = $em->createQueryBuilder()
            ->select('order_product', 'orderDetail', 'product')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.id = :id')->setParameter('id', $id)
            ->andWhere('order_product.requestType = :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->orderBy('order_product.id', 'asc')
            ->groupBy('order_product.pin')
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
            ->select('order_product','product','orderDetail')
            ->from('UniversalIDTBundle:OrderProduct', 'order_product')
            ->where('order_product.pin = :pin')->setParameter('pin', $pin->getPin())
            ->andWhere('order_product.requestType = :requestType')->setParameter('requestType', RequestTypeEnumType::RECHARGE)
            ->andWhere('order_product.requestStatus = :requestStatus')->setParameter('requestStatus', RequestStatusEnumType::SUCCEED)
            ->orderBy('order_product.id', 'desc')
            ->innerJoin('order_product.orderDetail', 'orderDetail')
            ->andWhere('orderDetail.user = :user')->setParameter('user', $user)
            ->innerJoin('order_product.product', 'product')
            ->getQuery()->getResult();

        return $this->render('UniversalIDTBundle:Pins:details.html.twig', array(
                'pin' => $pin,
                'call_details' => $call_details,
                'calls' => (array) $call_details->calls,
                'recharges' => $recharges
            ));    }
}