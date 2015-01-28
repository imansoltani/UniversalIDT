<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;

/**
 * Ordering
 *
 * @ORM\Table(name="ordering")
 * @ORM\Entity
 */
class Ordering
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType")
     * @ORM\Column(name="paymentMethod", type="PaymentMethodEnumType")
     */
    private $paymentMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=4, scale=2)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=2)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="charge", type="decimal", precision=4, scale=2)
     */
    private $charge;

    /**
     * @var string
     *
     * @ORM\Column(name="chargeDesc", type="string", length=45, nullable=true)
     */
    private $chargeDesc;

    /**
     * @var array
     *
     * @ORM\Column(name="deliveryMethod", type="simple_array")
     */
    private $deliveryMethod;

    /**
     * @ORM\OneToMany(targetEntity="Universal\IDTBundle\Entity\OrderProduct", mappedBy="ordering")
     */
    protected $orderProducts;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
        $this->date = new \DateTime();
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
     * Set date
     *
     * @param \DateTime $date
     * @return Ordering
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     * @return Ordering
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Ordering
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Ordering
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set charge
     *
     * @param string $charge
     * @return Ordering
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return string 
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set chargeDesc
     *
     * @param string $chargeDesc
     * @return Ordering
     */
    public function setChargeDesc($chargeDesc)
    {
        $this->chargeDesc = $chargeDesc;

        return $this;
    }

    /**
     * Get chargeDesc
     *
     * @return string 
     */
    public function getChargeDesc()
    {
        return $this->chargeDesc;
    }

    /**
     * Set deliveryMethod
     *
     * @param array $deliveryMethod
     * @return Ordering
     */
    public function setDeliveryMethod($deliveryMethod)
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    /**
     * Get deliveryMethod
     *
     * @return array 
     */
    public function getDeliveryMethod()
    {
        return $this->deliveryMethod;
    }

    /**
     * Add orderProducts
     *
     * @param OrderProduct $orderProducts
     * @return Ordering
     */
    public function addOrderProduct(OrderProduct $orderProducts)
    {
        $this->orderProducts[] = $orderProducts;

        return $this;
    }

    /**
     * Remove orderProducts
     *
     * @param OrderProduct $orderProducts
     */
    public function removeOrderProduct(OrderProduct $orderProducts)
    {
        $this->orderProducts->removeElement($orderProducts);
    }

    /**
     * Get orderProducts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderProducts()
    {
        return $this->orderProducts;
    }

    /**
     * Set user
     *
     * @param \Universal\IDTBundle\Entity\User $user
     * @return Ordering
     */
    public function setUser(\Universal\IDTBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Universal\IDTBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
