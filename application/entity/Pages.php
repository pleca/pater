<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Pages
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity
 */
class Pages
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
     * @ORM\Column(name="gallery_id", type="integer", nullable=false)
     */
    private $galleryId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", length=255, nullable=false)
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="menu", type="boolean", nullable=false)
     */
    private $menu;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

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


}

