<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;

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
     * @var integer
     *
     * @ORM\Column(name="pin", type="integer", nullable=true)
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
     * @ORM\Column(name="pinDenomination", type="decimal", precision=4, scale=2)
     */
    private $pinDenomination;

    /**
     * @var integer
     *
     * @ORM\Column(name="ctrlNumber", type="integer", nullable=true)
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
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\OrderDetail", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="order_detail_id", referencedColumnName="id", nullable=false)
     */
    protected $orderDetail;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer", nullable=false)
     */
    private $count = 1;


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
     * @param integer $pin
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
     * @return integer 
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
     * @param integer $ctrlNumber
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
     * @return integer 
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
     * Set count
     *
     * @param integer $count
     * @return OrderProduct
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
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
}
