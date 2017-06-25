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


}

