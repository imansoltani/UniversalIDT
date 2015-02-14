<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rate
 *
 * @ORM\Table()
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
     * @ORM\Column(name="classId", type="integer")
     */
    private $classId;

    /**
     * @var string
     *
     * @ORM\Column(name="tollFree", type="decimal", precision=4, scale=2)
     */
    private $tollFree;

    /**
     * @var string
     *
     * @ORM\Column(name="localAccess", type="decimal", precision=4, scale=2)
     */
    private $localAccess;

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
     * Set tollFree
     *
     * @param string $tollFree
     * @return Rate
     */
    public function setTollFree($tollFree)
    {
        $this->tollFree = $tollFree;

        return $this;
    }

    /**
     * Get tollFree
     *
     * @return string 
     */
    public function getTollFree()
    {
        return $this->tollFree;
    }

    /**
     * Set localAccess
     *
     * @param string $localAccess
     * @return Rate
     */
    public function setLocalAccess($localAccess)
    {
        $this->localAccess = $localAccess;

        return $this;
    }

    /**
     * Get localAccess
     *
     * @return string 
     */
    public function getLocalAccess()
    {
        return $this->localAccess;
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
