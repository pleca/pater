<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;

use Application\Entity\SiteGlobal;

/**
 * @ORM\Entity 
 * @ORM\Table(name="site_globals_translation")
 */
class SiteGlobalTranslation
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue 
	 */
    private $id;
	
    /**
	 * @Assert\NotBlank() 
     * @ORM\Column(type="string", length=100)
     */	
	private $value;
	
    /**
     * @ORM\Column(type="string", columnDefinition="enum('en','pl','es','de','fr')")
     */	
	private $locale;

    /**
     * Many Translations have One SiteGlobal.
     * @ORM\ManyToOne(targetEntity="SiteGlobal", inversedBy="translations")
     * @ORM\JoinColumn(name="translatable_id", referencedColumnName="id")
     */
	private $siteGlobal;	

    /**
     * Set siteGlobal
     *
     * @param \Application\Entity\SiteGlobal $siteGlobal
     * @return SiteGlobalTranslation
     */
    public function setSiteGlobal(\Application\Entity\SiteGlobal $siteGlobal = null)
    {
        $this->siteGlobal = $siteGlobal;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Application\Entity\SiteGlobal 
     */
    public function getSiteGlobal()
    {
        return $this->siteGlobal;
    }
	
    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
	
    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
	
    public function __toString() {
        return $this->getValue();
    }	

}