<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $added_items = json_decode(stripcslashes($request->cookies->get("products")), true);

        $valid = $this->checkCookie($added_items, $em);

        if(!$valid) {
            $response = new Response("Error in cookies and cleared.");
            $this->get('session')->getFlashBag()->add('error','Error occurred in Cookies.');
            $response->headers->setCookie(new Cookie("products", "[]",0,"/",null,false,false ));

            return $response;
        }

        $this->get('session')->set('basket', json_decode(stripcslashes($request->cookies->get("products")), true));

        $form = $this->createForm(new CheckoutType());

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
    private function checkCookie(array &$added_items, EntityManager $em)
    {
        if(is_null($added_items))
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

            $added_item['product'] = $added_item['type'] == "buy"
                ? $em->getRepository('UniversalIDTBundle:Product')->find($added_item['product'])
                : $em->getRepository('UniversalIDTBundle:OrderProduct')->find($added_item['product']);

            if(is_null($added_item['product']))
                return false;
//                die("not found id");
        }

        return true;
    }
}
