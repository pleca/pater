<?php

/* 2015-10-14 | 4me.CMS 15.3 */

//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/ApiShopLog.php');

class ApiShopLogController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new ApiShopLog();
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

		if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
			$_GET['page'] = 1; 	
		}

		$limit = 25;
		
		$params = array(
			'limitStart' => ($_GET['page'] - 1) * $limit,
			'limit' => $limit,
		);
		
        $entities = $this->entity->getAll($params);
		$pages = $this->entity->getPages($limit);

		$data = array(
			'entities'	=> $entities,
			'pageTitle' => 'Api Shop',
			'page' => $_GET['page'],
			'pages' => $pages,
			'interval' => $limit * ($_GET['page'] - 1)
		);

		echo Cms::$twig->render('admin/api_shop_log/list.twig', $data);
	}
	
	
}

$controller = new ApiShopLogController();
$controller->init($params);