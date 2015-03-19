<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vat
 *
 * @ORM\Table(name="vat")
 * @ORM\Entity
 */
class Vat
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
     * @ORM\Column(name="countryISO", type="string", length=2)
     */
    private $countryISO;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=4, scale=2, options={"unsigned"=true})
     */
    private $value;


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
     * Set countryISO
     *
     * @param string $countryISO
     * @return Vat
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
     * Set value
     *
     * @param float $value
     * @return Vat
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
