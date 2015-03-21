<?php
namespace Universal\IDTBundle\Idt;

use Doctrine\ORM\EntityManager;
use Guzzle\Service\ClientInterface;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;
use Universal\IDTBundle\DBAL\Types\RequestTypeEnumType;
use Universal\IDTBundle\Entity\OrderDetail;
use Universal\IDTBundle\Entity\OrderProduct;

/**
 * Class IDT
 * @package Universal\IDTBundle\Service
 */
class ClientIdt
{
    /**
     * @var array
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

    private function processCreationRequests(OrderDetail $orderDetail)
    {
        $debitRequests = "";

        $i = 1;

        $productsToCreate = array();

        /** @var OrderProduct $orderProduct */
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            if(!$orderProduct->isProcessed() && $orderProduct->getRequestType() == RequestTypeEnumType::CREATION) {
                $productsToCreate [$i++] = $orderProduct;
            }
        }

        $debitRequests .= $this->setAccountCreationNodes($productsToCreate);

        $responses = $this->generateAndPostRequestAndGetResponse($debitRequests);

        $numberOfSucceedCreations = 0;
        /** @var OrderProduct $orderProduct */
        foreach($productsToCreate as $id => $orderProduct) {
            if(strtolower($responses[$id]->status) == 'ok') {
                $orderProduct->setCtrlNumber($responses[$id]->account);
                $orderProduct->setPin($responses[$id]->authcode);
                $orderProduct->setRequestType(RequestTypeEnumType::RECHARGE);
                $numberOfSucceedCreations ++;
            } else {
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
                $orderProduct->setStatusDesc(substr($responses[$id]->code.":".$responses[$id]->description, 0, 100));
            }
        }

        $this->em->flush();

        return $numberOfSucceedCreations;
    }

    private function processRechargeRequests(OrderDetail $orderDetail)
    {
        $debitRequests = "";

        $i = 1;

        $productsToRecharge = array();

        /** @var OrderProduct $orderProduct */
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            if(!$orderProduct->isProcessed() && $orderProduct->getRequestType() == RequestTypeEnumType::RECHARGE) {
                $productsToRecharge [$i++] = $orderProduct;
            }
        }

        $debitRequests .= $this->setRechargeNodes($productsToRecharge);

        $responses = $this->generateAndPostRequestAndGetResponse($debitRequests);

        /** @var OrderProduct $orderProduct */
        foreach($productsToRecharge as $id => $orderProduct) {
            if(strtolower($responses[$id]->status) == 'ok') {
                $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            } else {
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
                $orderProduct->setStatusDesc(substr($responses[$id]->code.":".$responses[$id]->description, 0, 100));
            }
        }

        $this->em->flush();
    }

    /**
     * @param OrderDetail $orderDetail
     * @return OrderDetail
     * @throws \Exception
     */
    public function processOrder(OrderDetail $orderDetail)
    {
        $numberOfProductsToCreate = 0;
        $numberOfProductsToRecharge = 0;

        /** @var OrderProduct $orderProduct */
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            if ($orderProduct->getRequestStatus() !== RequestStatusEnumType::REGISTERED)
                throw new \Exception("OrderProduct ID '" . $orderProduct->getId() . "' is '" . $orderProduct->getRequestStatus() . "'. The status must be registered.");

            $orderProduct->setRequestStatus(RequestStatusEnumType::PENDING);

            if($orderProduct->getRequestType() == RequestTypeEnumType::CREATION)
                $numberOfProductsToCreate ++;

            if($orderProduct->getRequestType() == RequestTypeEnumType::RECHARGE)
                $numberOfProductsToRecharge ++;
        }

        if(count($orderDetail->getOrderProducts()) != $numberOfProductsToCreate + $numberOfProductsToRecharge) {
            $this->em->flush();
            throw new \Exception("System found unknown request type.");
        }

        if($numberOfProductsToCreate > 0) {
            $numberOfProductsToRecharge += $this->processCreationRequests($orderDetail);
        }

        if($numberOfProductsToRecharge > 0) {
            $this->processRechargeRequests($orderDetail);
        }

        $this->em->flush();

        return $orderDetail;
    }

    public function getCallDetails(OrderProduct $orderProduct)
    {
        //request id is same as response id
        $id = 1;

        $debitRequests =
            '<DebitRequest id="'.$id.'" type="calldetails">
                <account>'.$orderProduct->getCtrlNumber().'</account>
                <startdate>'.date("m/d/y",strtotime("-1 week")).'</startdate>
                <enddate>'.date("m/d/y",strtotime("now")).'</enddate>
            </DebitRequest>
            ';


        $responses = $this->generateAndPostRequestAndGetResponse($debitRequests);

        if(isset($responses[$id]->status))
            throw new \Exception("Error in IDT: ". $responses[0]->description. " (".$responses[0]->code.")");

        return $responses[$id];
    }

    /**
     * @param string $debitRequests
     * @param bool $reportSuccesses
     * @return array
     * @throws \Exception
     */
    private function generateAndPostRequestAndGetResponse($debitRequests, $reportSuccesses = true)
    {
        $request =
            '<?xml version="1.0"?>
            <IDTDebitInterface>
            <UserInfo>
                <username>'.$this->idt_parameters['username'].'</username>
                <password>'.$this->idt_parameters['password'].'</password>
            </UserInfo>
            <DebitRequests ReportSuccesses="'.($reportSuccesses?"true":"false").'">
                '.$debitRequests.'
            </DebitRequests>
            </IDTDebitInterface>';

        Log::save(print_r($request, true), "idt_request");

        try {
            $response = $this->guzzle->post($this->idt_parameters['api_location'], null, $request)->send();
        }
        catch (\Exception $e) {
            throw new \Exception("Something wrong happened with IDT server.");
        }

        Log::save($response->getBody(), "idt_response");

        $arrayResponses = simplexml_load_string($response->getBody());

        if (isset($arrayResponses->DebitError)) {
            throw new \Exception($arrayResponses->DebitError->errordescription, 1234);
        }

        $result = array();
        /** @var \SimpleXMLElement $debitResponse */
        foreach ($arrayResponses->DebitResponses->DebitResponse as $debitResponse) {
            $result [(int) $debitResponse->attributes()->id]= $debitResponse;
        }

        return $result;
    }

    //--------------------------------

    private function setAccountCreationNodes(array $productsToCreate)
    {
        $debitRequests = "";

        /**
         * @var int $id
         * @var OrderProduct $orderProduct
         */
        foreach($productsToCreate as $id => $orderProduct) {
            $debitRequests .= '<DebitRequest id="'.$id.'" type="creation">
                <CustomerInformation><classid>'.$orderProduct->getProduct()->getClassId().'</classid></CustomerInformation>
            </DebitRequest>
            ';
        }

        return $debitRequests;
    }

    private function setRechargeNodes(array $productsToRecharge)
    {
        $debitRequests = "";

        /**
         * @var int $id
         * @var OrderProduct $orderProduct
         */
        foreach($productsToRecharge as $id => $orderProduct) {
            $debitRequests .=
                '<DebitRequest id="'.$id.'" type="misctrans">
                <account>'.$orderProduct->getCtrlNumber().'</account>
                <amount>'.$orderProduct->getPinDenomination().'</amount>
                <transtype>vendorcredit</transtype>
                <note>recharge</note>
                <balancetozero>n</balancetozero>
            </DebitRequest>
            ';
        }

        return $debitRequests;
    }
}