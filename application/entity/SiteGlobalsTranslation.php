<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SiteGlobalsTranslation
 *
 * @ORM\Table(name="site_globals_translation", indexes={@ORM\Index(name="IDX_2AA395522C2AC5D3", columns={"translatable_id"})})
 * @ORM\Entity
 */
class SiteGlobalsTranslation
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
     * @ORM\Column(name="value", type="string", length=100, nullable=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=true)
     */
    private $locale;

    /**
     * @var \SiteGlobals
     *
     * @ORM\ManyToOne(targetEntity="SiteGlobals")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="translatable_id", referencedColumnName="id")
     * })
     */
    private $translatable;


}

