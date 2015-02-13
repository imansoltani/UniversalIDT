<?php
namespace Universal\IDTBundle\Service;

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
class IDT
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

    /**
     * @param OrderDetail $order
     * @return array
     * @throws \Exception
     */
    public function doRequest(OrderDetail $order)
    {
        $this->debitRequests = "";
        $this->debitRequestsIDs->reset();


        /** @var OrderProduct $orderProduct */
        foreach($order->getOrderProducts() as $orderProduct) {
            if($orderProduct->getRequestStatus() !== RequestStatusEnumType::REGISTERED)
                throw new \Exception("OrderProduct status with ID '". $orderProduct->getId(). "' is NOT registered.");

            $orderProduct->setRequestStatus(RequestStatusEnumType::PENDING);
            switch ($orderProduct->getRequestType()) {
                case RequestTypeEnumType::CREATION: $this->accountCreationRequest($orderProduct); break;
                case RequestTypeEnumType::ACTIVATION: $this->cardActivationRequest($orderProduct); break;
                case RequestTypeEnumType::RECHARGE: $this->rechargeAccountRequest($orderProduct); break;
                case RequestTypeEnumType::DEACTIVATION: $this->cardDeactivationRequest($orderProduct); break;
            }
        }
        $this->em->flush();

        $result = array();

        try {
            $responses = $this->generateAndPostRequestAndGetResponse();
            //echo(print_r($responses, true));

            usort($result, function($a, $b){
                    if ($a['@attributes']['id'] == $b['@attributes']['id'])
                        return 0;

                    return ($a['@attributes']['id'] < $b['@attributes']['id']) ? -1 : 1;
                });

            $i = 0;
            foreach($order->getOrderProducts() as $orderProduct) {
                $responseID = $this->debitRequestsIDs->get($orderProduct->getId());
                $response = $responses[$i++];
                if($response['@attributes']['id'] != $responseID)
                    throw new \Exception('Error in count of responses.');

                switch ($orderProduct->getRequestType()) {
                    case RequestTypeEnumType::CREATION: $result []= $this->accountCreationResponse($orderProduct, $response); break;
                    case RequestTypeEnumType::ACTIVATION: $result []= $this->cardActivationResponse($orderProduct, $response); break;
                    case RequestTypeEnumType::RECHARGE: $result []= $this->rechargeAccountResponse($orderProduct, $response); break;
                    case RequestTypeEnumType::DEACTIVATION: $result []= $this->cardDeactivationResponse($orderProduct, $response); break;
                }
            }
            $this->em->flush();

            return $result;
        }
        catch (\Exception $e) {
            /** @var OrderProduct $orderProduct */
            foreach($order->getOrderProducts() as $orderProduct) {
                $this->em->refresh($orderProduct);
                $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
                $result []= array('status' => 'serverError', 'id'=>$orderProduct->getId(), 'description' => $e->getMessage());
            }
            $this->em->flush();

            return $result;
        }
    }

    public function getCallDetails(OrderProduct $orderProduct)
    {
        $this->debitRequests = "";
        $this->debitRequestsIDs->reset();

        $this->callDetailsRequest($orderProduct);

        try {
            $responses = $this->generateAndPostRequestAndGetResponse();

            return $this->callDetailsResponse($orderProduct, $responses[0]);
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

        $response = $this->guzzle->post($this->idt_parameters['api_location'], null, $request)->send();

        $result = simplexml_load_string($response->getBody());

        if(isset($result->DebitError))
            throw new \Exception($result->DebitError->errordescription, 1234);

        $arrayResponses = json_decode(json_encode((array) $result->DebitResponses), true);
        return $arrayResponses['DebitResponse'];
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

        $this->rechargeAccountRequest($orderProduct);
    }

    /**
     * @param OrderProduct $orderProduct
     */
    private function rechargeAccountRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="recharge">
                <CustomerInformation>
                    <account>'.$orderProduct->getCtrlNumber().'</account>
                </CustomerInformation>
                <CreditCard>
                    <amount>'.$orderProduct->getOrderDetail()->getAmount().'</amount>
                </CreditCard>
            </DebitRequest>
            ';
    }

    /**
     * @param OrderProduct $orderProduct
     */
    private function cardDeactivationRequest(OrderProduct $orderProduct)
    {
        $this->debitRequests .=
            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="deactivation">
                <account>'.$orderProduct->getCtrlNumber().'</account>
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

//    /**
//     * @param OrderProduct $orderProduct
//     */
//    private function QueryRequestsRequest(OrderProduct $orderProduct, \DateTime $start_date, \DateTime $end_date)
//    {
//        $this->debitRequests .=
//            '<DebitRequest id="'.$this->debitRequestsIDs->add($orderProduct->getId()).'" type="queryrequests">
//                <account>'.$orderProduct->getCtrlNumber().'</account>
//                <startdate>'.date("mm/dd/yy",strtotime("-1 week")).'</startdate>
//                <enddate>'.date("mm/dd/yy",strtotime("now")).'</enddate>
//            </DebitRequest>
//            ';
//    }

    //--------------------------------

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     * @return array
     */
    private function accountCreationResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if($debitResponse['status'] == 'OK') {
            $orderProduct->setCtrlNumber($debitResponse['account']);
            $orderProduct->setPin($debitResponse['pin']);
            $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            return array('status' => 'ok', 'id'=>$orderProduct->getId());
        }
        else {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            return array('status' => 'fail', 'id'=>$orderProduct->getId(), 'code' => $debitResponse['code'], 'description' => $debitResponse['description']);
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     * @return array
     */
    private function cardActivationResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if($debitResponse['status'] == 'OK') {
            $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            return array('status' => 'ok', 'id'=>$orderProduct->getId());
        }
        else {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            return array('status' => 'fail', 'id'=>$orderProduct->getId(), 'code' => $debitResponse['code'], 'description' => $debitResponse['description']);
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     * @return array
     */
    private function rechargeAccountResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if($debitResponse['status'] == 'OK') {
            $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            return array('status' => 'ok', 'id'=>$orderProduct->getId());
        }
        else {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            return array('status' => 'fail', 'id'=>$orderProduct->getId(), 'code' => $debitResponse['code'], 'description' => $debitResponse['description']);
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     * @return array
     */
    private function cardDeactivationResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        if($debitResponse['status'] == 'OK') {
            $orderProduct->setRequestStatus(RequestStatusEnumType::SUCCEED);
            return array('status' => 'ok', 'id'=>$orderProduct->getId());
        }
        else {
            $orderProduct->setRequestStatus(RequestStatusEnumType::FAILED);
            return array('status' => 'fail', 'id'=>$orderProduct->getId(), 'code' => $debitResponse['code'], 'description' => $debitResponse['description']);
        }
    }

    /**
     * @param OrderProduct $orderProduct
     * @param array $debitResponse
     * @return array
     */
    private function callDetailsResponse(OrderProduct $orderProduct, array $debitResponse)
    {
        return array('status' => 'ok', 'id'=>$orderProduct->getId(), 'data' => $debitResponse);
    }
}