<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SeoSettings
 *
 * @ORM\Table(name="seo_settings")
 * @ORM\Entity
 */
class SeoSettings
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
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", length=255, nullable=false)
     */
    private $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="text", length=255, nullable=false)
     */
    private $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_alt", type="text", length=255, nullable=false)
     */
    private $logoAlt;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_header1", type="text", length=255, nullable=false)
     */
    private $accordionHeader1;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_content1", type="text", length=65535, nullable=false)
     */
    private $accordionContent1;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_header2", type="text", length=255, nullable=false)
     */
    private $accordionHeader2;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_content2", type="text", length=65535, nullable=false)
     */
    private $accordionContent2;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_header3", type="text", length=255, nullable=false)
     */
    private $accordionHeader3;

    /**
     * @var string
     *
     * @ORM\Column(name="accordion_content3", type="text", length=65535, nullable=false)
     */
    private $accordionContent3;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=false)
     */
    private $locale;


}

