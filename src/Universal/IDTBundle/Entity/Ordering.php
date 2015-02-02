<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Sylius\Component\Cart\Model\Cart;

/**
 * Ordering
 *
 * @ORM\Table(name="ordering")
 * @ORM\Entity
 */
class Ordering extends Cart
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paid_at", type="datetime", nullable=true)
     */
    private $datePay;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activated_at", type="datetime", nullable=true)
     */
    private $dateActivate;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType")
     * @ORM\Column(name="paymentMethod", type="PaymentMethodEnumType", nullable=true)
     */
    private $paymentMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=2, nullable=true)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="charge", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $charge;

    /**
     * @var string
     *
     * @ORM\Column(name="chargeDesc", type="string", length=45, nullable=true, nullable=true)
     */
    private $chargeDesc;

    /**
     * @var array
     *
     * @ORM\Column(name="deliveryMethod", type="simple_array", nullable=true)
     */
    private $deliveryMethod;

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
     * Set datePay
     *
     * @param \DateTime $datePay
     * @return Ordering
     */
    public function setDatePay($datePay)
    {
        $this->datePay = $datePay;

        return $this;
    }

    /**
     * Get datePay
     *
     * @return \DateTime
     */
    public function getDatePay()
    {
        return $this->datePay;
    }

    /**
     * Set dateActivate
     *
     * @param \DateTime $dateActivate
     * @return Ordering
     */
    public function setDateActivate($dateActivate)
    {
        $this->dateActivate = $dateActivate;

        return $this;
    }

    /**
     * Get dateActivate
     *
     * @return \DateTime
     */
    public function getDateActivate()
    {
        return $this->dateActivate;
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
     * Set user
     *
     * @param User $user
     * @return Ordering
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
