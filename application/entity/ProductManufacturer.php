<?php
//ALTER TABLE product_manufacturer CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE status_id status_id INT NOT NULL, CHANGE popular popular TINYINT(1) NOT NULL, CHANGE `order` `order` INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductManufacturer
 *
 * @ORM\Table(name="product_manufacturer")
 * @ORM\Entity
 */
class ProductManufacturer
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
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_url", type="text", length=255, nullable=false)
     */
    private $nameUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=100, nullable=false)
     */
    private $file;

    /**
     * @var boolean
     *
     * @ORM\Column(name="popular", type="boolean", nullable=false)
     */
    private $popular;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameUrl()
    {
        return $this->nameUrl;
    }

    /**
     * @param string $nameUrl
     */
    public function setNameUrl($nameUrl)
    {
        $this->nameUrl = $nameUrl;
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
     * @return bool
     */
    public function isPopular()
    {
        return $this->popular;
    }

    /**
     * @param bool $popular
     */
    public function setPopular($popular)
    {
        $this->popular = $popular;
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

