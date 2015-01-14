<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as UniqueAssert;

/**
 * OrderProduct
 *
 * @ORM\Table(name="order_product")
 * @ORM\Entity
 * @UniqueAssert\UniqueEntity(fields="pin", message="Pin_must_be_unique.")
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
     * @ORM\Column(name="pin", type="integer", nullable=true, unique=true)
     */
    private $pin;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\PinStatusEnumType")
     * @ORM\Column(name="pin_status", type="PinStatusEnumType", nullable=true)
     */
    private $pinStatus;

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
     * Set pinStatus
     *
     * @param string $pinStatus
     * @return OrderProduct
     */
    public function setPinStatus($pinStatus)
    {
        $this->pinStatus = $pinStatus;

        return $this;
    }

    /**
     * Get pinStatus
     *
     * @return string
     */
    public function getPinStatus()
    {
        return $this->pinStatus;
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
}
