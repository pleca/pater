<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('transport'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCourier.php');
require_once(MODEL_DIR . '/transportType.php');
require_once(MODEL_DIR . '/transportRegion.php');
require_once(MODEL_DIR . '/transportService.php');
require_once(MODEL_DIR . '/transportServiceOption.php');
require_once(ENTITY_DIR . '/Tax.php');

class TransportServiceOptionController {

	private $tpl;
	private $courier;
	private $type;
	private $region;
	private $service;
	private $serviceOption;
	private $tax;
	private $params;
	private $module;
	private $access;
	private $historyType;

	public function __construct() {

		$this->courier = new TransportCourierModel();
		$this->type = new TransportTypeModel();
		$this->region = new TransportRegionModel();
		$this->service = new TransportServiceModel();
		$this->serviceOption = new TransportServiceOptionModel();
		$this->historyType = 'to';
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
					$this->params['service'] = $value;
					break;
				case 7:
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
			if (!$service = $this->service->getById($this->getParam('service'))[0]) {
				error_404();
			}
			$_POST['region_id'] = $region['id'];
			$_POST['service_id'] = $service['id'];
			if ($id = $this->serviceOption->set($_POST)) {
				// setHistory($this->_historyType, 73, $id);
				$params['info'] = $GLOBALS['LANG']['info_add'];
				$this->listAction($params);
			} else {
				$params['error'] = $GLOBALS['LANG']['error_add'];
				$this->addForm($params);
			}
		}
	}
	
	public function editAction() {
		if (!$_POST) {
			$this->editForm($this->getParam('id'));
		} else {
			if (!$aItem = $this->serviceOption->getById($this->getParam('id'))[0]) {
				error_404();
			}
			$_POST['price'] = str_replace(',', '.', $_POST['price']);
			if ($this->serviceOption->updateById($aItem['id'], $_POST)) {
				// setHistory($this->_historyType, 74, $aItem['id']);
				$params['info'] = $GLOBALS['LANG']['info_edit'];
				$this->listAction($params);
			} else {
				$params['error'] = $GLOBALS['LANG']['error_edit'];
				$this->editForm($aItem['id'], $params);
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
		if (!$service = $this->service->getById($this->getParam('service'))[0]) {
			error_404();
		}
		if (!$aItem = $this->serviceOption->getById($this->getParam('id'))[0]) {
			error_404();
		}
		if ($this->serviceOption->deleteById($aItem['id'])) {
			// setHistory($this->_historyType, 75, $aItem['id']);
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
		
		if (!$service = $this->service->getById($this->getParam('service'))[0]) {
			error_404();
		}
		
		$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
		$taxes = $taxRepository->findBy([],['position' => 'ASC']);
	
		$entity = array("weight_from" => '', "weight_to" => '', "price" => '', "tax_id" => 0, "delivery_time" => '');
		$entity = $_POST ? $_POST : $entity;
		
		$data = array(
			'entity' => $entity,
			'courier' => $courier,
			'taxes' => $taxes,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '/list/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '/' . $service['id'] . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service'] . ' : ' . $service['name'] . ' | ' . $GLOBALS['LANG']['transport_option']. ' | ' . $GLOBALS['LANG']['btn_add']
		);

		echo Cms::$twig->render('admin/transport_service_option/add.twig', array_merge($data, $params));				
	}
	
	function editForm($id, $params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}
		
		if (!$region = $this->region->getById($this->getParam('region'))[0]) {
			error_404();
		}
		
		if (!$service = $this->service->getById($this->getParam('service'))[0]) {
			error_404();
		}
		
		if (!$entity = $this->serviceOption->getById($this->getParam('id'))[0]) {
			error_404();
		}
		
		$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
		$taxes = $taxRepository->findBy([],['position' => 'ASC']);		

		$data = array(
			'entity' => $entity,
			'courier' => $courier,
			'taxes' => $taxes,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '/list/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '/' . $service['id'] . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service'] . ' : ' . $service['name'] . ' | ' . $GLOBALS['LANG']['transport_option'] . ' : ' . $entity['id']
				  . ' | ' . $GLOBALS['LANG']['btn_edit']
		);

		echo Cms::$twig->render('admin/transport_service_option/edit.twig', array_merge($data, $params));			
		
		
	}
	
	function historyAction() {
		global $oUser;
		
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}		
		if (!$region = $this->region->getById($this->getParam('region'))[0]) {
			error_404();
		}
		if (!$service = $this->service->getById($this->getParam('service'))[0]) {
			error_404();
		}
		if (!$aItem = $this->serviceOption->getById($this->getParam('id'))[0]) {
			error_404();
		}		
		if ($entities = getHistory($this->_historyType, '', $aItem['id'])) {
			$aUser = $oUser->load_admin();
			$aUser[] = array("id" => 9998, "login" => 'API');
			$aUser[] = array("id" => 9999, "login" => 'CRON');
			Cms::$tpl->assign('aItems', $entities);
			Cms::$tpl->assign('aUser', $aUser);
		}
		Cms::$tpl->assign('url_back', CMS_URL . '/admin/' . $this->_module . '/list/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '/' . $service['id'] . '.html');
		Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service'] . ' : ' . $service['name'] . ' | ' . $GLOBALS['LANG']['transport_option'] . ' : ' . $aItem['id']
				  . ' | ' . $GLOBALS['LANG']['btn_history']);
		Cms::$tpl->showPage('other/history.tpl');
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
		
		if (!$service = $this->service->getById($this->getParam('service'))[0]) {
			error_404();
		}
		
		if (!$entities = $this->serviceOption->getAllByRegionIdServiceId($region['id'], $service['id'])) {
			$entities = array();
		}
		
		$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
		$taxes = $taxRepository->findBy([],['position' => 'ASC']);			

		foreach ($entities as $k => $v) {	
			foreach ($taxes as $tax) {
				if ($v['tax_id'] == $tax->getId()) {
					$v['tax'] = $tax->getValue();
				} 
			}			

			$entities[$k]['price_gross'] = formatPrice($entities[$k]['price'], $v['tax']);
			$entities[$k]['price_net'] = formatPrice($v['price']);
		}		

		$data = array(
			'entities' => $entities,
			'courier' => $courier,
			'type' => $type,
			'taxes' => $taxes,
			'url_back' => CMS_URL . '/admin/transport_region_service/list/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '.html',
			'module' => $this->module,
			'url_add' => $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add/' . $courier['id'] . '/' . $type['id'] . '/' . $region['id'] . '/' . $service['id'] . '.html' : false,
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $region['name']
				  . ' | ' . $GLOBALS['LANG']['transport_service'] . ' : ' . $service['name'] . ' | ' . $GLOBALS['LANG']['transport_option']
		);

		echo Cms::$twig->render('admin/transport_service_option/list.twig', array_merge($data, $params));		
	}
}

$oTransportServiceOptionController = new TransportServiceOptionController();
$oTransportServiceOptionController->init($params);
