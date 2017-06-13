<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}
if (Cms::$modules['news'] != 1) {
	die('This module is disabled!');
}

require_once(MODEL_DIR . '/NewsModel.php');
require_once(MODEL_DIR . '/gallery.php');

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

		$action = $this->getParam('slug') ? 'view' : 'list';
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
					$this->params['controller'] = $value;
					break;
				case 1:
					$this->params['slug'] = $value;
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

	public function viewAction() {		
		if (!$desc = $this->news->getDescByNameUrlLangId($this->getParam('slug'), _ID)[0]) {
			error_404();
		}
		
		$entity = $this->news->getById($desc['parent_id'])[0];  // dane z tabeli podstawowej	
		$entity['desc'] = $desc;

		$data = array(
			'entity'	=>	$entity,
			'url_back'	=>	URL . '/' . $this->module . '.html',
			'title'	=>	$entity['desc']['name'],
			'pageTitle' => $entity['desc']['name'] . ' - ' . Cms::$seo['title'],
			'pageDescription' => $entity['desc']['desc_short'],
	
		);

		echo Cms::$twig->render('templates/news/view.twig', $data);			
	}

	public function listAction() {
		$params = ["active" => 1, "lang_id" => _ID, "limit" => 0, "offset" => 5];
		
		if (!$entities = $this->news->getAllNews($params)) {
			$entities = array();
		}
		
		foreach ($entities as &$v) {
			$v['url'] = URL . '/' . $this->module . '/' . $v['name_url'] . '.html';
		}

		$data = array(
			'entities'	=>	$entities,
			'title'	=>	$GLOBALS['LANG']['module_news'],
			'pageTitle' => $GLOBALS['LANG']['module_news'] . ' - ' . Cms::$seo['title'],
	
		);

		echo Cms::$twig->render('templates/news/list.twig', array_merge($data, $params));		
	}
}

$News = new News();
$News->init($params);




//$oGallery = new Gallery();

//	if ($aItem['gallery_id'] > 0 AND Cms::$modules['gallery'] == 1) {
//		$aGallery = $oGallery->loadById($aItem['gallery_id']);
//		$aPhotos = $oGallery->loadPhotos($aGallery['id']);
//
//		Cms::$tpl->assign('aGallery', $aGallery);
//		Cms::$tpl->assign('aPhotos', $aPhotos);
//	}

//} else {
//	$limit = 5;
//	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
//		$_GET['page'] = 1;
//	}
//	$limitStart = ($_GET['page'] - 1) * $limit;
//
//	$aItems = $oNews->loadArticles($limitStart, $limit);
//	$pages = $oNews->getPages($limit);
//
//	Cms::$tpl->assign('pages', $pages);
//	Cms::$tpl->assign('page', $_GET['page']);

//}

