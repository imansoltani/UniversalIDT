<?php

namespace Universal\IDTBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Universal\IDTBundle\Entity\User;
use Universal\IDTBundle\Form\ChangeEmailType;
use Universal\IDTBundle\Form\NotificationType;

class UserController extends Controller
{
    public function HomeAction()
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));

        return $this->render('UniversalIDTBundle:Settings:home.html.twig');
    }
    public function notificationAction(Request $request)
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.user_settings.index',[],'application'));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.user_settings.notifications',[],'application'));

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

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('settings.notifications.flash.success',[],'application'));
            }
        }

        return $this->render("UniversalIDTBundle:Settings:notifications.html.twig", array(
                'form' => $form->createView()
            ));
    }

    public function emailAction(Request $request)
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.home_index',[],'application'), $this->get("router")->generate("user_home"));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.user_settings.index',[],'application'));
        $breadcrumbs->addItem($this->get('translator')->trans('menu.breadcrumbs.user_settings.email',[],'application'));

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(new ChangeEmailType(), $user);

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
                $user->setNewEmailExpireAt(new \DateTime("+24 hours"));

                $this->get('EmailService')->sendEmailMessage(
                    $this->render("UniversalIDTBundle:Mails:changing_email.email.html.twig", array(
                            'user' => $user,
                            'confirmationUrl' =>  $this->generateUrl('user_profile_email_confirm', array('token'=> $user->getConfirmationToken()), true)
                        ))->getContent(),
                    $this->container->getParameter('mailer_sender_address'),
                    $user->getNewEmail()
                );

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('settings.email.flash.success',['%email%'=>$user->getNewEmail()],'application'));
                $em->flush();
            }
        }

        return $this->render("UniversalIDTBundle:Settings:email.html.twig", array(
                'email' => $user->getEmail(),
                'form' => $form->createView()
            ));
    }

    public function emailConfirmedAction($token)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $email_canonicalizer = $this->get('fos_user.util.email_canonicalizer');

        $user = $em->getRepository('UniversalIDTBundle:User')->findOneBy(array('confirmationToken'=>$token));

        if(!$user)
        {
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('confirmation_new_email.flash.invalid',[],'application'));

            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        if(new \DateTime() > $user->getNewEmailExpireAt())
        {
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('confirmation_new_email.flash.expired',[],'application'));
        }
        elseif($findUserEmail = $em->getRepository('UniversalIDTBundle:User')->findOneBy(array('emailCanonical' => $email_canonicalizer->canonicalize($user->getNewEmail()))))
        {
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('confirmation_new_email.flash.taken',[],'application'));
        }
        else
        {
            $user->setEmail($user->getNewEmail());

            $this->get('fos_user.user_manager')->updateCanonicalFields($user);

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('confirmation_new_email.flash.success',[],'application'));
        }

        $user->setConfirmationToken(null);
        $user->setNewEmail(null);
        $user->setNewEmailExpireAt(null);

        $em->flush();

        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }
}