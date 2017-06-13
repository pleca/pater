<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('transport'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCourier.php');
require_once(MODEL_DIR . '/transportType.php');

class TransportTypeController {

	private $tpl;
	private $courier;
	private $type;	
	private $params;
	private $module;
	private $access;
	private $historyType;

	public function __construct() {
		$this->courier = new TransportCourierModel();
		$this->type = new TransportTypeModel();
		$this->historyType = '';
	}

	public function init($params = '') {
		$this->setParams($params);
		$this->setAccess();
		$this->setModule();

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
					$this->params['courier'] = $value;
					break;
				case 4:
					$this->params['id'] = $value;
					break;
				default:
					throw new \Exception('Unknown params.');
					break;
			}
		}
	}

	public function setAccess() {
        $this->access = in_array($_SESSION[USER_CODE]['level'], [1,2]) ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}

	function listAction($params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}			
		if (!$entities = $this->type->getAll()) {			
			$entities = array();
		}

		$data = array(
			'courier' => $courier,
			'entities' => $entities,
			'url_back' => CMS_URL . '/admin/transport_courier.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type']
		);

		echo Cms::$twig->render('admin/transport_type/list.twig', array_merge($data, $params));			
	}
}

$oTransportTypeController = new TransportTypeController();
$oTransportTypeController->init($params);
