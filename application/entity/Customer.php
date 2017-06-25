<?php



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


}

