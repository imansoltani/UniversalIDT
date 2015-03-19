<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Universal\IDTBundle\DBAL\Types\RequestStatusEnumType;

/**
 * OrderProduct
 *
 * @ORM\Table(name="order_product")
 * @ORM\Entity
 */
class OrderProduct
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="string", length=11, nullable=true)
     */
    private $pin;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\RequestTypeEnumType")
     * @ORM\Column(name="request_type", type="RequestTypeEnumType")
     */
    private $requestType;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\RequestStatusEnumType")
     * @ORM\Column(name="request_status", type="RequestStatusEnumType")
     */
    private $requestStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="pinDenomination", type="decimal", precision=6, scale=2)
     */
    private $pinDenomination;

    /**
     * @var string
     *
     * @ORM\Column(name="ctrlNumber", type="string", nullable=true, length=10)
     */
    private $ctrlNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="status_desc", type="string", nullable=true, length=100)
     */
    private $statusDesc;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\Product", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\OrderDetail", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="order_detail_id", referencedColumnName="id", nullable=false)
     */
    private $orderDetail;

    /**
     * @var integer
     *
     * @ORM\Column(name="vat", type="decimal", precision=4, scale=2, options={"unsigned"=true})
     */
    private $vat = 0.0;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pin
     *
     * @param string $pin
     * @return OrderProduct
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set pinDenomination
     *
     * @param string $pinDenomination
     * @return OrderProduct
     */
    public function setPinDenomination($pinDenomination)
    {
        $this->pinDenomination = $pinDenomination;

        return $this;
    }

    /**
     * Get pinDenomination
     *
     * @return string 
     */
    public function getPinDenomination()
    {
        return $this->pinDenomination;
    }

    /**
     * Set ctrlNumber
     *
     * @param string $ctrlNumber
     * @return OrderProduct
     */
    public function setCtrlNumber($ctrlNumber)
    {
        $this->ctrlNumber = $ctrlNumber;

        return $this;
    }

    /**
     * Get ctrlNumber
     *
     * @return string
     */
    public function getCtrlNumber()
    {
        return $this->ctrlNumber;
    }

    /**
     * Set product
     *
     * @param Product $product
     * @return OrderProduct
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set orderDetail
     *
     * @param OrderDetail $orderDetail
     * @return OrderProduct
     */
    public function setOrderDetail(OrderDetail $orderDetail)
    {
        $this->orderDetail = $orderDetail;

        return $this;
    }

    /**
     * Get orderDetail
     *
     * @return OrderDetail
     */
    public function getOrderDetail()
    {
        return $this->orderDetail;
    }

    /**
     * Set requestType
     *
     * @param string $requestType
     * @return OrderProduct
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * Get requestType
     *
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Set requestStatus
     *
     * @param string $requestStatus
     * @return OrderProduct
     */
    public function setRequestStatus($requestStatus)
    {
        $this->requestStatus = $requestStatus;

        return $this;
    }

    /**
     * Get requestStatus
     *
     * @return string
     */
    public function getRequestStatus()
    {
        return $this->requestStatus;
    }

    /**
     * Set statusDesc
     *
     * @param string $statusDesc
     * @return OrderProduct
     */
    public function setStatusDesc($statusDesc)
    {
        $this->statusDesc = $statusDesc;

        return $this;
    }

    /**
     * Get statusDesc
     *
     * @return string 
     */
    public function getStatusDesc()
    {
        return $this->statusDesc;
    }

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return RequestStatusEnumType::PENDING !== $this->getRequestStatus();
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return RequestStatusEnumType::REGISTERED === $this->getRequestStatus();
    }

    /**
     * @return bool
     */
    public function isSucceed()
    {
        return RequestStatusEnumType::SUCCEED === $this->getRequestStatus();
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return RequestStatusEnumType::FAILED === $this->getRequestStatus();
    }

    /**
     * Set vat
     *
     * @param integer $vat
     * @return OrderProduct
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return integer 
     */
    public function getVat()
    {
        return $this->vat;
    }
}
