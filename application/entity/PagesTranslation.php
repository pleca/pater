<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PagesTranslation
 *
 * @ORM\Table(name="pages_translation", indexes={@ORM\Index(name="translatable_id", columns={"translatable_id"})})
 * @ORM\Entity
 */
class PagesTranslation
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
     * @ORM\Column(name="translatable_id", type="integer", nullable=false)
     */
    private $translatableId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="text", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string", length=60, nullable=false)
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="content_short", type="text", length=255, nullable=false)
     */
    private $contentShort;

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
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=false)
     */
    private $locale;


}

