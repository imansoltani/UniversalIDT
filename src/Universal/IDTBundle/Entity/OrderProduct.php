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
     * @ORM\Column(name="request_type", type="RequestTypeEnumType", nullable=true)
     */
    private $requestType;

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
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\Product", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\Ordering", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="ordering_id", referencedColumnName="id", nullable=false)
     */
    protected $ordering;


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
     * Set ordering
     *
     * @param Ordering $ordering
     * @return OrderProduct
     */
    public function setOrdering(Ordering $ordering)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get ordering
     *
     * @return Ordering
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Set requestType
     *
     * @param RequestTypeEnumType $requestType
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
     * @return RequestTypeEnumType 
     */
    public function getRequestType()
    {
        return $this->requestType;
    }
}
