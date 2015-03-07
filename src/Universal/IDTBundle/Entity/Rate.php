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
     * @ORM\ManyToOne(targetEntity="Universal\IDTBundle\Entity\Destination", inversedBy="rates")
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", nullable=false)
     */
    protected $destination;


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
     * Set destination
     *
     * @param Destination $destination
     * @return Rate
     */
    public function setDestination(Destination $destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return Destination
     */
    public function getDestination()
    {
        return $this->destination;
    }
}
