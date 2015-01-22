<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Universal\IDTBundle\Service\JsonDetails;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Product extends JsonDetails
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
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="countryISO", type="string", length=2)
     */
    private $countryISO;

    /**
     * @var string
     *
     * @ORM\Column(name="denomination", type="string", length=255)
     */
    private $denomination;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="Universal\IDTBundle\Entity\OrderProduct", mappedBy="product")
     */
    protected $orderProducts;

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
     * Set name
     *
     * @param string $name
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set countryISO
     *
     * @param string $countryISO
     * @return Product
     */
    public function setCountryISO($countryISO)
    {
        $this->countryISO = $countryISO;

        return $this;
    }

    /**
     * Get countryISO
     *
     * @return string 
     */
    public function getCountryISO()
    {
        return $this->countryISO;
    }

    /**
     * Set denomination
     *
     * @param string $denomination
     * @return Product
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;

        return $this;
    }

    /**
     * Get denomination
     *
     * @return string 
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Product
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
     * Constructor
     */
    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
    }

    /**
     * Add orderProducts
     *
     * @param OrderProduct $orderProducts
     * @return Product
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
}
