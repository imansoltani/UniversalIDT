<?php

namespace Universal\IDTBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('UniversalIDTBundle:Default:index.html.twig', array('name' => $name));
    }
}
