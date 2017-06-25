<?php

namespace Application\Entity;

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getLogoAlt()
    {
        return $this->logoAlt;
    }

    /**
     * @param string $logoAlt
     */
    public function setLogoAlt($logoAlt)
    {
        $this->logoAlt = $logoAlt;
    }

    /**
     * @return string
     */
    public function getAccordionHeader1()
    {
        return $this->accordionHeader1;
    }

    /**
     * @param string $accordionHeader1
     */
    public function setAccordionHeader1($accordionHeader1)
    {
        $this->accordionHeader1 = $accordionHeader1;
    }

    /**
     * @return string
     */
    public function getAccordionContent1()
    {
        return $this->accordionContent1;
    }

    /**
     * @param string $accordionContent1
     */
    public function setAccordionContent1($accordionContent1)
    {
        $this->accordionContent1 = $accordionContent1;
    }

    /**
     * @return string
     */
    public function getAccordionHeader2()
    {
        return $this->accordionHeader2;
    }

    /**
     * @param string $accordionHeader2
     */
    public function setAccordionHeader2($accordionHeader2)
    {
        $this->accordionHeader2 = $accordionHeader2;
    }

    /**
     * @return string
     */
    public function getAccordionContent2()
    {
        return $this->accordionContent2;
    }

    /**
     * @param string $accordionContent2
     */
    public function setAccordionContent2($accordionContent2)
    {
        $this->accordionContent2 = $accordionContent2;
    }

    /**
     * @return string
     */
    public function getAccordionHeader3()
    {
        return $this->accordionHeader3;
    }

    /**
     * @param string $accordionHeader3
     */
    public function setAccordionHeader3($accordionHeader3)
    {
        $this->accordionHeader3 = $accordionHeader3;
    }

    /**
     * @return string
     */
    public function getAccordionContent3()
    {
        return $this->accordionContent3;
    }

    /**
     * @param string $accordionContent3
     */
    public function setAccordionContent3($accordionContent3)
    {
        $this->accordionContent3 = $accordionContent3;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }


}

