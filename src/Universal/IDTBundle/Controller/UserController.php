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

        return $this->render("UniversalIDTBundle:Settings:notifications.html.twig", array(
                'form' => $form->createView()
            ));
    }

    public function emailAction(Request $request)
    {
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

                $this->sendEmailMessage(
                    $this->render("UniversalIDTBundle:Mails:changing_email.email.html.twig", array(
                            'user' => $user,
                            'confirmationUrl' =>  $this->generateUrl('user_profile_email_confirm', array('token'=> $user->getConfirmationToken()), true)
                        ))->getContent(),
                    $this->container->getParameter('mailer_sender_address'),
                    $user->getNewEmail()
                );

                $this->get('session')->getFlashBag()->add('success', "Email sent.");
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

        $user = $em->getRepository('UniversalIDTBundle:User')->findOneBy(array('confirmationToken'=>$token));

        if(!$user) {
            $this->get('session')->getFlashBag()->add('failed', "Token not found. Email change failed");
        }

        if(new \DateTime() > $user->getNewEmailExpireAt()) {
            $this->get('session')->getFlashBag()->add('failed', "Token expired");
        }
        else {
            $user->setEmail($user->getNewEmail());
            $this->get('fos_user.user_manager')->updateCanonicalFields($user);

            $this->get('session')->getFlashBag()->add('success', "Email changed.");
        }

        $user->setConfirmationToken(null);
        $user->setNewEmail(null);
        $user->setNewEmailExpireAt(null);

        $em->flush();

        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    private function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        $this->get('mailer')->send($message);
    }
}