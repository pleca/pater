<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/Module.php');

class ModuleController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new Module();
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
        
		foreach ($params as $key => $value) {
			switch ($key) {
				case 0:
				// no break
				case 1:
					$this->params['controller'] = $value;
					break;
				case 2:
					$this->params['action'] = $value;
					break;
				case 3:
					$this->params['id'] = $value;
					break;
				default:
					throw new \Exception('Unknown params.');
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
		
        if (isset($_POST['action']) AND $_POST['action'] == 'saveModules') {
			if ($this->entity->edit($_POST)) {
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_config']);
				redirect_301(URL . '/admin/module.html');
			} else {
				$params['error'] = $GLOBALS['LANG']['error_change'];
			}
        }
                
        $entities = $this->entity->getAll(['type' => 0]);

		$data = array(
			'entities'	=> $entities,
			'pageTitle' => $GLOBALS['LANG']['modules'],
			'url_add'	=> $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false			
		);
		
		echo Cms::$twig->render('admin/module/list.twig', array_merge($data, $params));
	}
}

$controller = new ModuleController();
$controller->init($params);