<?php
namespace Universal\IDTBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Validator\Constraints as EnumAssert;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(name="language", type="string", length=5)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=11, nullable=true)
     * @Assert\Regex(
     *     pattern="/^\d{6,12}$/",
     *     match=true,
     *     message="Your Phone number is invalid."
     * )
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="notifications", type="json_array", nullable=true)
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
    private $orderDetails;

    /**
     * @var string
     *
     * @ORM\Column(name="new_email", type="string", length=255, nullable=true)
     */
    private $newEmail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="new_email_expire_at", type="datetime", nullable=true)
     */
    private $newEmailExpireAt;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=30)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=30)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2)
     */
    private $country;

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

    /**
     * Set newEmail
     *
     * @param string $newEmail
     * @return User
     */
    public function setNewEmail($newEmail)
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    /**
     * Get newEmail
     *
     * @return string 
     */
    public function getNewEmail()
    {
        return $this->newEmail;
    }

    /**
     * Set newEmailExpireAt
     *
     * @param \DateTime $newEmailExpireAt
     * @return User
     */
    public function setNewEmailExpireAt($newEmailExpireAt)
    {
        $this->newEmailExpireAt = $newEmailExpireAt;

        return $this;
    }

    /**
     * Get newEmailExpireAt
     *
     * @return \DateTime 
     */
    public function getNewEmailExpireAt()
    {
        return $this->newEmailExpireAt;
    }

    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }
}
