<?php



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


}

