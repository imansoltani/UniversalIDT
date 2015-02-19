<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\User;

class OrdersController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $orders = $em->getRepository('UniversalIDTBundle:OrderDetail')->findBy(array('user' => $user));

        return $this->render('UniversalIDTBundle:Orders:index.html.twig', array(
                'orders' => $orders
            ));    }

    public function detailsAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var OrderDetail $order */
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

        return $this->render('UniversalIDTBundle:Orders:details.html.twig', array(
                'order' => $order
            ));    }
}