<?php

namespace Application\Classes;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Cms;

trait ValidatorTrait {
	function validate() {
		//for now only admin locale
		$locale = \Cms::$session->get('locale_admin') ? \Cms::$session->get('locale_admin') : \Cms::$defaultLocale;
		
		$translator = new Translator($locale); 
		$translator->addLoader('yaml', new YamlFileLoader());
		$translator->addResource('yaml', __DIR__ . '/../../application/translations/validators.' . $locale . '.yml', $locale, 'validation');

		$validator = Validation::createValidatorBuilder()
			->enableAnnotationMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validation')				
			->getValidator();

		$errors = $validator->validate($this);	
		
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				Cms::getFlashBag()->add('error', $error->getMessage());
			}		
			return false;
		}
		
		return true;		
	}
}
