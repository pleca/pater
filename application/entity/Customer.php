<?php
//ALTER TABLE customer CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE type type TINYINT(1) NOT NULL, CHANGE country country TINYINT(1) NOT NULL, CHANGE discount discount NUMERIC(4, 2) NOT NULL, CHANGE price_group price_group TINYINT(1) NOT NULL, CHANGE sales_representative sales_representative INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="customer", uniqueConstraints={@ORM\UniqueConstraint(name="login", columns={"login"})})
 * @ORM\Entity
 */
class Customer
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
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=32, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="pass", type="string", length=81, nullable=false)
     */
    private $pass;

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
    private $country = '1';

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
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="date", nullable=false)
     */
    private $dateAdd;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="decimal", precision=4, scale=2, nullable=false)
     */
    private $discount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="price_group", type="boolean", nullable=false)
     */
    private $priceGroup = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="sales_representative", type="integer", nullable=false)
     */
    private $salesRepresentative;

    /**
     * @var boolean
     *
     * @ORM\Column(name="only_netto_prices", type="boolean", nullable=false)
     */
    private $onlyNettoPrices;

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
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
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
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * @param \DateTime $dateAdd
     */
    public function setDateAdd($dateAdd)
    {
        $this->dateAdd = $dateAdd;
    }

    /**
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param string $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return bool
     */
    public function isPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param bool $priceGroup
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    /**
     * @return int
     */
    public function getSalesRepresentative()
    {
        return $this->salesRepresentative;
    }

    /**
     * @param int $salesRepresentative
     */
    public function setSalesRepresentative($salesRepresentative)
    {
        $this->salesRepresentative = $salesRepresentative;
    }

    /**
     * @return bool
     */
    public function isOnlyNettoPrices()
    {
        return $this->onlyNettoPrices;
    }

    /**
     * @param bool $onlyNettoPrices
     */
    public function setOnlyNettoPrices($onlyNettoPrices)
    {
        $this->onlyNettoPrices = $onlyNettoPrices;
    }


}

