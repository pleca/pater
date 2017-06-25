<?php
//ALTER TABLE taxes CHANGE position position SMALLINT NOT NULL;

namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;

/**
 * @ORM\Entity 
 * @ORM\Table(name="taxes")
 */
class Tax
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue 
	 */
    protected $id;
	
    /**
     * @Assert\NotBlank(message = "tax.value.not_blank")
	 * @ORM\Column(type="decimal", precision=5, scale=2)
     */		
    protected $value;
	
	/** 
	 * @ORM\Column(type="smallint") 
	 */
    protected $position;

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
	
    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }
	
	public function getMaxPosition() {
		$q = "SELECT MAX(`position`) FROM `taxes`";
		return Cms::$db->max($q);
	}	
	
	
//	public function validate() {
//
////		$translator = new Translator('pl'); 
////		$translator->addLoader('xlf', new XliffFileLoader());
////		$translator->addResource('xlf', __DIR__ . '/../../vendor/symfony/validator/Resources/translations/validators.pl.xlf', 'pl','validation');
//
//		
//		$translator = new Translator('en'); 
//		$translator->addLoader('xlf', new XliffFileLoader());
//		$translator->addResource('xlf', __DIR__ . '/../../vendor/symfony/validator/Resources/translations/validators.en.xlf', 'en','validation');
//
////		$translator->addLoader('yaml', new YamlFileLoader());
////		$translator->addResource('yaml', 'path/to/messages.fr.yml', 'fr_FR');
//
//		$validator = Validation::createValidatorBuilder()
//			->enableAnnotationMapping()
//            ->setTranslator($translator)
//            ->setTranslationDomain('validation')				
//			->getValidator();
//
//		$errors = $validator->validate($this);	
//		
//		if (count($errors) > 0) {
//			foreach ($errors as $error) {
//				Cms::getFlashBag()->add('error', $error->getMessage());
////				Cms::getFlashBag()->add('error', $GLOBALS['LANG'][$error->getMessage()]);
//			}		
//			return false;
//		}
//		
//		return true;
//	}
}