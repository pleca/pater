<?php

/* 2015-10-14 | 4me.CMS 15.3 */
require_once(ENTITY_DIR . '/SiteGlobal.php');
use Application\Entity\SiteGlobalTranslation;
//use Application\Entity\Template;
//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

//check_level(1);

class TranslationsController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
//		$this->entity = new Template();
	}

	public function init($params = '') {
		$this->setParams($params);
		$this->setAccess();
		$this->setModule();
        $this->run();
	}
    
    public function run() {
		$action = $this->getParam('action') ? $this->getParam('action') : 'list';
		$action .= 'Action';
		$this->$action();        
    }

	public function __call($method = '', $args = '') {
		error_404();
	}

	public function getParam($name) {
		return isset($this->params[$name]) ? $this->params[$name] : 0;
	}

	public function setParams($params = []) {        
        $params = array_merge($params, $_POST);
        $params = array_merge($params, $_GET);

		foreach ($params as $key => $value) {
			switch ($key) {
                case (string) 0:                
                case (string) 1:
					$this->params['controller'] = $value;
					break;
				case 'action':
					$this->params['action'] = $value;
					break;
				case 'id':
					$this->params['id'] = $value;
					break;
			}
		}
		
	}

	public function setAccess() {
		$this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}
  
	function editAction() {
		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\SiteGlobal');
		$entity = $entityRepository->findOneBy(['name' => $_REQUEST['name']]);

		if ($_POST) {
			unset($_POST['action']);
			unset($_POST['name']);			
		
			foreach ($_POST as $locale => $trans) {
				
				if (!$entity->hasTranslation($locale)) {
					$translation = new SiteGlobalTranslation();					
					$translation->setValue($trans['value']);
					$translation->setLocale($locale);
					$translation->setSiteGlobal($entity);
					
					$entity->addTranslation($translation);
					CMS::$entityManager->persist($entity);
				} else {
					$transRepository = CMS::$entityManager->getRepository('Application\Entity\SiteGlobalTranslation');
					$translation = $transRepository->findOneBy(['locale' => $locale, 'siteGlobal' => $entity->getId()]);
					$translation->setValue($trans['value']);
					$translation->setLocale($locale);	
					CMS::$entityManager->persist($translation);
				}
			}
			
			CMS::$entityManager->flush();
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
			redirect(URL . '/admin/translations');
		}
		
		$data = array(
			'entity'	=>	$entity,
		);

		echo Cms::$twig->render('admin/translations/edit.twig', $data);
	}
	
	function saveAction() {	
//		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Color');
//		
//		foreach ($_POST['colors'] as $id => $value) {
//			$color = $entityRepository->find($id);
//			$color->setValue($value);
//			CMS::$entityManager->persist($color);
//		}
//		
//		CMS::$entityManager->flush();
//		
//		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//		redirect(URL . '/admin/templates?template_id=' . $_POST['template_id'] . '&action=colors');
	}

	
//	function editAction() {
//		if (!$_SESSION[USER_CODE]['available_actions']['template_change']) {
//			redirect(URL . '/admin/templates');
//		}
//				
//		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
//		$entities = $entityRepository->findAll();
//		
//		foreach ($entities as $entity) {
//			$entity->setActive(false);
//		}
//		
//		CMS::$entityManager->flush();		
//				
//		$entity =  $entityRepository->find($_REQUEST['id']);
//		$entity->setActive(true);
//		CMS::$entityManager->persist($entity);
//		CMS::$entityManager->flush($entity);
//		
//		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//		redirect(URL . '/admin/templates');		
//	}	
	
	function listAction() {
		$siteGlobalRepository = CMS::$entityManager->getRepository('Application\Entity\SiteGlobal');
		$entities = $siteGlobalRepository->findAll();

		$data = array(
			'entities'	=> $entities,
			'actionTemplateChange'	=> $_SESSION[USER_CODE]['available_actions']['template_change'],
			'pageTitle' => 'Templates',
		);

		echo Cms::$twig->render('admin/translations/list.twig', $data);
	}
	
	
}

$controller = new TranslationsController();
$controller->init($params);