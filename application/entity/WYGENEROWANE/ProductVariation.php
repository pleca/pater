<?php
//ALTER TABLE product_variation CHANGE id2 id2 INT AUTO_INCREMENT NOT NULL, CHANGE product_id product_id INT NOT NULL, CHANGE tax_id tax_id INT NOT NULL, CHANGE qty qty INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductVariation
 *
 * @ORM\Table(name="product_variation", indexes={@ORM\Index(name="product_id", columns={"product_id"})})
 * @ORM\Entity
 */
class ProductVariation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id2", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id2;

    /**
     * @var integer
     *
     * @ORM\Column(name="product_id", type="integer", nullable=false)
     */
    private $productId;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_id", type="integer", nullable=false)
     */
    private $taxId;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=40, nullable=false)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="ean", type="string", length=13, nullable=true)
     */
    private $ean;

    /**
     * @var string
     *
     * @ORM\Column(name="price_purchase", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $pricePurchase;

    /**
     * @var string
     *
     * @ORM\Column(name="price_rrp", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $priceRrp;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="price2", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price2;

    /**
     * @var string
     *
     * @ORM\Column(name="price3", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price3;

    /**
     * @var string
     *
     * @ORM\Column(name="price_promotion", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $pricePromotion;

    /**
     * @var string
     *
     * @ORM\Column(name="promotion", type="string", nullable=false)
     */
    private $promotion;

    /**
     * @var string
     *
     * @ORM\Column(name="bestseller", type="string", nullable=false)
     */
    private $bestseller;

    /**
     * @var string
     *
     * @ORM\Column(name="recommended", type="string", nullable=false)
     */
    private $recommended;

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_page", type="boolean", nullable=true)
     */
    private $mainPage;

    /**
     * @var string
     *
     * @ORM\Column(name="mega_offer", type="string", nullable=true)
     */
    private $megaOffer;

    /**
     * @var string
     *
     * @ORM\Column(name="special_link_content", type="string", length=255, nullable=false)
     */
    private $specialLinkContent;

    /**
     * @var string
     *
     * @ORM\Column(name="special_link_url", type="string", length=255, nullable=false)
     */
    private $specialLinkUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="length", type="decimal", precision=6, scale=1, nullable=true)
     */
    private $length;

    /**
     * @var string
     *
     * @ORM\Column(name="width", type="decimal", precision=6, scale=1, nullable=true)
     */
    private $width;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="decimal", precision=6, scale=1, nullable=true)
     */
    private $height;

    /**
     * @var integer
     *
     * @ORM\Column(name="transport_group_id", type="integer", nullable=true)
     */
    private $transportGroupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="transport_unit_id", type="integer", nullable=true)
     */
    private $transportUnitId;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", precision=6, scale=0, nullable=false)
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="qty", type="integer", nullable=false)
     */
    private $qty;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_promotion", type="date", nullable=false)
     */
    private $datePromotion;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature1_value_id", type="integer", nullable=false)
     */
    private $feature1ValueId;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature2_value_id", type="integer", nullable=false)
     */
    private $feature2ValueId;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature3_value_id", type="integer", nullable=false)
     */
    private $feature3ValueId;

    /**
     * @return int
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * @param int $id2
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * @param int $taxId
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return string
     */
    public function getPricePurchase()
    {
        return $this->pricePurchase;
    }

    /**
     * @param string $pricePurchase
     */
    public function setPricePurchase($pricePurchase)
    {
        $this->pricePurchase = $pricePurchase;
    }

    /**
     * @return string
     */
    public function getPriceRrp()
    {
        return $this->priceRrp;
    }

    /**
     * @param string $priceRrp
     */
    public function setPriceRrp($priceRrp)
    {
        $this->priceRrp = $priceRrp;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getPrice2()
    {
        return $this->price2;
    }

    /**
     * @param string $price2
     */
    public function setPrice2($price2)
    {
        $this->price2 = $price2;
    }

    /**
     * @return string
     */
    public function getPrice3()
    {
        return $this->price3;
    }

    /**
     * @param string $price3
     */
    public function setPrice3($price3)
    {
        $this->price3 = $price3;
    }

    /**
     * @return string
     */
    public function getPricePromotion()
    {
        return $this->pricePromotion;
    }

    /**
     * @param string $pricePromotion
     */
    public function setPricePromotion($pricePromotion)
    {
        $this->pricePromotion = $pricePromotion;
    }

    /**
     * @return string
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param string $promotion
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * @return string
     */
    public function getBestseller()
    {
        return $this->bestseller;
    }

    /**
     * @param string $bestseller
     */
    public function setBestseller($bestseller)
    {
        $this->bestseller = $bestseller;
    }

    /**
     * @return string
     */
    public function getRecommended()
    {
        return $this->recommended;
    }

    /**
     * @param string $recommended
     */
    public function setRecommended($recommended)
    {
        $this->recommended = $recommended;
    }

    /**
     * @return bool
     */
    public function isMainPage()
    {
        return $this->mainPage;
    }

    /**
     * @param bool $mainPage
     */
    public function setMainPage($mainPage)
    {
        $this->mainPage = $mainPage;
    }

    /**
     * @return string
     */
    public function getMegaOffer()
    {
        return $this->megaOffer;
    }

    /**
     * @param string $megaOffer
     */
    public function setMegaOffer($megaOffer)
    {
        $this->megaOffer = $megaOffer;
    }

    /**
     * @return string
     */
    public function getSpecialLinkContent()
    {
        return $this->specialLinkContent;
    }

    /**
     * @param string $specialLinkContent
     */
    public function setSpecialLinkContent($specialLinkContent)
    {
        $this->specialLinkContent = $specialLinkContent;
    }

    /**
     * @return string
     */
    public function getSpecialLinkUrl()
    {
        return $this->specialLinkUrl;
    }

    /**
     * @param string $specialLinkUrl
     */
    public function setSpecialLinkUrl($specialLinkUrl)
    {
        $this->specialLinkUrl = $specialLinkUrl;
    }

    /**
     * @return string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param string $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getTransportGroupId()
    {
        return $this->transportGroupId;
    }

    /**
     * @param int $transportGroupId
     */
    public function setTransportGroupId($transportGroupId)
    {
        $this->transportGroupId = $transportGroupId;
    }

    /**
     * @return int
     */
    public function getTransportUnitId()
    {
        return $this->transportUnitId;
    }

    /**
     * @param int $transportUnitId
     */
    public function setTransportUnitId($transportUnitId)
    {
        $this->transportUnitId = $transportUnitId;
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
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return \DateTime
     */
    public function getDatePromotion()
    {
        return $this->datePromotion;
    }

    /**
     * @param \DateTime $datePromotion
     */
    public function setDatePromotion($datePromotion)
    {
        $this->datePromotion = $datePromotion;
    }

    /**
     * @return int
     */
    public function getFeature1ValueId()
    {
        return $this->feature1ValueId;
    }

    /**
     * @param int $feature1ValueId
     */
    public function setFeature1ValueId($feature1ValueId)
    {
        $this->feature1ValueId = $feature1ValueId;
    }

    /**
     * @return int
     */
    public function getFeature2ValueId()
    {
        return $this->feature2ValueId;
    }

    /**
     * @param int $feature2ValueId
     */
    public function setFeature2ValueId($feature2ValueId)
    {
        $this->feature2ValueId = $feature2ValueId;
    }

    /**
     * @return int
     */
    public function getFeature3ValueId()
    {
        return $this->feature3ValueId;
    }

    /**
     * @param int $feature3ValueId
     */
    public function setFeature3ValueId($feature3ValueId)
    {
        $this->feature3ValueId = $feature3ValueId;
    }


}

