<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

require_once(ENTITY_DIR . '/SiteGlobalTranslation.php');
use Application\Entity\SiteGlobalTranslation;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity 
 * @ORM\Table(name="site_globals")
 */
class SiteGlobal
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
	private $name;

   /**
     * One SiteGloabal has Many translations.
     * @ORM\OneToMany(targetEntity="SiteGlobalTranslation", mappedBy="siteGlobal", cascade={"persist", "remove"})
     */	
	private $translations;
	
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
	
    public function __toString() {
        return $this->getName();
    }
	
    /**
     * Add translation
     *
     * @param \Application\Entity\SiteGlobalTranslation $translation
     * @return SiteGlobal
     */
    public function addTranslation(SiteGlobalTranslation $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }

    /**
     * Remove translation
     *
     * @param \Application\Entity\SiteGlobalTranslation $translation
     */
    public function removeTranslation(SiteGlobalTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }
	
    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */	
    public function getTranslations()
    {
        return $this->translations;
    }	
	
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

	public function hasTranslation($locale) {
		if ($this->translations) {
			foreach ($this->translations as $trans) {
				if ($trans->getLocale() == $locale) {
					return true;
				}
			}
		}
		
		return false;
	}

}