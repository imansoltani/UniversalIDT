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
    private $json = null;

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
     * Load json file
     */
    public function loadJson()
    {
        if(!is_null($this->json))
            return;

        if(!is_null($this->getId()) && file_exists($this->folderPath.$this->getId().".json") && $jsonString = file_get_contents($this->folderPath.$this->getId().".json"))
            $this->json = json_decode($jsonString, true);
        else
            $this->json = array();
    }

    /**
     * Save json file
     *
     * @ORM\PostPersist
     * @ORM\PreFlush
     */
    public function saveJson()
    {
        if(!is_null($this->getId()) && !is_null($this->json))
            file_put_contents($this->folderPath.$this->getId().".json", json_encode($this->json, JSON_PRETTY_PRINT));
    }

    /**
     * Remove json file
     *
     * @ORM\PreRemove
     */
    public function removeJson()
    {
        if(file_exists($this->folderPath.$this->getId().".json"))
            unlink($this->folderPath.$this->getId().".json");
    }

    /**
     * @param string $parameter
     * @param mixed $value
     * @return $this
     */
    private function setJsonParameter($parameter, $value)
    {
        $this->loadJson();

        $this->json[$parameter] = $value;

        return $this;
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    private function getJsonParameter($parameter)
    {
        $this->loadJson();

        return isset($this->json[$parameter])?$this->json[$parameter]:"";
    }

    //------------------------------

    /**
     * @param string $generalInformation
     * @return $this
     */
    public function setGeneralInformation($generalInformation)
    {
        $this->setJsonParameter('general_information', $generalInformation);

        return $this;
    }

    /**
     * @return string
     */
    public function getGeneralInformation()
    {
        return $this->getJsonParameter('general_information');
    }

    /**
     * @param string $accessNumbers
     * @return $this
     */
    public function setAccessNumbers($accessNumbers)
    {
        $this->setJsonParameter('access_numbers', $accessNumbers);

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessNumbers()
    {
        return $this->getJsonParameter('access_numbers');
    }

    /**
     * @param string $dialingInstructions
     * @return $this
     */
    public function setDialingInstructions($dialingInstructions)
    {
        $this->setJsonParameter('dialing_instructions', $dialingInstructions);

        return $this;
    }

    /**
     * @return string
     */
    public function getDialingInstructions()
    {
        return $this->getJsonParameter('dialing_instructions');
    }

    /**
     * @param int $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        $this->setJsonParameter('class_id', $classId);

        return $this;
    }

    /**
     * @return int
     */
    public function getClassId()
    {
        return $this->getJsonParameter('class_id');
    }
}