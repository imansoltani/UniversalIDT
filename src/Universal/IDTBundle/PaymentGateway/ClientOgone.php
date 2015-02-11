<?php
namespace Universal\IDTBundle\PaymentGateway;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Universal\IDTBundle\Entity\OrderDetail;

class ClientOgone
{
    /** @var EntityManager $em */
    private $em;

    private $pspId;
    private $shaIn;
    private $shaOut;
    private $submitUrl;
    private $resultUrl;
    private $catalogUrl;
    private $homeUrl;
    private $ogoneTemplateUrl;

    public function __construct(Router $router, EntityManager $em, $pspId, $shaIn, $shaOut, $submitUrl)
    {
        $this->em           = $em;
        $this->pspId        = $pspId;
        $this->shaIn        = $shaIn;
        $this->shaOut       = $shaOut;
        $this->submitUrl    = $submitUrl;

        $this->ogoneTemplateUrl   = $router->generate("transactions_ogone_template", [], true);
    }

    private function getSortedParameters(OrderDetail $payment)
    {
        return array(
            'ACCEPTURL'     => $this->resultUrl,
            'AMOUNT'        => $payment->getAmount(),
            'CANCELURL'     => $this->resultUrl,
            'CATALOGURL'    => $this->catalogUrl,
            'CURRENCY'      => $payment->getCurrency(),
            'DECLINEURL'    => $this->resultUrl,
            'EXCEPTIONURL'  => $this->resultUrl,
            'HOMEURL'       => $this->homeUrl,
            'LANGUAGE'      => sprintf('%s_%s',$payment->getUser()->getLanguage(),$payment->getUser()->getLanguage()),
            'ORDERID'       => $payment->getOrderReference(),
            'PSPID'         => $this->pspId,
            'TP'            => $this->ogoneTemplateUrl
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