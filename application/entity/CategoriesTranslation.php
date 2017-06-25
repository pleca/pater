<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoriesTranslation
 *
 * @ORM\Table(name="categories_translation", indexes={@ORM\Index(name="translatable_id", columns={"translatable_id"})})
 * @ORM\Entity
 */
class CategoriesTranslation
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="text", length=255, nullable=false)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string", length=60, nullable=false)
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", length=255, nullable=false)
     */
    private $metaDescription;

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
     * @return int
     */
    public function getTranslatableId()
    {
        return $this->translatableId;
    }

    /**
     * @param int $translatableId
     */
    public function setTranslatableId($translatableId)
    {
        $this->translatableId = $translatableId;
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
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

