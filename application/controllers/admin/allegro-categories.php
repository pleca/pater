<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/AllegroCategory.php');

class AllegroCategoriesController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new AllegroCategory();
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
			'pageTitle' => $GLOBALS['LANG']['categories'] . ' Allegro',
		);
		
		echo Cms::$twig->render('admin/allegro_categories/list.twig', $data);
	}
	
	function addFormAction($post = []) {		
		
		$data = array(
			'entity' => $post,
			'pageTitle' => $GLOBALS['LANG']['categories'] . ' Allegro',
		);
		
		echo Cms::$twig->render('admin/allegro_categories/add.twig', $data);
	}	
	
	function addAction() {		
		$entity = isset($_POST) ? $_POST : [];
		
		$data = array(
			'entity'	=> $entity,
			'pageTitle' => $GLOBALS['LANG']['categories'] . ' Allegro',
		);
		
		if ($_POST) {
			if ($this->entity->add($_POST)) {
				$this->listAction();
			} else {				
				$this->addFormAction($_POST);	
			}			
		}	
	}
	
	function editAction() {		
		$entity = $this->entity->getById($this->getParam('id'))[0];

		$data = array(
			'entity'	=> $entity,
			'pageTitle' => $GLOBALS['LANG']['categories'] . ' Allegro',
		);

		echo Cms::$twig->render('admin/allegro_categories/edit.twig', $data);
	}	
	
	function saveAction() {		
		$entity = isset($_POST['action']) ? $_POST : [];

		if (!$this->validate($entity)) {
            $this->editAction();
            return false;
		}
        
		if ($this->entity->updateById($this->getParam('id'), $entity)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
			redirect(URL . '/admin/allegro-categories.html');	
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
			$this->editAction();
		}		
	}
    
	function deleteAction() {
        $this->entity->deleteById($this->getParam('id'));
        redirect(URL . '/admin/allegro-categories.html');        
    }	    
	
	
	protected function validate($entity) {		

		$name = isset($entity['name']) ? $entity['name'] : null;
		$category_id = isset($entity['category_id']) ? $entity['category_id'] : null;

		if (!$name || !$category_id) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_edit']);
			return false;
		}
	
		return true;
	}
	
}

$controller = new AllegroCategoriesController();
$controller->init($params);