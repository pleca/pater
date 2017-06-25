<?php
//ALTER TABLE payment CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE `order` `order` TINYINT(1) NOT NULL, CHANGE active active TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity
 */
class Payment
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
     * @var boolean
     *
     * @ORM\Column(name="order", type="boolean", nullable=false)
     */
    private $order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

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
     * @return bool
     */
    public function isOrder()
    {
        return $this->order;
    }

    /**
     * @param bool $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


}

