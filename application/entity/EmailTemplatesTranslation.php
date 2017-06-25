<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * EmailTemplatesTranslation
 *
 * @ORM\Table(name="email_templates_translation")
 * @ORM\Entity
 */
class EmailTemplatesTranslation
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
     * @ORM\Column(name="title", type="string", length=128, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="text", length=65535, nullable=false)
     */
    private $info;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=false)
     */
    private $locale;


}

