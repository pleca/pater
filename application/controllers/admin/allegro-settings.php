<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/AllegroSetting.php');

class AllegroSettingsController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new AllegroSetting();
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
			'pageTitle' => $GLOBALS['LANG']['general_settings'],
		);
		
		echo Cms::$twig->render('admin/allegro_settings/list.twig', $data);
	}

	function saveAction() {		
		$post = maddslashes($_POST);
		
		if (!$post) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
			$this->listAction();
		}
		
		unset($post['action']);
		foreach ($post as $name => $value) {
			
			$item = array(
				'name' => $name,
				'value' => $value
			);

			$where = $this->entity->where(['name' => $name]);			
			$this->entity->update($this->entity->table, $where, $item);		
		}
		
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		redirect(URL . '/admin/allegro-settings.html');		
	}

	
}

$controller = new AllegroSettingsController();
$controller->init($params);