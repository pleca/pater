<?php
//ALTER TABLE comments CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT NOT NULL, CHANGE customer_id customer_id INT NOT NULL, CHANGE lang_id lang_id INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comments
 *
 * @ORM\Table(name="comments")
 * @ORM\Entity
 */
class Comments
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
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="lang_id", type="integer", nullable=false)
     */
    private $langId;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="text", length=255, nullable=false)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="desc", type="text", length=65535, nullable=false)
     */
    private $desc;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=32, nullable=false)
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="note", type="boolean", nullable=false)
     */
    private $note;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;


}

