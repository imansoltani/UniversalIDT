<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Universal\IDTBundle\Form\NotificationType;

class UserController extends Controller
{
    public function notificationAction(Request $request)
    {
        $notifications = array(
            'a' => 'abc',
            'b' => 'def',
            'c' => 'ghi',
            'd' => 'jkl',
        );

        $user = $this->getUser();

        $form = $this->createForm(new NotificationType($notifications), $user);

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $em->flush();
            }
        }

        return $this->render("UniversalIDTBundle::notifications.html.twig", array(
                'form' => $form->createView()
            ));
    }
}