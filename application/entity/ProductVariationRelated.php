<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ProductVariationRelated
 *
 * @ORM\Table(name="product_variation_related")
 * @ORM\Entity
 */
class ProductVariationRelated
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
     * @ORM\Column(name="variation_id", type="integer", nullable=false)
     */
    private $variationId;

    /**
     * @var integer
     *
     * @ORM\Column(name="variation_related_id", type="integer", nullable=false)
     */
    private $variationRelatedId;


}

