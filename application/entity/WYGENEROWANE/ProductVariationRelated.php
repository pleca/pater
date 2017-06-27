<?php

namespace Application\Entity;

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
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param int $variationId
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
    }

    /**
     * @return int
     */
    public function getVariationRelatedId()
    {
        return $this->variationRelatedId;
    }

    /**
     * @param int $variationRelatedId
     */
    public function setVariationRelatedId($variationRelatedId)
    {
        $this->variationRelatedId = $variationRelatedId;
    }


}

