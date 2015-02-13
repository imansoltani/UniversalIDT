<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\Product;

class DefaultController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        for($i=10; $i<=15; $i++)
        {
            $product = new Product();
            $product->setName("aaa".$i);
            $product->setCurrency("CHF");
            $product->setCountryISO("US");
            $product->setDenominations(array(($i.".".$i),($i.".".$i.$i)));
            $product->setClassId((int)($i.$i.$i));
            $product->addRate("US", "", 1.1*$i, 1.1*$i, 1.1*$i);
            $product->addRate("FR", "paris", 2.2*$i, 3.3*$i, 4.4*$i);
            $product->addRate("CH", "shanghai", 3.3*$i, 1.1*$i, 2.2*$i);
//            $product->setFile(new UploadedFile("C:\\xampp\\htdocs\\UniversalIdt\\web\\uploads\\test\\(".$i.").png", "(".$i.").png", 'image/png', 70, null, true));
            $em->persist($product);
        }
        $em->flush();

//        $order = new OrderDetail();
//        $order->setCurrency('USD');
//        $order->setAmount(20);
//        $order->setCharge(0);
//        $order->setDeliveryEmail("mm@ss.com");
//        $order->setPaymentMethod(PaymentMethodEnumType::OGONE);
//        $em->persist($order);
//
//        $order_product1 = new OrderProduct();
//        $order_product1->setOrderDetail($order);
//        $order_product1->setProduct($em->getRepository('UniversalIDTBundle:Product')->find(1));
//        $order_product1->setPinDenomination(10);
//        $order_product1->setRequestType(RequestTypeEnumType::CREATION);
//        $order_product1->setRequestStatus(RequestStatusEnumType::SUCCEED);
//        $order->addOrderProduct($order_product1);
//        $em->persist($order_product1);
//
//        $order_product2 = new OrderProduct();
//        $order_product2->setOrderDetail($order);
//        $order_product2->setProduct($em->getRepository('UniversalIDTBundle:Product')->find(2));
//        $order_product2->setPinDenomination(10);
//        $order_product2->setRequestType(RequestTypeEnumType::CREATION);
//        $order_product2->setRequestStatus(RequestStatusEnumType::SUCCEED);
//        $order->addOrderProduct($order_product2);
//        $em->persist($order_product2);
//        $em->flush();

//        $order = $em->getRepository('UniversalIDTBundle:OrderDetail')->find(4);
//
//        die(var_dump($this->get('IDT')->doRequest($order)));



//        $product = new Product();
//        $product->setName("aaa12345");
//        $product->setCurrency("USD");
//        $product->setCountryISO("US");
//        $product->setDenominations(array("2222"));
//        $product->setClassId(12345);
//        $em->persist($product);

//        $a = array(
//            "a"=>array("aa"=>11, "ab"=>12),
//            "b"=>array("ba"=>21, "bb"=>22),
//        );
//
//        var_dump($a);
//
//        $b = &$a["b"];
//        $b["ba"] = 211;
//
//        var_dump($a);
//        die();


//        $product = $em->getRepository("UniversalIDTBundle:Product")->find(42);
//
//        $product->addRate("IR", "shiraaaaaz",12.02,13.03,14.04);
//        $em->flush();
//
//        var_dump($product->findRates(array("des"=>"isfahan")));
//        var_dump($product->getAllRates());

        die("OK");
    }
}
