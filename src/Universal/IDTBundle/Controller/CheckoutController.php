<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\OrderProduct;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Form\CheckoutType;

class CheckoutController extends Controller
{
    public function basketAction()
    {
        return $this->render('UniversalIDTBundle:Checkout:basket.html.twig');
    }

    public function checkoutAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new CheckoutType());

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $basket = json_decode($this->get('session')->get('basket'), true);
                $basket_currency = json_decode($this->get('session')->get('basket_currency'), true);

            }
        }
        else
        {
            $added_items = json_decode(stripcslashes($request->cookies->get("products")), true);
            $added_items_currency = $request->cookies->get("products_currency");

            $valid = $this->checkCookie($added_items, $added_items_currency, $em);

            if(!$valid) {
                $response = new Response("Error in cookies and cleared.");
                $this->get('session')->getFlashBag()->add('error','Error occurred in Cookies.');
                $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));
                $response->headers->setCookie(new Cookie("products_currency", "",0,"/",null,false,false ));

                return $response;
            }

            $this->get('session')->set('basket', stripcslashes($request->cookies->get("products")));
            $this->get('session')->set('basket_currency', stripcslashes($added_items_currency));
        }

        return $this->render('UniversalIDTBundle:Checkout:checkout.html.twig', array(
                'data' => $added_items,
                'form' => $form->createView()
            ));
    }

    public function testAction(Request $request)
    {
        $data= array(
            array('product'=>1, 'count'=>2, 'denomination'=>5),
            array('product'=>2, 'count'=>1, 'denomination'=>4),
            array('product'=>3, 'count'=>1, 'denomination'=>3),
            array('product'=>4, 'count'=>3, 'denomination'=>6),
        );
//        die(var_dump($response->headers->getCookies(ResponseHeaderBag::COOKIES_FLAT)));

//        die(var_dump(json_decode(stripcslashes($request->cookies->get("products")), true)));
        $response = new Response("aaaa");
        $response->headers->setCookie(new Cookie("products", json_encode($data),0,"/",null,false,false ));
        return $response;
    }

    /**
     * @param array $added_items
     * @param EntityManager $em
     * @return bool
     */
    private function checkCookie(array &$added_items, $added_items_currency, EntityManager $em)
    {
        if(is_null($added_items) || is_null($added_items_currency))
            return false;
//        die("is null");

        foreach($added_items as &$added_item) {
            if(
                !isset($added_item['product']) ||
                !isset($added_item['count']) ||
                !isset($added_item['denomination']) ||
                !in_array($added_item['type'], array("buy", "recharge"))
            )
                return false;
//                die("field error");

            if($added_item['type'] == "buy") {
                /** @var Product $row */
                $row = $em->getRepository('UniversalIDTBundle:Product')->find($added_item['product']);
                if(!$row) return false;

                $added_item['product'] = $row;

                if($row->getCurrency() !== $added_items_currency) return false;

                if(!in_array($added_item['denomination'], $row->getDenominations())) return false;
            }
            else {
                /** @var OrderProduct $row */
                $row = $em->getRepository('UniversalIDTBundle:OrderProduct')->find($added_item['product']);
                if(!$row) return false;

                $added_item['product'] = $row;

                if($row->getProduct()->getCurrency() !== $added_items_currency) return false;

                if(!in_array($added_item['denomination'], $row->getProduct()->getDenominations())) return false;
            }
        }

        return true;
    }
}
