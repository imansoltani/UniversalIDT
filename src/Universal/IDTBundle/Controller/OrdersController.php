<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Universal\IDTBundle\DBAL\Types\RequestsStatusEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\User;

class OrdersController extends Controller
{
    public function indexAction()
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.transactions.index',[],'application'), $this->get("router")->generate("user_orders"));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var OrderDetail[] $orders */
        $orders = $em->getRepository('UniversalIDTBundle:OrderDetail')->findBy(array('user' => $user));

        return $this->render('UniversalIDTBundle:Orders:index.html.twig', array(
                'orders' => $orders
            ));    }

    public function detailsAction($id)
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.transactions.index',[],'application'), $this->get("router")->generate("user_orders"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.transactions.details',['%id%'=> $id],'application'));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $order */
        $order = $em->createQueryBuilder()
            ->select('orderDetail', 'order_products', 'product')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $id)
            ->andWhere('orderDetail.user = :user')->setParameter('user', $this->getUser())
            ->innerJoin('orderDetail.orderProducts', 'order_products')
            ->innerJoin('order_products.product', 'product')
            ->getQuery()->getOneOrNullResult();

        if(!$order)
            throw $this->createNotFoundException('Order not found.');

        if($order->getRequestsStatus() != RequestsStatusEnumType::DONE)
            throw new \Exception('Error in IDT');

        return $this->render('UniversalIDTBundle:Orders:details.html.twig', array(
                'order' => $order
            ));    }

    public function detailsPrintAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OrderDetail $order */
        $order = $em->createQueryBuilder()
            ->select('orderDetail', 'order_products', 'product')
            ->from('UniversalIDTBundle:OrderDetail', 'orderDetail')
            ->where('orderDetail.id = :id')->setParameter('id', $id)
            ->andWhere('orderDetail.user = :user')->setParameter('user', $this->getUser())
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