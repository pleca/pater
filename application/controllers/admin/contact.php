<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('contact'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/Contact.php');

class ContactController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
        $this->entity = new Contact();
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
			}
		}       
	}

	public function setAccess() {
		$this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}
    
    public function addAction() {
		if ($this->entity->add($_POST)) {
			$params['info'] = $GLOBALS['LANG']['info_add'];
		} else {
			$params['error'] = $GLOBALS['LANG']['error_add'];
		}
		
        $this->listAction($params);
    }
    
    public function editAction() {
        $this->listAction();
    }
    
    public function saveAction() {
		if ($this->entity->edit($_POST)) {
			$params['info'] = $GLOBALS['LANG']['info_config'];
		} else {
			$params['error'] = $GLOBALS['LANG']['error_change'];
		}
		
        $this->listAction($params);
    }
    public function deleteAction() {
		if ($this->entity->deleteOld($_GET['id'])) {
			$params['info'] = $GLOBALS['LANG']['info_delete'];
		} else {
			$params['error'] = $GLOBALS['LANG']['error_delete'];
		}
		
        $this->listAction($params);
    }

	function listAction($params = []) {        
        $entities = $this->entity->getAll();

		$data = array(
			'entities'	=> $entities,
			'pageTitle'	=>	$GLOBALS['LANG']['module_contact']
		);

		echo Cms::$twig->render('admin/contact/list.twig', array_merge($data, $params));			
	}
    
}

$controller = new ContactController();
$controller->init($params);
