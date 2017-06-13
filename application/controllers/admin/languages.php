<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/Language.php');

class LanguagesController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new Language();
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
    
	function listAction() {
        $entities = $this->entity->getAll();

		$data = array(
			'entities'	=> $entities,
			'pageTitle' => $GLOBALS['LANG']['languages'],
		);
		
		echo Cms::$twig->render('admin/languages/list.twig', $data);
	}
	
	function editAction() {		
		$entity = $this->entity->getById($this->getParam('id'))[0];

		$data = array(
			'entity'	=> $entity,
			'pageTitle' => $GLOBALS['LANG']['languages'] . ' :' . $entity['name'] . '[' . $entity['code'] . ']',
		);

		echo Cms::$twig->render('admin/languages/edit.twig', $data);
	}	
	
	function saveAction() {		
		$entity = isset($_POST['action']) ? $_POST : $entity;
		$entity['default'] = isset($_POST['default']) ? $_POST['default'] : 0;
		$entity['active'] = isset($_POST['active']) ? $_POST['active'] : 0;		
		
		if ($entity['default']) {
			$entity['active_front'] = 1;
		} else {
			$entity['active_front'] = isset($_POST['active_front']) ? $_POST['active_front'] : 0;
		}

		$this->processConsistency($entity);
		
		if (!$this->validate($entity)) {
			redirect(URL . '/admin/languages.html');
		}
		
		if ($this->entity->updateById($this->getParam('id'), $entity)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
			redirect(URL . '/admin/languages.html');	
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
			$this->editAction();
		}		
	}
	
	protected function processConsistency($entity) {
		$entityBeforeChange = $this->entity->getById($entity['id'])[0];
		
		if (!$entityBeforeChange['default'] && $entity['default']) {
			$entity['active'] = 1;
			$this->entity->clearDefault($entity['id'], $entity);
		}
		
	}
	
	protected function validate($entity) {		

		if (!$entity['active']) {						
			$entityBeforeChange = $this->entity->getById($entity['id'])[0];
			
			if (!$this->entity->isDefaultLanguageSet($entity['id'])) {

				if ($entityBeforeChange['active']) {
					Cms::getFlashBag()->add('error', $GLOBALS['LANG']['default_language_must_be_active']);
					return false;
				}
			}

			if ($this->entity->isActiveLanguageSet($entity['id'])) {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['active_language_not_set']);
				return false;
			}
		}
	
		return true;
	}
	
}

$controller = new LanguagesController();
$controller->init($params);