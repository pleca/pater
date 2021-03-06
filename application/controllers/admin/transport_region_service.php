<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('transport'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCourier.php');
require_once(MODEL_DIR . '/transportType.php');
require_once(MODEL_DIR . '/transportRegion.php');
require_once(MODEL_DIR . '/transportRegionService.php');
require_once(MODEL_DIR . '/transportService.php');

class TransportService2RegionController {

	private $tpl;
	private $courier;
	private $type;
	private $region;
	private $regionService;
	private $service;	
	private $params;
	private $module;
	private $access;
	private $historyType;

	public function __construct() {
		$this->courier = new TransportCourierModel();
		$this->type = new TransportTypeModel();
		$this->region = new TransportRegionModel();
		$this->regionService = new TransportRegionServiceModel();
		$this->service = new TransportServiceModel();		
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
					$this->params['type'] = $value;
					break;
				case 5:
					$this->params['region'] = $value;
					break;
				case 6:
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

	public function addAction() {
		if (!$_POST) {
			$this->addForm();
		} else {
			if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
				error_404();
			}
			if (!$type = $this->type->getById($this->getParam('type'))[0]) {
				error_404();
			}
			if (!$region = $this->region->getById($this->getParam('region'))[0]) {
				error_404();
			}			
			if(!$this->regionService->getByRegionIdServiceId($region['id'], $_POST['service_id'])) {
				$_POST['region_id'] = $region['id'];
				if ($id = $this->regionService->set($_POST)) {
					$params['info'] = $GLOBALS['LANG']['info_add'];
					$this->listAction($params);
				} else {
					$params['error'] = $GLOBALS['LANG']['error_add'];
					$this->addForm($params);
				}
			} else {
				$params['error'] = $GLOBALS['LANG']['error_isexist'];
				$this->addForm($params);
			}
		}
	}

	public function deleteAction() {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}
		if (!$region = $this->region->getById($this->getParam('region'))[0]) {
			error_404();
		}
		if (!$aItem = $this->regionService->getById($this->getParam('id'))[0]) {
			error_404();
		}		
		if ($this->regionService->deleteById($aItem['id'])) {
			$params['info'] = $GLOBALS['LANG']['info_delete'];
		} else {
			$params['error'] = $GLOBALS['LANG']['error_delete'];
		}
		$this->listAction($params);
	}

	function addForm($params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}
		if (!$region = $this->region->getById($this->getParam('region'))[0]) {
			error_404();
		}		
		$services = $this->service->getAllByCourierId($courier['id']);
		$entity = array("service_id" => 0);
		$entity = $_POST ? $_POST : $entity;
		
		$data = array(
			'entity' => $entity,
			'services' => $services,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '/list/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service'] . ' | ' . $GLOBALS['LANG']['btn_add']
		);

		echo Cms::$twig->render('admin/transport_region_service/add.twig', array_merge($data, $params));		
	}

	function listAction($params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}
		
		if (!$region = $this->region->getById($this->getParam('region'))[0]) {
			error_404();
		}
		
		if (!$entities = $this->regionService->getAllByRegionId($region['id'])) {
			$entities = array();
		}
		
		$services = $this->service->getAllByCourierId($courier['id']);	
		
		$data = array(
			'entities' => $entities,
			'courier' => $courier,
			'type' => $type,
			'services' => $services,
			'url_back' => CMS_URL . '/admin/transport_region/list/' . $courier['id'] . '/' . $type['id'] . '.html',
			'module' => $this->module,
			'url_add' => $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '.html' : false,
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service']
		);

		echo Cms::$twig->render('admin/transport_region_service/list.twig', array_merge($data, $params));			
	}
}

$oTransportService2RegionController = new TransportService2RegionController();
$oTransportService2RegionController->init($params);