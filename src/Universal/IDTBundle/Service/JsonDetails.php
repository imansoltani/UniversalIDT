<?php
namespace Universal\IDTBundle\Service;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class JsonDetails
 * @package Universal\IDTBundle\Service
 */
abstract class JsonDetails {

    /**
     * @var array
     */
    private $json = array();

    /**
     * @var string
     */
    private $folderPath = "..//app//Resources//ProductJson//";

    /**
     * Get id
     *
     * @return integer
     */
    abstract public function getId();

    /**
     * @ORM\postLoad
     */
    public function loadJson()
    {
        if(!is_null($this->getId()) && file_exists($this->folderPath.$this->getId().".json") && $jsonString = file_get_contents($this->folderPath.$this->getId().".json"))
            $this->json = json_decode($jsonString, true);
        else
            $this->json = array();
    }

    /**
     * @ORM\PostPersist
     * @ORM\PreFlush
     */
    public function saveJson()
    {
        if(!is_null($this->getId()))
            file_put_contents($this->folderPath.$this->getId().".json", json_encode($this->json, JSON_PRETTY_PRINT));
    }

    /**
     * @ORM\PreRemove
     */
    public function removeJson()
    {
        if(file_exists($this->folderPath.$this->getId().".json"))
            unlink($this->folderPath.$this->getId().".json");
    }

    //------------------------------

    /**
     * @param string $generalInformation
     * @return $this
     */
    public function setGeneralInformation($generalInformation)
    {
        $this->json['general_information'] = $generalInformation;

        return $this;
    }

    /**
     * @return string
     */
    public function getGeneralInformation()
    {
        return isset($this->json['general_information'])?$this->json['general_information']:"";
    }

    /**
     * @param string $accessNumbers
     * @return $this
     */
    public function setAccessNumbers($accessNumbers)
    {
        $this->json['access_numbers'] = $accessNumbers;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessNumbers()
    {
        return isset($this->json['access_numbers'])?$this->json['access_numbers']:"";
    }

    /**
     * @param string $dialingInstructions
     * @return $this
     */
    public function setDialingInstructions($dialingInstructions)
    {
        $this->json['dialing_instructions'] = $dialingInstructions;

        return $this;
    }

    /**
     * @return string
     */
    public function getDialingInstructions()
    {
        return isset($this->json['dialing_instructions'])?$this->json['dialing_instructions']:"";
    }
}