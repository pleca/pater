<?php
//ALTER TABLE logistic_services CHANGE id id INT AUTO_INCREMENT NOT NULL;

namespace Application\Entity;

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
    public function getCompanyAddress1()
    {
        return $this->companyAddress1;
    }

    /**
     * @param string $companyAddress1
     */
    public function setCompanyAddress1($companyAddress1)
    {
        $this->companyAddress1 = $companyAddress1;
    }

    /**
     * @return string
     */
    public function getCompanyAddress2()
    {
        return $this->companyAddress2;
    }

    /**
     * @param string $companyAddress2
     */
    public function setCompanyAddress2($companyAddress2)
    {
        $this->companyAddress2 = $companyAddress2;
    }

    /**
     * @return string
     */
    public function getCompanyAddress3()
    {
        return $this->companyAddress3;
    }

    /**
     * @param string $companyAddress3
     */
    public function setCompanyAddress3($companyAddress3)
    {
        $this->companyAddress3 = $companyAddress3;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @param string $customerName
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;
    }

    /**
     * @return string
     */
    public function getCustomerAddress1()
    {
        return $this->customerAddress1;
    }

    /**
     * @param string $customerAddress1
     */
    public function setCustomerAddress1($customerAddress1)
    {
        $this->customerAddress1 = $customerAddress1;
    }

    /**
     * @return string
     */
    public function getCustomerAddress2()
    {
        return $this->customerAddress2;
    }

    /**
     * @param string $customerAddress2
     */
    public function setCustomerAddress2($customerAddress2)
    {
        $this->customerAddress2 = $customerAddress2;
    }

    /**
     * @return string
     */
    public function getCustomerAddress3()
    {
        return $this->customerAddress3;
    }

    /**
     * @param string $customerAddress3
     */
    public function setCustomerAddress3($customerAddress3)
    {
        $this->customerAddress3 = $customerAddress3;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @return string
     */
    public function getGtin()
    {
        return $this->gtin;
    }

    /**
     * @param string $gtin
     */
    public function setGtin($gtin)
    {
        $this->gtin = $gtin;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getBestBefore()
    {
        return $this->bestBefore;
    }

    /**
     * @param string $bestBefore
     */
    public function setBestBefore($bestBefore)
    {
        $this->bestBefore = $bestBefore;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getCountItem()
    {
        return $this->countItem;
    }

    /**
     * @param string $countItem
     */
    public function setCountItem($countItem)
    {
        $this->countItem = $countItem;
    }

    /**
     * @return string
     */
    public function getCountEuro()
    {
        return $this->countEuro;
    }

    /**
     * @param string $countEuro
     */
    public function setCountEuro($countEuro)
    {
        $this->countEuro = $countEuro;
    }

    /**
     * @return string
     */
    public function getSscc()
    {
        return $this->sscc;
    }

    /**
     * @param string $sscc
     */
    public function setSscc($sscc)
    {
        $this->sscc = $sscc;
    }

    /**
     * @return string
     */
    public function getPaletteHeight()
    {
        return $this->paletteHeight;
    }

    /**
     * @param string $paletteHeight
     */
    public function setPaletteHeight($paletteHeight)
    {
        $this->paletteHeight = $paletteHeight;
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
     * @return \DateTime
     */
    public function getDateMod()
    {
        return $this->dateMod;
    }

    /**
     * @param \DateTime $dateMod
     */
    public function setDateMod($dateMod)
    {
        $this->dateMod = $dateMod;
    }


}

