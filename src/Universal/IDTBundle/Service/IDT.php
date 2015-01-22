<?php
namespace Universal\IDTBundle\Service;

use Doctrine\ORM\EntityManager;
use Guzzle\Service\ClientInterface;
use JMS\Serializer\SerializerInterface;
use Universal\IDTBundle\DBAL\Types\OrderStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\Ordering;
use Universal\IDTBundle\Entity\OrderProduct;

/**
 * Class IDT
 * @package Universal\IDTBundle\Service
 */
class IDT
{
    /**
     * @var SerializerInterface
     */
    private $idt_parameters;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var EntityManager
     */
    private $em;

    private $debitRequests = "";
    private $countDebitRequests = 0;

    /**
     * @param array $idt_parameters
     * @param ClientInterface $guzzle
     * @param EntityManager $em
     */
    public function __construct(array $idt_parameters, ClientInterface $guzzle, EntityManager $em)
    {
        $this->idt_parameters = $idt_parameters;
        $this->guzzle = $guzzle;
        $this->em = $em;
    }

    public function doRequest(Ordering $order)
    {
        $this->debitRequests = "";
        $this->countDebitRequests = 0;
        $order->setStatus(OrderStatusEnumType::PENDING);
        $this->em->flush();

        /** @var OrderProduct $orderProduct */
        foreach($order->getOrderProducts() as $orderProduct) {
            switch ($orderProduct->getRequestType()) {
                case RequestTypeEnumType::CREATION: $this->accountCreationRequest($orderProduct); break;
                case RequestTypeEnumType::ACTIVATION: $this->cardActivationRequest($orderProduct); break;
                case RequestTypeEnumType::RECHARGE: $this->rechargeAccountRequest($orderProduct); break;
                case RequestTypeEnumType::DEACTIVATION: $this->cardDeactivationRequest($orderProduct); break;
            }
        }

        $result = $this->send();


    }

    private function accountCreationRequest(OrderProduct $orderProduct)
    {
        $class_id = 1234;

        $this->debitRequests .=
            '<DebitRequest id="'.$this->countDebitRequests++.'" type="creation">
                <CustomerInformation><classid>'.$class_id.'</classid></CustomerInformation>
            </DebitRequest>';
    }

    private function cardActivationRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->countDebitRequests++.'" type="activation">
                <account>'.$orderProduct->getCtrlNumber().'</account>
            </DebitRequest>';

        $this->rechargeAccountRequest($orderProduct);
    }

    private function rechargeAccountRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->countDebitRequests++.'" type="recharge">
                <CustomerInformation>
                    <account>'.$orderProduct->getCtrlNumber().'</account>
                </CustomerInformation>
                <CreditCard>
                    <amount>'.$orderProduct->getOrdering()->getAmount().'</amount>
                </CreditCard>
            </DebitRequest>';
    }

    private function cardDeactivationRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->countDebitRequests++.'" type="deactivation">
                <account>'.$orderProduct->getCtrlNumber().'</account>
            </DebitRequest>';
    }

    /**
     * @param bool $reportSuccesses
     * @return string
     * @throws \Exception
     */
    private function send($reportSuccesses = true)
    {
        $request =
            '<?xml version="1.0"?>
            <IDTDebitInterface>
            <UserInfo>
            <username>'.$this->idt_parameters['username'].'</username>
            <password>'.$this->idt_parameters['password'].'</password>
            </UserInfo>
            <DebitRequests ReportSuccesses="'.($reportSuccesses?"true":"false").'">'.$this->debitRequests.'</DebitRequests>
            </IDTDebitInterface>';

        $response = $this->guzzle->post($this->idt_parameters['api_location'], null, $request)->send();

        $result = simplexml_load_string($response->getBody());

        if(isset($result->DebitError))
            throw new \Exception($result->DebitError->errordescription, -99);

        return $result->DebitResponses;
    }
}