<?php
//ALTER TABLE order_address CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE order_id order_id INT NOT NULL, CHANGE type type TINYINT(1) NOT NULL, CHANGE shipping_type shipping_type TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderAddress
 *
 * @ORM\Table(name="order_address")
 * @ORM\Entity
 */
class OrderAddress
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", nullable=false)
     */
    private $model;

    /**
     * @var boolean
     *
     * @ORM\Column(name="type", type="boolean", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=100, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="nip", type="string", length=16, nullable=false)
     */
    private $nip;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=100, nullable=false)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=100, nullable=false)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="address3", type="string", length=100, nullable=false)
     */
    private $address3;

    /**
     * @var string
     *
     * @ORM\Column(name="post_code", type="string", length=16, nullable=false)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=100, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string", length=100, nullable=false)
     */
    private $province;

    /**
     * @var boolean
     *
     * @ORM\Column(name="country", type="boolean", nullable=false)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=false)
     */
    private $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="shipping_type", type="boolean", nullable=false)
     */
    private $shippingType;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_company_name", type="string", length=100, nullable=false)
     */
    private $shippingCompanyName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_nip", type="string", length=16, nullable=false)
     */
    private $shippingNip;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_first_name", type="string", length=100, nullable=false)
     */
    private $shippingFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_last_name", type="string", length=100, nullable=false)
     */
    private $shippingLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address1", type="string", length=100, nullable=false)
     */
    private $shippingAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address2", type="string", length=100, nullable=false)
     */
    private $shippingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address3", type="string", length=100, nullable=false)
     */
    private $shippingAddress3;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_post_code", type="string", length=16, nullable=false)
     */
    private $shippingPostCode;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_city", type="string", length=100, nullable=false)
     */
    private $shippingCity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="shipping_country", type="boolean", nullable=false)
     */
    private $shippingCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_phone", type="string", length=16, nullable=false)
     */
    private $shippingPhone;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return bool
     */
    public function isType()
    {
        return $this->type;
    }

    /**
     * @param bool $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getNip()
    {
        return $this->nip;
    }

    /**
     * @param string $nip
     */
    public function setNip($nip)
    {
        $this->nip = $nip;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @param string $postCode
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param string $province
     */
    public function setProvince($province)
    {
        $this->province = $province;
    }

    /**
     * @return bool
     */
    public function isCountry()
    {
        return $this->country;
    }

    /**
     * @param bool $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return bool
     */
    public function isShippingType()
    {
        return $this->shippingType;
    }

    /**
     * @param bool $shippingType
     */
    public function setShippingType($shippingType)
    {
        $this->shippingType = $shippingType;
    }

    /**
     * @return string
     */
    public function getShippingCompanyName()
    {
        return $this->shippingCompanyName;
    }

    /**
     * @param string $shippingCompanyName
     */
    public function setShippingCompanyName($shippingCompanyName)
    {
        $this->shippingCompanyName = $shippingCompanyName;
    }

    /**
     * @return string
     */
    public function getShippingNip()
    {
        return $this->shippingNip;
    }

    /**
     * @param string $shippingNip
     */
    public function setShippingNip($shippingNip)
    {
        $this->shippingNip = $shippingNip;
    }

    /**
     * @return string
     */
    public function getShippingFirstName()
    {
        return $this->shippingFirstName;
    }

    /**
     * @param string $shippingFirstName
     */
    public function setShippingFirstName($shippingFirstName)
    {
        $this->shippingFirstName = $shippingFirstName;
    }

    /**
     * @return string
     */
    public function getShippingLastName()
    {
        return $this->shippingLastName;
    }

    /**
     * @param string $shippingLastName
     */
    public function setShippingLastName($shippingLastName)
    {
        $this->shippingLastName = $shippingLastName;
    }

    /**
     * @return string
     */
    public function getShippingAddress1()
    {
        return $this->shippingAddress1;
    }

    /**
     * @param string $shippingAddress1
     */
    public function setShippingAddress1($shippingAddress1)
    {
        $this->shippingAddress1 = $shippingAddress1;
    }

    /**
     * @return string
     */
    public function getShippingAddress2()
    {
        return $this->shippingAddress2;
    }

    /**
     * @param string $shippingAddress2
     */
    public function setShippingAddress2($shippingAddress2)
    {
        $this->shippingAddress2 = $shippingAddress2;
    }

    /**
     * @return string
     */
    public function getShippingAddress3()
    {
        return $this->shippingAddress3;
    }

    /**
     * @param string $shippingAddress3
     */
    public function setShippingAddress3($shippingAddress3)
    {
        $this->shippingAddress3 = $shippingAddress3;
    }

    /**
     * @return string
     */
    public function getShippingPostCode()
    {
        return $this->shippingPostCode;
    }

    /**
     * @param string $shippingPostCode
     */
    public function setShippingPostCode($shippingPostCode)
    {
        $this->shippingPostCode = $shippingPostCode;
    }

    /**
     * @return string
     */
    public function getShippingCity()
    {
        return $this->shippingCity;
    }

    /**
     * @param string $shippingCity
     */
    public function setShippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;
    }

    /**
     * @return bool
     */
    public function isShippingCountry()
    {
        return $this->shippingCountry;
    }

    /**
     * @param bool $shippingCountry
     */
    public function setShippingCountry($shippingCountry)
    {
        $this->shippingCountry = $shippingCountry;
    }

    /**
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->shippingPhone;
    }

    /**
     * @param string $shippingPhone
     */
    public function setShippingPhone($shippingPhone)
    {
        $this->shippingPhone = $shippingPhone;
    }


}

