<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('payment'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/PaymentModel.php');

class PaymentController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
		$this->entity = new PaymentModel();
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

	public function editAction() {
		if (!$_POST) {
			$this->editForm($this->getParam('id'));
		} else {
			if (!$item = $this->entity->getById($this->getParam('id'))[0]) {
				error_404();
			}
            
			if ($this->entity->updateById($item['id'], $_POST)) {
				$params['info'] = $GLOBALS['LANG']['info_edit'];
				$this->listAction($params);
			} else {
				$params['error'] = $GLOBALS['LANG']['error_edit'];
				$this->editForm($item['id'], $params);
			}
		}
	}

	function editForm($id, $params = []) {		
		if (!$entity = $this->entity->getById($this->getParam('id'))[0]) {
			error_404();
		}
        
		$data = array(
			'entity' => $entity,
			'url_back' => CMS_URL . '/admin/' . $this->module . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_payment']
		);

		echo Cms::$twig->render('admin/payment/edit.twig', array_merge($data, $params));		
	}
    
	function listAction($params = []) {        
        $entities = $this->entity->getAll();

		$data = array(
			'entities' => $entities,
			'module' => $this->module,
			'url_add' => $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false,
			'pageTitle' => $GLOBALS['LANG']['payment_types']
		);

		echo Cms::$twig->render('admin/payment/list.twig', array_merge($data, $params));		
	}
}

$controller = new PaymentController();
$controller->init($params);
