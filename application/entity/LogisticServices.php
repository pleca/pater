<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LogisticServices
 *
 * @ORM\Table(name="logistic_services")
 * @ORM\Entity
 */
class LogisticServices
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
     * @ORM\Column(name="company_name", type="string", length=100, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address_1", type="string", length=100, nullable=false)
     */
    private $companyAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address_2", type="string", length=100, nullable=false)
     */
    private $companyAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address_3", type="string", length=100, nullable=false)
     */
    private $companyAddress3;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_name", type="string", length=100, nullable=false)
     */
    private $customerName;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_address_1", type="string", length=100, nullable=false)
     */
    private $customerAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_address_2", type="string", length=100, nullable=false)
     */
    private $customerAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_address_3", type="string", length=100, nullable=false)
     */
    private $customerAddress3;

    /**
     * @var string
     *
     * @ORM\Column(name="product_name", type="string", length=100, nullable=false)
     */
    private $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="gtin", type="string", length=100, nullable=false)
     */
    private $gtin;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=100, nullable=false)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="best_before", type="string", length=100, nullable=false)
     */
    private $bestBefore;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string", length=100, nullable=false)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="count_item", type="string", length=100, nullable=false)
     */
    private $countItem;

    /**
     * @var string
     *
     * @ORM\Column(name="count_euro", type="string", length=100, nullable=false)
     */
    private $countEuro;

    /**
     * @var string
     *
     * @ORM\Column(name="sscc", type="string", length=100, nullable=false)
     */
    private $sscc;

    /**
     * @var string
     *
     * @ORM\Column(name="palette_height", type="string", length=100, nullable=false)
     */
    private $paletteHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=100, nullable=false)
     */
    private $login;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_mod", type="datetime", nullable=false)
     */
    private $dateMod;


}

