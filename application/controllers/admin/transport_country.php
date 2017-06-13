<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('transport'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCountry.php');

class TransportCountryController {

	private $tpl;
	private $country;
	private $params;
	private $module;
	private $access;
	private $_historyType;

	public function __construct() {
		$this->country = new TransportCountryModel();
		$this->_historyType = 'tk';
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

	public function addAction() {
		if (!$_POST) {
			$this->addForm();
		} else {
			if ($id = $this->country->set($_POST)) {
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
			if(!$entity = $this->country->getById($this->getParam('id'))[0]) {
				error_404();
			}
			if ($this->country->updateById($entity['id'], $_POST)) {
				// setHistory($this->_historyType, 74, $aItem['id']);
				$params['info'] = $GLOBALS['LANG']['info_edit'];
				$this->listAction($params);
			} else {
				$params['error'] = $GLOBALS['LANG']['error_edit'];
				$this->editForm($entity['id'], $params);
			}
		}
	}

	function addForm($params = []) {
		$entity = array("name" => '', "code" => '', "status_id" => 1);
		$entity = $_POST ? $_POST : $entity;

		$data = array(
			'entity' => $entity,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' .$GLOBALS['LANG']['module_transport_country'] . ' | ' . $GLOBALS['LANG']['btn_add']
		);

		echo Cms::$twig->render('admin/transport_country/add.twig', array_merge($data, $params));			
	}

	function editForm($id, $params = []) {
		if (!$entity = $this->country->getById($this->getParam('id'))[0]) {			
			error_404();
		}

		$data = array(
			'entity' => $entity,
			'module' => $this->module,
			'url_back' => CMS_URL . '/admin/' . $this->module . '.html',
			'pageTitle' => $GLOBALS['LANG']['module_transport'] . ' | ' .$GLOBALS['LANG']['module_transport_country'] . ' : ' . $entity['name']
				  . ' | ' . $GLOBALS['LANG']['btn_edit']
		);

		echo Cms::$twig->render('admin/transport_country/edit.twig', array_merge($data, $params));			
	}

	function historyAction() {
		global $oUser;

		if(!$aItem = $this->country->getById($this->getParam('id'))[0]) {			
			error_404();
		}

		if ($aItems = getHistory($this->_historyType, '', $aItem['id'])) {
			$aUser = $oUser->load_admin();
			$aUser[] = array("id" => 9998, "login" => 'API');
			$aUser[] = array("id" => 9999, "login" => 'CRON');
			Cms::$tpl->assign('aItems', $aItems);
			Cms::$tpl->assign('aUser', $aUser);
		}
		Cms::$tpl->assign('url_back', CMS_URL . '/admin/' . $this->_module . '.html');
		Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['module_transport'] . ' | ' .$GLOBALS['LANG']['module_transport_country'] . ' : ' . $aItem['name']
				  . ' | ' . $GLOBALS['LANG']['btn_history']);
		Cms::$tpl->showPage('other/history.tpl');
	}

	function listAction($params = []) {
		if (!$entities = $this->country->getAll()) {
			$entities = array();
		}

		$data = array(
			'entities' => $entities,
			'module' => $this->module,
			'url_add' => $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false,
			'pageTitle' => $GLOBALS['LANG']['available_countries'] . ' | ' . $GLOBALS['LANG']['module_transport_country']
		);

		echo Cms::$twig->render('admin/transport_country/list.twig', array_merge($data, $params));		
	}
}

$oTransportCountryController = new TransportCountryController();
$oTransportCountryController->init($params);
