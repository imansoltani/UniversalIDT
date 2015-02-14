<?php
namespace Universal\IDTBundle\Json;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class JsonDetails
 * @package Universal\IDTBundle\Service
 */
abstract class JsonParser {

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
    abstract public function getClassId();

    /**
     * Load json file
     */
    public function loadJson()
    {
        if(!is_null($this->json))
            return;

        if(!is_null($this->getClassId()) && file_exists($this->folderPath.$this->getClassId().".json") && $jsonString = file_get_contents($this->folderPath.$this->getClassId().".json"))
            $this->json = json_decode($jsonString, true);
        else
            $this->json = array();
    }

    /**
     * Save json file
     */
    public function saveJson()
    {
        if(!is_null($this->getClassId()) && !is_null($this->json))
            file_put_contents($this->folderPath.$this->getClassId().".json", json_encode($this->json, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Remove json file
     */
    public function removeJson()
    {
        if(file_exists($this->folderPath.$this->getClassId().".json"))
            unlink($this->folderPath.$this->getClassId().".json");

        $this->json = null;
    }

    //---------------------------------

    /**
     * @param string $parameter
     * @param mixed $value
     * @return $this
     */
    private function set($parameter, $value)
    {
        $this->loadJson();

        $this->json[$parameter] = $value;

        return $this;
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    private function get($parameter)
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
    private function arrayAdd($parameter, $value, $key = null)
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
    private function arrayRemoveByKey($parameter, $key)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        unset($this->json[$parameter][$key]);

        return $this;
    }

    /**
     * @param string $parameter
     * @param mixed $value
     * @return $this
     */
    private function arrayRemoveByValue($parameter, $value)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        if(($key = array_search($value, $this->json[$parameter])) !== false)
            unset($this->json[$parameter][$key]);

        return $this;
    }

    /**
     * @param string $parameter
     * @param int $key
     * @return mixed
     */
    private function arrayGet($parameter, $key)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        return isset($this->json[$parameter][$key]) ? $this->json[$parameter][$key] : "";
    }

    /**
     * @param string $parameter
     * @param array $criteria array(columnName => value)
     * @return array
     */
    private function arrayFind($parameter, array $criteria)
    {
        $this->loadJson();

        if(!isset($this->json[$parameter]))
            $this->json[$parameter] = array();

        return array_filter($this->json[$parameter], function ($row) use ($criteria) {
                foreach($criteria as $columnName => $value)
                    if ($row[$columnName] != $value) return false;
                return true;
            });
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++
    //------------------------------ General Information

    /**
     * @param string $lang
     * @param string $generalInformation
     * @return $this
     */
    public function setGeneralInformation($lang, $generalInformation)
    {
        $this->arrayAdd('general_information', $generalInformation, strtoupper($lang));

        return $this;
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function removeGeneralInformation($lang)
    {
        $this->arrayRemoveByKey('general_information', strtoupper($lang));

        return $this;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getGeneralInformation($lang)
    {
        return $this->arrayGet('general_information', strtoupper($lang));
    }

    /**
     * @return array
     */
    public function getAllGeneralInformation()
    {
        return $this->get('general_information');
    }

    //------------------------------------------ DialingInstructions

    /**
     * @param string $lang
     * @param string $dialingInstructions
     * @return $this
     */
    public function setDialingInstructions($lang, $dialingInstructions)
    {
        $this->arrayAdd('dialing_instructions', $dialingInstructions, strtoupper($lang));

        return $this;
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function removeDialingInstructions($lang)
    {
        $this->arrayRemoveByKey('dialing_instructions', strtoupper($lang));

        return $this;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getDialingInstructions($lang)
    {
        return $this->arrayGet('dialing_instructions', strtoupper($lang));
    }

    /**
     * @return array
     */
    public function getAllDialingInstructions()
    {
        return $this->get('dialing_instructions');
    }

    //--------------------------------------- AccessNumbers

    /**
     * @param string $type
     * @param string $number
     * @param string $location
     * @param string $languages comma separated
     * @return $this
     */
    public function addAccessNumber($type, $number, $location, $languages)
    {
        $this->arrayAdd('access_numbers', array("typ" => $type, "num" => $number, "loc" => $location, "lang" => strtoupper($languages)));

        return $this;
    }

    /**
     * @param string $type
     * @param string $number
     * @param string $location
     * @param string $languages comma separated
     * @return $this
     */
    public function removeAccessNumber($type, $number, $location, $languages)
    {
        $this->arrayRemoveByValue('access_numbers', array("typ" => $type, "num" => $number, "loc" => $location, "lang" => strtoupper($languages)));

        return $this;
    }

    /**
     * @param array $criteria array ($columnName => $value)
     * @return array
     */
    public function findAccessNumbers(array $criteria)
    {
        return $this->arrayFind('access_numbers', $criteria);
    }

    /**
     * @return array
     */
    public function getAllAccessNumbers()
    {
        return $this->get('access_numbers');
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++++
    //--------------------------------------------- Image
    /**
     * @var UploadedFile
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        if($this->file !== null)
            $this->setLogoExtension($this->file->getExtension());
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    //---------------

    /**
     * @param string $logoExtension
     * @return $this
     */
    public function setLogoExtension($logoExtension)
    {
        $this->set('logoExtension', $logoExtension);

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoExtension()
    {
        return $this->get('logoExtension');
    }

    //---------------

    /**
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->getLogoExtension() == "" ? null : $this->getUploadRootDir().'/'.$this->getClassId().".".$this->getLogoExtension();
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->getLogoExtension() == ""  ? null : $this->getUploadDir().'/'.$this->getClassId().".".$this->getLogoExtension();
    }

    /**
     * @return string
     */
    private function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    /**
     * @return string
     */
    private function getUploadDir()
    {
        return 'uploads/products';
    }

    /**
     * Upload Product Logo
     */
    public function uploadLogo()
    {
        if (null === $this->file) {
            return;
        }

        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getClassId() . '.' . $this->file->getExtension()
        );

        $this->file = null;
    }

    /**
     * Remove Product Logo
     */
    public function removeLogo()
    {
        if(file_exists($this->getAbsolutePath()))
            unlink($this->getAbsolutePath());

        $this->setLogoExtension("");
    }
}