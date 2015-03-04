<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Destination
 *
 * @ORM\Table(name="destination")
 * @ORM\Entity
 */
class Destination
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
     * @ORM\Column(name="countryIso", type="string", length=2)
     */
    private $countryIso;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=30, nullable=true)
     */
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="Universal\IDTBundle\Entity\Rate", mappedBy="destination")
     */
    protected $rates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rates = new ArrayCollection();
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
     * Set countryIso
     *
     * @param string $countryIso
     * @return Destination
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
     * Set location
     *
     * @param string $location
     * @return Destination
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

    /**
     * Add rates
     *
     * @param Rate $rates
     * @return Destination
     */
    public function addRate(Rate $rates)
    {
        $this->rates[] = $rates;

        return $this;
    }

    /**
     * Remove rates
     *
     * @param Rate $rates
     */
    public function removeRate(Rate $rates)
    {
        $this->rates->removeElement($rates);
    }

    /**
     * Get rates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRates()
    {
        return $this->rates;
    }
}
