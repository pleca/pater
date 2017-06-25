<?php
//ALTER TABLE product_image CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE product_id product_id INT NOT NULL, CHANGE ga_image_id ga_image_id INT NOT NULL, CHANGE `order` `order` INT NOT NULL;

namespace Application\Entity;

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
    public function getGaImageId()
    {
        return $this->gaImageId;
    }

    /**
     * @param int $gaImageId
     */
    public function setGaImageId($gaImageId)
    {
        $this->gaImageId = $gaImageId;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
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

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


}

