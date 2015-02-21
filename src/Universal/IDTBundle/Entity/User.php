<?php
namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=2)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=11)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="notifications", type="json_array")
     */
    private $notifications;

    /**
     * @var string
     *
     * @EnumAssert\Enum(entity="Universal\IDTBundle\DBAL\Types\GenderEnumType")
     * @ORM\Column(name="gender", type="GenderEnumType")
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="Universal\IDTBundle\Entity\OrderDetail", mappedBy="user")
     */
    protected $orderDetails;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderDetails = new ArrayCollection();
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
     * Set language
     *
     * @param string $language
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Add orderDetails
     *
     * @param OrderDetail $orderDetails
     * @return User
     */
    public function addOrderDetail(OrderDetail $orderDetails)
    {
        $this->orderDetails[] = $orderDetails;

        return $this;
    }

    /**
     * Remove orderDetails
     *
     * @param OrderDetail $orderDetails
     */
    public function removeOrderDetail(OrderDetail $orderDetails)
    {
        $this->orderDetails->removeElement($orderDetails);
    }

    /**
     * Get orderDetails
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set notifications
     *
     * @param array $notifications
     * @return User
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;

        return $this;
    }

    /**
     * Get notifications
     *
     * @return array 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
