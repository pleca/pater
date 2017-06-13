<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('transport'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCourier.php');
require_once(MODEL_DIR . '/transportType.php');
require_once(MODEL_DIR . '/transportRegion.php');

class TransportRegionController {

	private $tpl;
	private $courier;
	private $type;
	private $region;
	private $params;
	private $module;
	private $access;
	private $historyType;

	public function __construct() {
		$this->courier = new TransportCourierModel();
		$this->type = new TransportTypeModel();
		$this->region = new TransportRegionModel();
		$this->historyType = 'tr';
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
			$_POST['courier_id'] = $courier['id'];
			$_POST['type_id'] = $type['id'];

			if ($id = $this->region->set($_POST)) {
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
			if (!$aItem = $this->region->getById($this->getParam('id'))[0]) {
				error_404();
			}
			if ($this->region->updateById($aItem['id'], $_POST)) {
				// setHistory($this->_historyType, 74, $aItem['id']);
				$params['info'] = $GLOBALS['LANG']['info_edit'];
				$this->listAction($params);
			} else {
				$params['error'] = $GLOBALS['LANG']['error_edit'];
				$this->editForm($aItem['id'], $params);
			}
		}
	}

	function addForm($params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}
		
		$entity = array("name" => '', "code" => '', "status_id" => 1);
		$entity = $_POST ? $_POST : $entity;
		
		$data = array(
			'entity' => $entity,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '/list/' . $courier['id'] . '/' . $type['id'] . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' | ' . $GLOBALS['LANG']['btn_add']
		);

		echo Cms::$twig->render('admin/transport_region/add.twig', array_merge($data, $params));		
	}

	function editForm($id, $params = []) {
		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}	
		
		if (!$entity = $this->region->getById($this->getParam('id'))[0]) {
			error_404();
		}

		$data = array(
			'entity' => $entity,
			'url_back' => CMS_URL . '/admin/' . $this->module . '/list/' . $courier['id'] . '/' . $type['id'] . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $aItem['name']
				  . ' | ' . $GLOBALS['LANG']['btn_edit']
		);

		echo Cms::$twig->render('admin/transport_region/edit.twig', array_merge($data, $params));		
	}

	function historyAction() {
		global $oUser;

		if (!$courier = $this->courier->getById($this->getParam('courier'))[0]) {
			error_404();
		}
		if (!$type = $this->type->getById($this->getParam('type'))[0]) {
			error_404();
		}		
		if (!$aItem = $this->region->getById($this->getParam('id'))[0]) {
			error_404();
		}
		if ($entities = getHistory($this->_historyType, '', $aItem['id'])) {
			$aUser = $oUser->load_admin();
			$aUser[] = array("id" => 9998, "login" => 'API');
			$aUser[] = array("id" => 9999, "login" => 'CRON');
			Cms::$tpl->assign('aItems', $entities);
			Cms::$tpl->assign('aUser', $aUser);
		}
		Cms::$tpl->assign('url_back', CMS_URL . '/admin/' . $this->_module . '/list/' . $courier['id'] . '/' . $type['id'] . '.html');
		Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region'] . ' : ' . $aItem['name']
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
		
		if (!$entities = $this->region->getAllByCourierIdTypeId($courier['id'], $type['id'])) {
			$entities = array();
		}
		
		$data = array(
			'module' => $this->module,
			'entities' => $entities,
			'url_back' => CMS_URL . '/admin/transport_type/list/' . $courier['id'] . '/' . $type['id'] . '.html',
			'url_add' => $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add/' . $courier['id'] . '/' . $type['id'] . '.html' : false,
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' . $GLOBALS['LANG']['transport_courier'] . ' : ' . $courier['name']
				  . ' | ' . $GLOBALS['LANG']['transport_type'] . ' : ' . $type['name'] . ' | ' . $GLOBALS['LANG']['transport_region']
		);

		echo Cms::$twig->render('admin/transport_region/list.twig', array_merge($data, $params));			
	}
}

$oTransportRegionController = new TransportRegionController();
$oTransportRegionController->init($params);
