<?php
//ALTER TABLE menu CHANGE parent_id parent_id INT NOT NULL, CHANGE `order` `order` TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity
 */
class Menu
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
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="text", length=255, nullable=false)
     */
    private $group;

    /**
     * @var boolean
     *
     * @ORM\Column(name="target", type="boolean", nullable=false)
     */
    private $target;

    /**
     * @var boolean
     *
     * @ORM\Column(name="order", type="boolean", nullable=false)
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
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
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
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return bool
     */
    public function isTarget()
    {
        return $this->target;
    }

    /**
     * @param bool $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
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


}

