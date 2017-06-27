<?php
//ALTER TABLE categories CHANGE show_expanded show_expanded TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity
 */
class Categories
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
     * @var integer
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;

    /**
     * @var integer
     *
     * @ORM\Column(name="old_category_id", type="integer", nullable=false)
     */
    private $oldCategoryId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_expanded", type="boolean", nullable=false)
     */
    private $showExpanded;

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

    /**
     * @return int
     */
    public function getOldCategoryId()
    {
        return $this->oldCategoryId;
    }

    /**
     * @param int $oldCategoryId
     */
    public function setOldCategoryId($oldCategoryId)
    {
        $this->oldCategoryId = $oldCategoryId;
    }

    /**
     * @return bool
     */
    public function isShowExpanded()
    {
        return $this->showExpanded;
    }

    /**
     * @param bool $showExpanded
     */
    public function setShowExpanded($showExpanded)
    {
        $this->showExpanded = $showExpanded;
    }


}

