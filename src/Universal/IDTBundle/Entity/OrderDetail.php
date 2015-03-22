<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType;

/**
 * OrderDetail
 *
 * @ORM\Table(name="order_detail")
 * @ORM\Entity(repositoryClass="Universal\IDTBundle\Entity\OrderDetailRepository")
 */
class OrderDetail
{
    /** Ogone constants */
    const OGONE_RESULT_ACCEPTED = 5;
    const OGONE_RESULT_CANCELED = 1;
    const OGONE_RESULT_DECLINED = 2;
    const OGONE_RESULT_EXCEPTION = 52;



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
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=2)
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
     * @ORM\Column(name="charge", type="decimal", precision=6, scale=2)
     */
    private $charge = 0.0;

    /**
     * @var string
     *
     * @ORM\Column(name="chargeDesc", type="string", length=45, nullable=true)
     */
    private $chargeDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="deliveryEmail", type="string", nullable=true)
     */
    private $deliveryEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="deliverySMS", type="integer", options={"unsigned"=true}, nullable=true)
     */
    private $deliverySMS;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\PaymentStatusEnumType")
     * @ORM\Column(name="paymentStatus", type="PaymentStatusEnumType")
     */
    private $paymentStatus;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\RequestsStatusEnumType")
     * @ORM\Column(name="requests_status", type="RequestsStatusEnumType", nullable=true)
     */
    private $requestsStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="paymentId", type="integer", options={"unsigned"=true}, nullable=true)
     */
    private $paymentId;

    /**
     * @ORM\OneToMany(targetEntity="Universal\IDTBundle\Entity\OrderProduct", mappedBy="orderDetail")
     */
    private $orderProducts;

    /**
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\User", inversedBy="orderDetails")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

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
     * @return OrderDetail
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
     * @return OrderDetail
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
     * @return OrderDetail
     */
    public function setAmount($amount)
    {
        $this->amount = round($amount, 2);

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
     * @return OrderDetail
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
     * @return OrderDetail
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
     * @return OrderDetail
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
     * Add orderProducts
     *
     * @param OrderProduct $orderProducts
     * @return OrderDetail
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
     * @param User $user
     * @return OrderDetail
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

    /**
     * Set deliveryEmail
     *
     * @param string $deliveryEmail
     * @return OrderDetail
     */
    public function setDeliveryEmail($deliveryEmail)
    {
        $this->deliveryEmail = $deliveryEmail;

        return $this;
    }

    /**
     * Get deliveryEmail
     *
     * @return string
     */
    public function getDeliveryEmail()
    {
        return $this->deliveryEmail;
    }

    /**
     * Set deliverySMS
     *
     * @param string $deliverySMS
     * @return OrderDetail
     */
    public function setDeliverySMS($deliverySMS)
    {
        $this->deliverySMS = $deliverySMS;

        return $this;
    }

    /**
     * Get deliverySMS
     *
     * @return string
     */
    public function getDeliverySMS()
    {
        return $this->deliverySMS;
    }

    /**
     * Set paymentStatus
     *
     * @param string $paymentStatus
     * @return OrderDetail
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get paymentStatus
     *
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return PaymentStatusEnumType::STATUS_PENDING !== $this->getPaymentStatus();
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return PaymentStatusEnumType::STATUS_ACCEPTED === $this->getPaymentStatus();
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return PaymentStatusEnumType::STATUS_CANCELED === $this->getPaymentStatus();
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->getDate()->format("ymdHi").$this->getId();
    }

    public function getOgoneAmount()
    {
        return $this->amount * 100;
    }

    /**
     * Set paymentId
     *
     * @param integer $paymentId
     * @return OrderDetail
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Get paymentId
     *
     * @return integer 
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Set requestsStatus
     *
     * @param string $requestsStatus
     * @return OrderDetail
     */
    public function setRequestsStatus($requestsStatus)
    {
        $this->requestsStatus = $requestsStatus;

        return $this;
    }

    /**
     * Get requestsStatus
     *
     * @return string
     */
    public function getRequestsStatus()
    {
        return $this->requestsStatus;
    }
}
