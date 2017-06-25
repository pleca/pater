<?php
//ALTER TABLE product CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE category_id category_id INT NOT NULL, CHANGE producer_id producer_id INT NOT NULL, CHANGE status_id status_id INT NOT NULL, CHANGE type type VARCHAR(255) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(name="status_id", columns={"status_id"})})
 * @ORM\Entity
 */
class Product
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
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId;

    /**
     * @var integer
     *
     * @ORM\Column(name="producer_id", type="integer", nullable=false)
     */
    private $producerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature1_id", type="integer", nullable=false)
     */
    private $feature1Id;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature2_id", type="integer", nullable=false)
     */
    private $feature2Id;

    /**
     * @var integer
     *
     * @ORM\Column(name="feature3_id", type="integer", nullable=false)
     */
    private $feature3Id;

    /**
     * @var string
     *
     * @ORM\Column(name="tag1", type="text", length=255, nullable=false)
     */
    private $tag1;

    /**
     * @var string
     *
     * @ORM\Column(name="tag2", type="text", length=255, nullable=false)
     */
    private $tag2;

    /**
     * @var string
     *
     * @ORM\Column(name="tag3", type="text", length=255, nullable=false)
     */
    private $tag3;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="date", nullable=false)
     */
    private $dateAdd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_mod", type="date", nullable=false)
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
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return int
     */
    public function getProducerId()
    {
        return $this->producerId;
    }

    /**
     * @param int $producerId
     */
    public function setProducerId($producerId)
    {
        $this->producerId = $producerId;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @param int $statusId
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getFeature1Id()
    {
        return $this->feature1Id;
    }

    /**
     * @param int $feature1Id
     */
    public function setFeature1Id($feature1Id)
    {
        $this->feature1Id = $feature1Id;
    }

    /**
     * @return int
     */
    public function getFeature2Id()
    {
        return $this->feature2Id;
    }

    /**
     * @param int $feature2Id
     */
    public function setFeature2Id($feature2Id)
    {
        $this->feature2Id = $feature2Id;
    }

    /**
     * @return int
     */
    public function getFeature3Id()
    {
        return $this->feature3Id;
    }

    /**
     * @param int $feature3Id
     */
    public function setFeature3Id($feature3Id)
    {
        $this->feature3Id = $feature3Id;
    }

    /**
     * @return string
     */
    public function getTag1()
    {
        return $this->tag1;
    }

    /**
     * @param string $tag1
     */
    public function setTag1($tag1)
    {
        $this->tag1 = $tag1;
    }

    /**
     * @return string
     */
    public function getTag2()
    {
        return $this->tag2;
    }

    /**
     * @param string $tag2
     */
    public function setTag2($tag2)
    {
        $this->tag2 = $tag2;
    }

    /**
     * @return string
     */
    public function getTag3()
    {
        return $this->tag3;
    }

    /**
     * @param string $tag3
     */
    public function setTag3($tag3)
    {
        $this->tag3 = $tag3;
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

