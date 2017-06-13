<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('news'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/NewsModel.php');

class News {

	private $news;
	private $params;
	private $module;

	public function __construct() {
		$this->news = new NewsModel();
	}

	public function init($params = '') {
		$this->setParams($params);
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

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}

	public function prepareData($data = '', $type = 'add') {
		if ($type == 'add') {
			$data['item']['date_add'] = date('Y-m-d');
		}
		$data['item']['active'] = isset($data['item']['active']) ? 1 : 0;
		$data['item']['galery_id'] = isset($data['item']['galery_id']) ? $data['item']['galery_id'] : 0;
		$data['item']['date_mod'] = date('Y-m-d');

		foreach ($data['desc'] as $k => $v) {
			$data['desc'][$k]['lang_id'] = $k; // ustawiamy ID jezyka
			$data['desc'][$k]['name'] = clearName($data['desc'][$k]['name']); // czyscimy nazwe z niewygodnych znakow
			$data['desc'][$k]['name_url'] = makeUrl($data['desc'][$k]['name']); // tworzymy slug
			if (empty($data['desc'][$k]['desc_short'])) {
				$data['desc'][$k]['desc_short'] = clearHtml($data['desc'][$k]['desc'], 200, '...');
			}
		}

		return $data;
	}

	// transakcja start, przygotuj transakcje
	// https://github.com/TargetHolding/Scratch4AllAsAService/blob/master/search/include/KLogger.php					
	// cze error hendling potrzebne???
	//rolback w try - 
	
	// ACTIVE na STATUS

	public function addAction() {
		if (!$_POST) {
			$this->addForm();
		} else {
			$aFields = $this->prepareData($_POST, 'add'); // przygotowanie danych	
			// transakcja
			$id = $this->news->set($aFields['item']); // zpais do pierwszej tabeli
			foreach (Cms::$langs as $k => $v) {
				$aFields['desc'][$k]['parent_id'] = $id; // ustawienie parent_id
				$this->news->setDesc($aFields['desc'][$k]); // zapis do drugiej tabeli, kazdy jezyk osobno
			}
            
            $params['info'] = $params['info'] = $GLOBALS['LANG']['info_add'];
			$this->listAction($params);

//			Cms::$tpl->setError($GLOBALS['LANG']['error_add']);
//			$this->addForm();
			// zdjecia
		}
	}

	public function editAction() {
		if (!$_POST) {
			$this->editForm($this->getParam('id'));
		} else {
			if (!$aItem = $this->news->getById($this->getParam('id'))[0]) { // sprawdzamy czy jest taki wpis
				error_404();
			}
			$aFields = $this->prepareData($_POST, 'edit');
			// transakcja
			$this->news->updateById($aItem['id'], $aFields['item']);
			foreach (Cms::$langs as $k => $v) {
				if ($desc = $this->news->getDescByParentIdLangId($aItem['id'], $k)[0]) { // sprawdzamy czy jest wpis w tabeli _desc
					unset($aFields['desc'][$k]['lang_id']); // usuwamy aby nie nadpisac
					$this->news->updateDescById($desc['id2'], $aFields['desc'][$k]); // uaktualniamy wpis w _desc dla kazdego jezyka
				} else {
					$aFields['desc'][$k]['parent_id'] = $aItem['id'];
					$this->news->setDesc($aFields['desc'][$k]); // zapis do tabeli _desc dla kazdego jezyka w petli
				}
			}

            $params['info'] = $GLOBALS['LANG']['info_edit'];
			$this->listAction($params);

//			Cms::$tpl->setError($GLOBALS['LANG']['error_edit']);
//			$this->editForm($this->getParam('id'));
			// zdjecia
		}
	}
	
	public function deleteAction() {
		if (!$aItem = $this->news->getById($this->getParam('id'))[0]) { // sprawdzamy czy jest taki wpis
			error_404();
		}

		// transakcja
		$this->news->deleteById($aItem['id']);
		foreach (Cms::$langs as $k => $v) {
			if ($desc = $this->news->getDescByParentIdLangId($aItem['id'], $k)[0]) {
				$this->news->deleteDescById($desc['id2']);
			}
		}

        $params['info'] = $GLOBALS['LANG']['info_delete'];
		$this->listAction($params);

//		Cms::$tpl->setError($GLOBALS['LANG']['error_delete']);
//		$this->editForm($this->getParam('id'));
		// zdjecia
	}

	public function addForm() {
		$entity['item'] = ["active" => 1, "gallery_id" => 0]; // domyslne dane dla pustego formularza		
		foreach (Cms::$langs as $k => $v) {
			$entity['desc'][$k] = ["name" => '', "desc" => '', "desc_short" => '', "tag1" => '', "tag2" => '', "tag3" => '',
				"lang_code" => $v['code'], "lang_name" => $v['name']];
		}
		$entity = $_POST ? $_POST : $entity;

        $data = array(
            'entity' => $entity,
            'module' => $this->module,
            'url_back'   => CMS_URL . '/admin/' . $this->module . '.html',
            'pageTitle' => $GLOBALS['LANG']['module_news'] . ' | ' . $GLOBALS['LANG']['add_section'],
        );

        echo Cms::$twig->render('admin/news/form.twig', $data);           
	}

	public function editForm() {
		if (!$entity['item'] = $this->news->getById($this->getParam('id'))[0]) {  // dane z tabeli podstawowej		
			error_404();
		}
		foreach (Cms::$langs as $k => $v) {
			$entity['desc'][$k] = $this->news->getDescByParentIdLangId($entity['item']['id'], $k)[0]; // dane z tabeli _desc
			$entity['desc'][$k]['lang_code'] = $v['code'];
			$entity['desc'][$k]['lang_name'] = $v['name'];
		}

        $data = array(
            'entity' => $entity,
            'module' => $this->module,
            'url_back'   => CMS_URL . '/admin/' . $this->module . '.html',
            'pageTitle' => $GLOBALS['LANG']['module_news'] . ' | ' . $GLOBALS['LANG']['edit'] . ' | ' . $entity['item']['id']
        );

        echo Cms::$twig->render('admin/news/form.twig', $data);         
	}

	public function listAction($params = []) {
		if (!$entities = $this->news->getAll()) {
			$entities = array();
		}
        
		foreach ($entities as &$v) {
			$desc = $this->news->getDescByParentIdLangId($v['id'])[0]; // brak jezyka = domyslny
			$v['name'] = $desc['name'];
			$v['desc_short'] = $desc['desc_short'];
			$v['url'] = URL . '/news/' . $desc['name_url'] . '.html';
		}
		// ??? tutaj zrobic funkcje w model news ktora pobierze dane z dwoch tabel
		// $aItems = $this->news->getAllNews(_ID);
		// STRONICOWANIE, PAGINACJA        
        
        $data = array(
            'entities' => $entities,
            'module' => $this->module,
            'url_add'   => CMS_URL . '/admin/' . $this->module . '/add.html',
            'pageTitle' => $GLOBALS['LANG']['module_news']
        );

        echo Cms::$twig->render('admin/news/list.twig', array_merge($data, $params));        
	}

}

$oNews = new News();
$oNews->init($params);






//
//if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
//	showAdd();
//} elseif (isset($_POST['action']) AND $_POST['action'] == 'addPublish') {
//	$oNewsAdmin->addAdmin($_POST, $_FILES, $aLangs);
//	showList();
//} elseif (isset($_POST['action']) AND $_POST['action'] == 'addContinue') {
//	$id = $oNewsAdmin->addAdmin($_POST, $_FILES, $aLangs);
//	showEdit($id);
//} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
//	showEdit($_GET['id']);
//} elseif (isset($_POST['action']) AND $_POST['action'] == 'savePublish') {
//	$oNewsAdmin->editAdmin($_POST, $_FILES, $aLangs);
//	showList();
//} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveContinue') {
//	$oNewsAdmin->editAdmin($_POST, $_FILES, $aLangs);
//	showEdit($_POST['id']);
//} elseif (isset($_GET['action']) AND $_GET['action'] == 'photo_delete') {
//	$oNewsAdmin->deletePhoto($_GET['id']);
//	showEdit($_GET['id']);
//} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
//	$oNewsAdmin->deleteAdmin($_GET['id']);
//	showList();
//} else {
//	showList();
//}
//
//function showAdd() {
//	global $oCore, $oNewsAdmin, Cms::$modules, $aLangs;
//
//	if (Cms::$modules['gallery'] == 1) {
//		require_once(CMS_DIR . '/application/models/gallery.php');
//		$oGallery = new Gallery();
//		$option_gallery = $oGallery->loadViews();
//		Cms::$tpl->assign('gallery', true);
//		Cms::$tpl->assign('option_gallery', $option_gallery);
//	}
//
//	$aItem = array("active" => '');
//	$aItem = isset($_POST['action']) ? $_POST : $aItem;
//	$aDesc = $oNewsAdmin->loadDescAdmin(0, $aLangs);
//
//	Cms::$tpl->assign('aItem', $aItem);
//	Cms::$tpl->assign('aDesc', $aDesc);
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['news_add']);
//	Cms::$tpl->assign('tinyMce', true);
//	Cms::$tpl->showPage('news/add.tpl');
//}
//
//function showEdit($id) {
//	global $oCore, $oNewsAdmin, Cms::$modules, $aLangs;
//
//	if (Cms::$modules['gallery'] == 1) {
//		require_once(CMS_DIR . '/application/models/gallery.php');
//		$oGallery = new Gallery();
//		$option_gallery = $oGallery->loadViews();
//		Cms::$tpl->assign('gallery', true);
//		Cms::$tpl->assign('option_gallery', $option_gallery);
//	}
//
//	$aItem = $oNewsAdmin->loadByIdAdmin($id);
//	$aDesc = $oNewsAdmin->loadDescAdmin($aItem['id'], $aLangs);
//	Cms::$tpl->assign('aItem', $aItem);
//	Cms::$tpl->assign('aDesc', $aDesc);
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['news_edit']);
//	Cms::$tpl->assign('tinyMce', true);
//	Cms::$tpl->showPage('news/edit.tpl');
//}
//
//function showList() {	
//	$oNewsAdmin = new NewsAdmin();
//
//	$limit = 25;
//	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
//		$_GET['page'] = 1;
//	}
//	$limitStart = ($_GET['page'] - 1) * $limit;
//	
//	$aItems = $oNewsAdmin->loadAdmin($limitStart, $limit);
//	$pages = $oNewsAdmin->getPagesAdmin($limit);
//
//	Cms::$tpl->assign('aItems', $aItems);
//	Cms::$tpl->assign('pages', $pages);
//	Cms::$tpl->assign('page', $_GET['page']);
//	Cms::$tpl->assign('interval', $limit * ($_GET['page'] - 1));
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['news_title']);
//	Cms::$tpl->showPage('news/list.tpl');
//
//	//test adding news using BaseModel
//	/*
//	  $aFields = [];
//	  $aFields['active'] = 0;
//	  $aFields['date_add'] = date('Y-m-d H:i:s');
//	  if ($oNewsAdmin->set($aFields)) {
//	  echo 'dodano news';
//	  } else {
//	  echo 'nie dodano';
//	  }
//	 */
//}
