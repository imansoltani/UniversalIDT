<?php
namespace Universal\IDTBundle\PaymentGateway;


use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType;
use Universal\IDTBundle\Entity\OrderDetail;

class ClientOgone
{
    /** @var EntityManager $em */
    private $em;

    /** @var  Request $request */
    private $request;

    private $pspId;
    private $shaIn;
    private $shaOut;
    private $submitUrl;
    private $resultUrl;
    private $catalogUrl;
    private $homeUrl;
    private $templateUrl;

    public function __construct(Request $request, Router $router, EntityManager $em, AuthorizationChecker $ac, array $ogone_parameters)
    {
        $this->em           = $em;
        $this->request      = $request;

        $this->pspId        = $ogone_parameters['pspid'];
        $this->shaIn        = $ogone_parameters['sha_in'];
        $this->shaOut       = $ogone_parameters['sha_out'];
        $this->submitUrl    = $ogone_parameters['submit_url'];

        $this->resultUrl    = $ac->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $router->generate("user_ogone_result", [], true) : $router->generate("WebPage_ogone_result", [], true);
        $this->catalogUrl   = $router->generate("WebPage_main", [], true)."#basket";
        $this->homeUrl      = $router->generate("WebPage_main", [], true);

        $this->templateUrl = $router->generate("ogone_template", [], true);
    }

    private function getSortedParameters(OrderDetail $payment)
    {
        return array(
            'ACCEPTURL'     => $this->resultUrl,
            'AMOUNT'        => $payment->getOgoneAmount(),
            'CANCELURL'     => $this->resultUrl,
            'CATALOGURL'    => $this->catalogUrl,
            'CURRENCY'      => $payment->getCurrency(),
            'DECLINEURL'    => $this->resultUrl,
            'EXCEPTIONURL'  => $this->resultUrl,
            'HOMEURL'       => $this->homeUrl,
            'LANGUAGE'      => $this->request->getLocale(),
            'ORDERID'       => $payment->getOrderReference(),
            'PSPID'         => $this->pspId,
            'TP'            => $this->templateUrl
        );
    }

    private function generateHashIn(OrderDetail $payment)
    {
        $fields = [];
        foreach ($this->getSortedParameters($payment) as $fieldName => $fieldValue)
        {
            $fields[] = sprintf('%s=%s', $fieldName, $fieldValue);
        }
        $fields[] = '';
        return sha1(implode($this->shaIn, $fields));
    }

    public function processResult($fields)
    {
        if (false === $this->checkHashOut($fields)) {
            throw new \Exception("Invalid Ogone data's.");
        }

        $orderDetail = $this->em->getRepository('UniversalIDTBundle:OrderDetail')->findOneByOrderReference(
            $fields['orderID']
        );

        if (null === $orderDetail) {
            throw new \Exception('Invalid payment');
        }

        if ($orderDetail->isProcessed())
        {
            return $orderDetail;
        }

        $orderDetail->setPaymentId($fields['PAYID']);

        switch ($fields['STATUS'])
        {
            case OrderDetail::OGONE_RESULT_ACCEPTED:
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_ACCEPTED);
                break;

            case OrderDetail::OGONE_RESULT_DECLINED:
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_DECLINED);
                break;

            case OrderDetail::OGONE_RESULT_CANCELED:
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_CANCELED);
                break;

            case OrderDetail::OGONE_RESULT_EXCEPTION:
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_UNCERTAIN);
                break;

            default:
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_UNKNOWN);

        }

        $this->em->flush();

        return $orderDetail;

    }

    private function checkHashOut(array $fields)
    {
        $datas = array_change_key_case($fields, CASE_UPPER);

        foreach (['ORDERID', 'PAYID', 'SHASIGN', 'STATUS'] as $fieldName)
        {
            if (!isset($datas[$fieldName])){return false;}
        }

        $ogoneDigestSign = $datas['SHASIGN'];
        unset($datas['SHASIGN']);
        ksort($datas);

        $hashedString = '';

        foreach ($datas as $fieldName => $fieldValue)
        {
            if($fieldValue !== '')
            {
                $hashedString .= sprintf('%s=%s%s', $fieldName, $fieldValue, $this->shaOut);
            }
        }

        return $ogoneDigestSign === strtoupper(sha1($hashedString));
    }

    public function generateForm(OrderDetail $payment)
    {
        $fields[] = sprintf('<form id="ogone_form" name="ogone_form" method="post" action="%s">', $this->submitUrl);

        foreach ($this->getSortedParameters($payment) as $name => $value)
        {
            $fields[] = sprintf('<input  type="hidden" name="%s" value="%s" />', $name, $value);
        }

        $fields[] = sprintf('<input  type="hidden" name="SHASIGN" value="%s" />', $this->generateHashIn($payment));
//        $fields[] ='<input type="submit" value="Accept" />';
        $fields[] = '</form>';

        return implode('', $fields);
    }
}