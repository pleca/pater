<?php
//ALTER TABLE colors CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL;

namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;

use Application\Entity\Template;

/**
 * @ORM\Entity 
 * @ORM\Table(name="colors")
 */
class Color
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer", options={"unsigned"=true}) 
	 * @ORM\GeneratedValue 
	 */
    private $id;
	
    /**
	 * @Assert\NotBlank(message = "color.name.not_blank") 
     * @ORM\Column(type="string", length=100)
     */	
	private $name;
	
    /**
     * @ORM\Column(type="string", length=32)
     */		
	private $value;
	
    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * Many Colors have One Template.
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="colors")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
	private $template;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $isDefault;	

    /**
     * Set template
     *
     * @param \Application\Entity\Template $template
     * @return Menu
     */
    public function setTemplate(\Application\Entity\Template $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Application\Entity\Template 
     */
    public function getTemplate()
    {
        return $this->template;
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
	
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
	
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
	
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
	}	

}