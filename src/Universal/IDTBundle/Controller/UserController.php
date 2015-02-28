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
                $new_email = $user->getEmail();

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->refresh($user);
                $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
                $em->flush();

                $this->sendEmailMessage(
                    $this->render("UniversalIDTBundle:Mails:changing_email.email.html.twig", array(
                            'user' => $user,
                            'confirmationUrl' =>  $this->generateUrl('user_profile_email_confirm', array('token'=> $user->getConfirmationToken()), true)
                        ))->getContent(),
                    $this->container->getParameter('mailer_sender_address'),
                    $new_email
                );

                $this->get('session')->set('new_email', $new_email);
                $this->get('session')->set('new_email_token', $user->getConfirmationToken());

                $this->get('session')->getFlashBag()->add('success', "Email sent.");
            }
        }

        return $this->render("UniversalIDTBundle:Settings:email.html.twig", array(
                'email' => $user->getEmail(),
                'form' => $form->createView()
            ));
    }

    public function emailConfirmedAction($token)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db_token = $user->getConfirmationToken();
        $session_token = $this->get('session')->get('new_email_token');
        $new_email = $this->get('session')->get('new_email');

        if(!$session_token || !$new_email) {
            throw $this->createNotFoundException('No email change request.');
        }

        if(!$db_token || $db_token != $session_token || $db_token != $token) {
            $this->get('session')->remove('new_email_token');
            $this->get('session')->remove('new_email');
            throw $this->createNotFoundException('Email change request expired.');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user->setConfirmationToken(null);
        $user->setEmail($new_email);
        $this->get('fos_user.user_manager')->updateCanonicalFields($user);

        $em->flush();

        $this->get('session')->remove('new_email_token');
        $this->get('session')->remove('new_email');

        $this->get('session')->getFlashBag()->add('success', "Email changed.");

        return $this->redirect($this->generateUrl('user_profile_email'));
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