<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ProductImage
 *
 * @ORM\Table(name="product_image", indexes={@ORM\Index(name="product_id", columns={"product_id"})})
 * @ORM\Entity
 */
class ProductImage
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
     * @ORM\Column(name="product_id", type="integer", nullable=false)
     */
    private $productId;

    /**
     * @var integer
     *
     * @ORM\Column(name="variation_id", type="integer", nullable=false)
     */
    private $variationId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ga_image_id", type="integer", nullable=false)
     */
    private $gaImageId;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="text", length=255, nullable=false)
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_mod", type="date", nullable=false)
     */
    private $dateMod;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;


}

