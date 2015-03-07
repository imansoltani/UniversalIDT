<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Universal\IDTBundle\Json\JsonParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Product extends JsonParser
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
     * @ORM\Column(name="denominations", type="string", length=255)
     */
    private $denominations;

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
     * @var integer
     *
     * @ORM\Column(name="class_id", type="integer", options={"unsigned"=true}, nullable=false)
     */
    private $classId;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_extension", type="string", length=4, nullable=true)
     */
    private $logoExtension;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
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
        $this->countryISO = strtoupper($countryISO);

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
     * Set denominations
     *
     * @param array $denominations
     * @return Product
     */
    public function setDenominations(array $denominations)
    {
        $this->denominations = implode(";", array_unique($denominations));

        return $this;
    }

    /**
     * Get denominations
     *
     * @return array
     */
    public function getDenominations()
    {
        return explode(";", $this->denominations);
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Product
     */
    public function setCurrency($currency)
    {
        $this->currency = strtoupper($currency);

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

    /**
     * @ORM\PostPersist
     */
    public function saveJson()
    {
        parent::saveJson();
        $this->uploadLogo();
    }

    /**
     * @ORM\PreRemove
     */
    public function removeJson()
    {
        parent::removeJson();
        $this->removeLogo();
    }

    /**
     * Set classId
     *
     * @param integer $classId
     * @return Product
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

    //--------------------------------------------- Image
    /**
     * Set logoExtension
     *
     * @param string $logoExtension
     * @return Product
     */
    public function setLogoExtension($logoExtension)
    {
        $this->logoExtension = $logoExtension;

        return $this;
    }

    /**
     * Get logoExtension
     *
     * @return string
     */
    public function getLogoExtension()
    {
        return $this->logoExtension;
    }
    //-----------------

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
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->getLogoExtension() == "" ? null : ($this->getUploadRootDir().'/'.$this->getClassId().".".$this->getLogoExtension());
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->getLogoExtension() == ""  ? 'bundles/universalidt/home/img/no_image.png' : ($this->getUploadDir().'/'.$this->getClassId().".".$this->getLogoExtension());
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

        $this->setLogoExtension(null);
    }
}
