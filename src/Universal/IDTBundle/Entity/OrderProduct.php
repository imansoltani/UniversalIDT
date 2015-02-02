<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Sylius\Component\Cart\Model\CartItem;

/**
 * OrderProduct
 *
 * @ORM\Table(name="order_product")
 * @ORM\Entity
 */
class OrderProduct extends CartItem
{
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
     * @ORM\Column(name="request_type", type="RequestTypeEnumType", nullable=true)
     */
    private $requestType;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\RequestStatusEnumType")
     * @ORM\Column(name="request_status", type="RequestStatusEnumType", nullable=true)
     */
    private $requestStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="pinDenomination", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $pinDenomination;

    /**
     * @var integer
     *
     * @ORM\Column(name="ctrlNumber", type="integer", nullable=true)
     */
    private $ctrlNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\Product", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

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
}
