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

        $this->json = null;
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

    //-----------------------

    /**
     * @param string $parameter
     * @param mixed $value
     * @param string $key
     * @return $this
     */
    private function addArrayJsonParameter($parameter, $value, $key = null)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        if(is_null($key))
            $this->json[$parameter][] = $value;
        else
            $this->json[$parameter][$key] = $value;

        return $this;
    }

    /**
     * @param string $parameter
     * @param int $key
     * @return $this
     */
    private function removeArrayJsonParameter($parameter, $key)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        unset($this->json[$parameter][$key]);

        return $this;
    }

    /**
     * @param string $parameter
     * @param int $key
     * @return mixed
     */
    private function getArrayJsonParameter($parameter, $key)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        if(isset($this->json[$parameter][$key]))
            return $this->json[$parameter][$key];
        else
            return "";
    }

    /**
     * @param string $parameter
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    private function findAllArrayJsonParameter($parameter, $columnName, $value)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        return array_filter($this->json[$parameter], function ($row) use ($columnName, $value) {
                return $row[$columnName] == $value;
            });
    }

    //------------------------------ General Information

    /**
     * @param string $lang
     * @param string $generalInformation
     * @return $this
     */
    public function setGeneralInformation($lang, $generalInformation)
    {
        $this->addArrayJsonParameter('general_information', $generalInformation, $lang);

        return $this;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getGeneralInformation($lang)
    {
        return $this->getArrayJsonParameter('general_information', $lang);
    }

    /**
     * @param string $lang
     * @return string
     */
    public function removeGeneralInformation($lang)
    {
        $this->removeArrayJsonParameter('general_information', $lang);

        return $this;
    }

    //------------------------------------------ DialingInstructions

    /**
     * @param string $lang
     * @param string $dialingInstructions
     * @return $this
     */
    public function setDialingInstructions($lang, $dialingInstructions)
    {
        $this->addArrayJsonParameter('dialing_instructions', $dialingInstructions, $lang);

        return $this;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getDialingInstructions($lang)
    {
        return $this->getArrayJsonParameter('dialing_instructions', $lang);
    }

    /**
     * @param string $lang
     * @return string
     */
    public function removeDialingInstructions($lang)
    {
        $this->removeArrayJsonParameter('dialing_instructions', $lang);

        return $this;
    }

    //---------------------------------------

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