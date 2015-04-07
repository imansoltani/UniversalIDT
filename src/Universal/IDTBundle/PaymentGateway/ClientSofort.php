<?php
namespace Universal\IDTBundle\PaymentGateway;


use Doctrine\ORM\EntityManager;
use PouyaSoft_ir\Sofort\elements\SofortTag;
use Sofort\SofortLibHttpSocket;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Universal\IDTBundle\Entity\OrderDetail;

class ClientSofort
{
    /** @var EntityManager $em */
    private $em;

    /** @var  Request $request */
    private $request;

    private $projectId;
    private $submitUrl;
    private $confKey;

    private $resultUrl;

    public function __construct(Request $request, Router $router, EntityManager $em, AuthorizationChecker $ac, array $sofort_parameters)
    {
        $this->em        = $em;
        $this->request   = $request;

        $this->projectId = $sofort_parameters['project_id'];
        $this->submitUrl = $sofort_parameters['submit_url'];
        $this->confKey   = $sofort_parameters['conf_key'];

        $this->resultUrl = $ac->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $router->generate("user_sofort_result", [], true) : $router->generate("WebPage_sofort_result", [], true);
    }

    public function getPaymentUrl(OrderDetail $orderDetail)
    {
        //use generateRequestXml()

        // use guzzle to send

        //return url or error
    }

    private function generateRequestXml()
    {
    }

    public function processResult(Request $request)
    {

    }
}