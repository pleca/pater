<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

require_once(ENTITY_DIR . '/Color.php');
use Application\Entity\Color;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity 
 * @ORM\Table(name="templates")
 */
class Template
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue 
	 */
    private $id;
	
    /**
	 * @Assert\NotBlank(message = "cron.name.not_blank")
     * @ORM\Column(type="string", length=100)
     */	
	private $name;
	
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */		
	private $slug;
	
    /**
     * @ORM\Column(type="text")
     */
    private $description;

   /**
     * One Template has Many Colors.
     * @ORM\OneToMany(targetEntity="Color", mappedBy="template")
     */	
	private $colors;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;	

    public function __construct()
    {
        $this->colors = new ArrayCollection();
    }
	
    public function __toString() {
        return $this->getName();
    }
	
    /**
     * Add color
     *
     * @param \Application\Entity\Color $color
     * @return Template
     */
    public function addColor(Color $color)
    {
        $this->colors[] = $color;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param \Application\Entity\Color $color
     */
    public function removeColor(Color $color)
    {
        $this->colors->removeElement($color);
    }
	
    /**
     * Get colors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */	
    public function getColors()
    {
        return $this->colors;
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
		
    public function getSlug()
    {
        return $this->slug;
    }
	
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }
	
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
	
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
	}	
		

}