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
     * @var string
     */
    private $debitRequests = "";

    /**
     * @var ArrayKey array(ID of Request => ID of OrderProduct)
     */
    private $debitRequestsIDs;

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
        $this->debitRequestsIDs = new ArrayKey();
    }

    private function processCreationRequests(array $productsToCreate)
    {
        $this->debitRequests = "";
        $this->debitRequestsIDs->reset();

        /** @var OrderProduct $orderProduct */
        foreach($productsToCreate as $orderProduct) {
            $this->accountCreationRequest($orderProduct);
        }
        $this->em->flush();

        $responses = $this->generateAndPostRequestAndGetResponse();

        usort($responses, function($a, $b){
                if ($a['@attributes']['id'] == $b['@attributes']['id'])
                    return 0;
                return ($a['@attributes']['id'] < $b['@attributes']['id']) ? -1 : 1;
            });

        $i = 0;
        foreach($productsToCreate as $orderProduct) {
            $responseID = $this->debitRequestsIDs->get($orderProduct->getId());
            if($responseID === false) continue;
            $response = $responses[$i++];
            if ($response['@attributes']['id'] != $responseID)
                throw new \Exception('Error in count of responses.');

            if(strtolower($response['status']) !== 'ok')
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);

            $this->accountCreationResponse($orderProduct, $response);
        }
        $this->em->flush();
    }

    private function processSecondaryRequests(OrderDetail $orderDetail)
    {
        $this->debitRequests = "";
        $this->debitRequestsIDs->reset();

        /** @var OrderProduct $orderProduct */
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            if($orderProduct->isProcessed()) continue;

            switch ($orderProduct->getRequestType()) {
                case RequestTypeEnumType::ACTIVATION: $this->cardActivationRequest($orderProduct); break;
                case RequestTypeEnumType::RECHARGE: $this->rechargeAccountRequest($orderProduct); break;
            }
        }
        $this->em->flush();

        if($this->debitRequestsIDs->count() === 0) {
            Log::save("No Data","idt_no_data");
            return;
        }

        $responses = $this->generateAndPostRequestAndGetResponse();

        usort($responses, function($a, $b){
                if ($a['@attributes']['id'] == $b['@attributes']['id'])
                    return 0;
                return ($a['@attributes']['id'] < $b['@attributes']['id']) ? -1 : 1;
            });

        $i = 0;
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            $responseID = $this->debitRequestsIDs->get($orderProduct->getId());
            if($responseID === false) continue;
            $response = $responses[$i++];
            if($response['@attributes']['id'] != $responseID)
                throw new \Exception('Error in count of responses.');

            if(strtolower($response['status']) === 'ok')
                $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            else
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);

            switch ($orderProduct->getRequestType()) {
                case RequestTypeEnumType::ACTIVATION: $this->cardActivationResponse($orderProduct, $response); break;
                case RequestTypeEnumType::RECHARGE: $this->rechargeAccountResponse($orderProduct, $response); break;
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
        $productsToCreate = array();

        /** @var OrderProduct $orderProduct */
        foreach($orderDetail->getOrderProducts() as $orderProduct) {
            if($orderProduct->getRequestStatus() !== RequestStatusEnumType::REGISTERED)
                throw new \Exception("OrderProduct status with ID '". $orderProduct->getId(). "' is NOT registered.");

            $orderProduct->setRequestStatus(RequestStatusEnumType::PENDING);

            if($orderProduct->getRequestType() == RequestTypeEnumType::CREATION)
                $productsToCreate []= $orderProduct;
        }

        try {
            $this->processCreationRequests($productsToCreate);
            $this->processSecondaryRequests($orderDetail);
        } catch (\Exception $e) {
            /** @var OrderProduct $orderProduct */
            foreach($orderDetail->getOrderProducts() as $orderProduct) {
                $this->em->refresh($orderProduct);
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            }
            $this->em->flush();

            throw new \Exception($e->getMessage());
        }

        return $orderDetail;
    }

    public function getCallDetails(OrderProduct $orderProduct)
    {
        $this->debitRequests = "";
        $this->debitRequestsIDs->reset();

        $this->callDetailsRequest($orderProduct);

        try {
            $responses = $this->generateAndPostRequestAndGetResponse();

            return $responses[0];
        }
        catch (\Exception $e) {
            return array('status' => 'fail', 'id'=>$orderProduct->getId(), 'description' => $e->getMessage());
        }

    }

    /**
     * @param bool $reportSuccesses
     * @return array
     * @throws \Exception
     */
    private function generateAndPostRequestAndGetResponse($reportSuccesses = true)
    {
        $request =
            '<?xml version="1.0"?>
            <IDTDebitInterface>
            <UserInfo>
                <username>'.$this->idt_parameters['username'].'</username>
                <password>'.$this->idt_parameters['password'].'</password>
            </UserInfo>
            <DebitRequests ReportSuccesses="'.($reportSuccesses?"true":"false").'">
                '.$this->debitRequests.'
            </DebitRequests>
            </IDTDebitInterface>';

        Log::save(print_r($request, true),"idt_request");

        $response = $this->guzzle->post($this->idt_parameters['api_location'], null, $request)->send();

        Log::save($response->getBody(),"idt_response");

        $result = simplexml_load_string($response->getBody());

        if(isset($result->DebitError))
            throw new \Exception($result->DebitError->errordescription, 1234);

        $arrayResponses = json_decode(json_encode((array) $result->DebitResponses), true);
        return $this->debitRequestsIDs->count() <= 1 ? array($arrayResponses['DebitResponse']) : $arrayResponses['DebitResponse'];
    }

    //--------------------------------

    /**
     * @param OrderProduct $orderProduct
     */
    private function accountCreationRequest(OrderProduct $orderProduct)
    {
        $class_id = $orderProduct->getProduct()->getClassId();

        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="creation">
                <CustomerInformation><classid>'.$class_id.'</classid></CustomerInformation>
            </DebitRequest>
            ';
    }

    /**
     * @param OrderProduct $orderProduct
     */
    private function cardActivationRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="activation">
                <account>'.$orderProduct->getCtrlNumber().'</account>
            </DebitRequest>
            ';
    }

    /**
     * @param OrderProduct $orderProduct
     */
    private function rechargeAccountRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="misctrans">
                <account>'.$orderProduct->getCtrlNumber().'</account>
                <amount>'.$orderProduct->getPinDenomination().'</amount>
                <transtype>vendorcredit</transtype>
                <note>recharge</note>
                <balancetozero>n</balancetozero>
            </DebitRequest>
            ';
    }

    /**
     * @param OrderProduct $orderProduct
     */
    private function callDetailsRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="calldetails">
                <account>'.$orderProduct->getCtrlNumber().'</account>
                <startdate>'.date("mm/dd/yy",strtotime("-1 week")).'</startdate>
                <enddate>'.date("mm/dd/yy",strtotime("now")).'</enddate>
            </DebitRequest>
            ';
    }

    //--------------------------------

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     */
    private function accountCreationResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if(strtolower($debitResponse['status']) == 'ok') {
            $orderProduct->setCtrlNumber($debitResponse['account']);
            $orderProduct->setPin($debitResponse['authcode']);
            $orderProduct->setRequestType(RequestTypeEnumType::RECHARGE);
        }
        else {
            $orderProduct->setStatusDesc(substr($debitResponse['code'].":".$debitResponse['description'], 0, 100));
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     */
    private function cardActivationResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if(strtolower($debitResponse['status']) !== 'ok') {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            $orderProduct->setStatusDesc(substr($debitResponse['code'].":".$debitResponse['description'], 0, 100));
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     */
    private function rechargeAccountResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if(strtolower($debitResponse['status']) !== 'ok') {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            $orderProduct->setStatusDesc(substr($debitResponse['code'].":".$debitResponse['description'], 0, 100));
        }
    }
}