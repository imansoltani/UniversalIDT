<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;

/**
 * Rate
 *
 * @ORM\Table(name="rate")
 * @ORM\Entity
 */
class Rate
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
     * @ORM\Column(name="classId", type="integer", options={"unsigned"=true})
     */
    private $classId;

    /**
     * @var string
     *
     * @ORM\Column(name="cost", type="decimal", precision=6, scale=2)
     */
    private $cost;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\RateEnumType")
     * @ORM\Column(name="type", type="RateEnumType")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="connectionFees", type="decimal", precision=4, scale=2)
     */
    private $connectionFees;

    /**
     * @var string
     *
     * @ORM\Column(name="countryIso", type="string", length=2)
     */
    private $countryIso;

    /**
     * @var string
     *
     * @ORM\Column(name="countryName", type="string", length=60)
     */
    private $countryName;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=30, nullable=true)
     */
    private $location;

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
     * Set classId
     *
     * @param integer $classId
     * @return Rate
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;

        return $this;
    }

    /**
     * Get classId
     *
     * @return integer 
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * Set cost
     *
     * @param string $cost
     * @return Rate
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Rate
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set connectionFees
     *
     * @param string $connectionFees
     * @return Rate
     */
    public function setConnectionFees($connectionFees)
    {
        $this->connectionFees = $connectionFees;

        return $this;
    }

    /**
     * Get connectionFees
     *
     * @return string 
     */
    public function getConnectionFees()
    {
        return $this->connectionFees;
    }

    /**
     * Set countryIso
     *
     * @param string $countryIso
     * @return Rate
     */
    public function setCountryIso($countryIso)
    {
        $this->countryIso = $countryIso;

        return $this;
    }

    /**
     * Get countryIso
     *
     * @return string 
     */
    public function getCountryIso()
    {
        return $this->countryIso;
    }

    /**
     * Set countryName
     *
     * @param string $countryName
     * @return Rate
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string 
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Rate
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }
}
