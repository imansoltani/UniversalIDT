<?php
namespace Universal\IDTBundle\PaymentGateway;

use Doctrine\ORM\EntityManager;
use Guzzle\Service\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Idt\Log;

class ClientSofort
{
    /** @var EntityManager $em */
    private $em;

    /** @var  Request $request */
    private $request;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    private $projectId;
    private $submitUrl;
    private $confKey;
    private $successUrl;
    private $abortUrl;
    private $timeoutUrl;
    private $notifyUrl;

    public function __construct(Request $request, Router $router, EntityManager $em, AuthorizationChecker $ac, array $sofort_parameters, ClientInterface $guzzle)
    {
        $this->em        = $em;
        $this->request   = $request;
        $this->guzzle    = $guzzle;

        $this->projectId = $sofort_parameters['project_id'];
        $this->submitUrl = $sofort_parameters['submit_url'];
        $this->confKey   = $sofort_parameters['conf_key'];

        $this->successUrl = ($ac->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ? $router->generate("user_sofort_result", ['status'=>'success'], true)
            : $router->generate("WebPage_sofort_result", ['status'=>'success'], true))
            . "?trans=-TRANSACTION-";

        $this->abortUrl = ($ac->isGranted('IS_AUTHENTICATED_REMEMBERED')
                ? $router->generate("user_sofort_result", ['status'=>'abort'], true)
                : $router->generate("WebPage_sofort_result", ['status'=>'abort'], true))
            . "?trans=-TRANSACTION-";

        $this->timeoutUrl = ($ac->isGranted('IS_AUTHENTICATED_REMEMBERED')
                ? $router->generate("user_sofort_result", ['status'=>'timeout'], true)
                : $router->generate("WebPage_sofort_result", ['status'=>'timeout'], true))
            . "?trans=-TRANSACTION-";

        $this->notifyUrl = ($ac->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ? $router->generate("user_sofort_notification", [], true)
            : $router->generate("WebPage_sofort_notification", [], true))
            . "?trans=-TRANSACTION-";
    }

    public function getPaymentUrl(OrderDetail $orderDetail, $language)
    {
        $request = $this->generateRequestXml(
            $orderDetail->getAmount(),
            $orderDetail->getCurrency(),
            $language,
            $orderDetail->getId().'-'.$orderDetail->getDate()->getTimestamp()
        );

        Log::save(print_r($request, true), "sofort_request");

        try {
            $response = $this->guzzle->post($this->submitUrl, array(
                    'Authorization' => 'Basic '.base64_encode($this->confKey),
                    'Content-Type' => 'application/xml; charset=UTF-8',
                    'Accept' => 'application/xml; charset=UTF-8'
                ), $request)->send();

            Log::save($response->getBody(true), "sofort_response");

            $result = simplexml_load_string($response->getBody(true));

            if ($result->getName() == "errors") {
                $errors = "";

                foreach ($result->error as $error) {
                    $errors .= $error->code . " : " . $error->message . " (" . $error->field . ")\n";
                }

                throw new \Exception($errors, 1234);
            }

            $orderDetail->setPaymentId($result->transaction);

            $this->em->flush();

            return (string) $result->payment_url;
        }
        catch (\Exception $e) {
            throw new \Exception("Something wrong happened with Sofort server.\n".$e->getMessage());
        }
    }

    private function generateRequestXml($amount, $currencyCode, $language, $reason = "payment")
    {
        return
            '<?xml version="1.0" encoding="UTF-8" ?>
            <multipay>
                <project_id>'.$this->projectId.'</project_id>
                <amount>'.$amount.'</amount>
                <reasons>
                    <reason>'.$reason.'</reason>
                </reasons>
                <currency_code>'.$currencyCode.'</currency_code>
                <language_code>'.$language.'</language_code>
                <success_url>'.$this->successUrl.'</success_url>
                <success_link_redirect>1</success_link_redirect>
                <abort_url>'.$this->abortUrl.'</abort_url>
                <timeout_url>'.$this->timeoutUrl.'</timeout_url>
                <notification_urls>
                    <notification_url>'.$this->notifyUrl.'</notification_url>
                </notification_urls>
                <su />
            </multipay>';

    }

    public function processResult($status, $transaction)
    {
        if(strlen($transaction) != 27)
            throw new \Exception('Invalid Transaction Number -1');

        $orderDetail = $this->em->getRepository('UniversalIDTBundle:OrderDetail')->findOneBy(array('paymentId'=>$transaction));

        if(!$orderDetail)
            throw new \Exception('Invalid Transaction Number -2');

        switch ($status) {
            case 'timeout':
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_DECLINED);
                break;
            case 'abort':
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_CANCELED);
                break;
            case 'success':
                $orderDetail->setPaymentStatus(PaymentStatusEnumType::STATUS_ACCEPTED);
                break;
            default:
                throw new \Exception('Invalid Status.');
        }

        $this->em->flush();

        return $orderDetail;
    }

    public function notification($data)
    {
//        Log::save($data, "sofort_notification_request_before");

        try {
            $result = simplexml_load_string($data);

            if ($result->getName() == "errors") {
                $errors = "";

                foreach ($result->error as $error) {
                    $errors .= $error->code . " : " . $error->message . " (" . $error->field . ")\n";
                }

                throw new \Exception($errors, 1234);
            }

            $transaction = (string) $result->transaction;
        }
        catch (\Exception $e) {
            throw new \Exception("Something wrong happened with Sofort server.\n".$e->getMessage());
        }

        $orderDetail = $this->em->getRepository('UniversalIDTBundle:OrderDetail')->findOneBy(array('paymentId'=>$transaction));

        if(!$orderDetail)
            throw new \Exception('Transaction Not found.');

        $requestXML =
            '<?xml version="1.0" encoding="UTF-8" ?>
            <transaction_request version="2">
                <transaction>'.$transaction.'</transaction>
            </transaction_request>';

        Log::save(print_r($requestXML, true), "sofort_notify_notification_request");

        $response = $this->guzzle->post($this->submitUrl, array(
                'Authorization' => 'Basic '.base64_encode($this->confKey),
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Accept' => 'application/xml; charset=UTF-8'
            ), $requestXML)->send();

        Log::save($response->getBody(true), "sofort_notify_notification_response");

        return;
    }
}