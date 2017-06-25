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


}

