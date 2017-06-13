<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/SalesRepresentative.php');

class SalesRepresentativeController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new SalesRepresentative();
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
		$params = [];
  
        $entities = $this->entity->getAll();

		$data = array(
			'entities'	=> $entities,
			'pageTitle' => $GLOBALS['LANG']['ml_sales_representatives'],
			'url_add'	=> $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false			
		);
		
		echo Cms::$twig->render('admin/sales_representative/list.twig', array_merge($data, $params));
	}
	
	function addAction() {
		
		if (!$this->validate($_POST)) {
			$this->addFormAction();
			die;
		}
		
		if ($this->entity->set($_POST)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
		}
		
		redirect_301(URL . '/admin/sales-representative.html');
	}
	
	function editAction() {		
		$entity = $this->entity->getById($this->getParam('id'))[0];

		$data = array(
			'entity'	=> $entity,
			'pageTitle' => $GLOBALS['LANG']['sales_representative_add'],
		);

		echo Cms::$twig->render('admin/sales_representative/edit.twig', $data);
	}	
	
	function deleteAction() {		

		if (!$this->entity->isAssignment($this->getParam('id'))) {				
			if ($this->entity->deleteById($this->getParam('id'))) {
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			} else {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
			}
		}		
		
		redirect_301(URL . '/admin/sales-representative.html');
	}	
	
	function saveAction() {		
		$entity = isset($_POST['action']) ? $_POST : $entity;

		if ($this->entity->updateById($this->getParam('id'), $entity)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
			redirect_301(URL . '/admin/sales-representative.html');	
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
			$this->editAction();
		}		
	}	
	
	function addFormAction() {
		$entity = array("first_name" => '', "last_name" => '', "email" => '');
		$entity = isset($_POST['action']) ? $_POST : $entity;

		$data = array(
			'entity'	=> $entity,
			'pageTitle' => $GLOBALS['LANG']['sales_representative_add'],
		);

		echo Cms::$twig->render('admin/sales_representative/add.twig', $data);		
	}

	protected function validate($data) {
		$isValid = true;
		
		if (!$data['first_name']) {
			Cms::getFlashBag()->add('error', 'Nie podano imienia!');
			$isValid = false;
		}
		
		if (!$data['last_name']) {
			Cms::getFlashBag()->add('error', 'Nie podano nazwiska!');
			$isValid = false;
		}
		
		if (!$data['email']) {
			Cms::getFlashBag()->add('error', 'Nie podano email!');
			$isValid = false;
		}
		
		if (!checkEmail($data['email'])) {
			Cms::getFlashBag()->add('error', 'Błędny email');
			$isValid = false;
		}
		
		return $isValid;
	}	
}

$controller = new SalesRepresentativeController();
$controller->init($params);