<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryDesc
 *
 * @ORM\Table(name="gallery_desc")
 * @ORM\Entity
 */
class GalleryDesc
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
     * @ORM\Column(name="lang_id", type="integer", nullable=false)
     */
    private $langId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="title_url", type="text", length=255, nullable=false)
     */
    private $titleUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="desc", type="text", length=65535, nullable=false)
     */
    private $desc;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_short", type="text", length=255, nullable=false)
     */
    private $descShort;

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


}

