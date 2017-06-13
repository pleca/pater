<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class Gallery {

	public $module;
	public $table;
	public $tableDesc;
	public $dir;
	public $url;

	public function __construct() {
		$this->module = 'gallery';
		$this->table = DB_PREFIX . 'gallery';
		$this->tableDesc = DB_PREFIX . 'gallery_desc';
		$this->tablePhotos = DB_PREFIX . 'gallery_photos';
		$this->dir = CMS_DIR . '/files/' . $this->module;
		$this->url = CMS_URL . '/files/' . $this->module;
		$this->widthS = 135;
		$this->heightS = 95;
	}

	public function __destruct() {
		
	}

	public function load($url = '') {
		if ($url) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE d.title_url='" . $url . "' AND d.lang_id='" . _ID . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['desc_short'] = clearHTML($v['desc_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
				if ($v['date_mod'] == '0000-00-00')
					$v['date_mod'] = $v['date_add'];
				$v['date_add'] = explode('-', $v['date_add']);
				$v['date_add'] = miesiac2($v['date_add'][1]) . '<br /><strong>' . $v['date_add'][2] . '</strong>';
				$N = date('N', strtotime($v['date_mod']));
				$v['date_mod'] = explode('-', $v['date_mod']);
				$v['date_mod'] = dzien2($N) . ', ' . $v['date_mod'][2] . ' ' . miesiac3($v['date_mod'][1]) . ' ' . $v['date_mod'][0];
				return $v;
			}
		}
		return false;
	}

	public function loadById($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.*, d.title FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id WHERE a.id='" . (int) $id . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v['date_add'] = explode('-', $v['date_add']);
				$v['date_add'] = $v['date_add'][2] . '.' . $v['date_add'][1] . '.' . $v['date_add'][0];
				return $v;
			}
		}
		return false;
	}

	public function loadPhotos($parent_id = 0) {
		$q = "SELECT * FROM `" . $this->tablePhotos . "` WHERE `parent_id`='" . (int) $parent_id . "' ORDER BY `order` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['photo'] = $this->getSrc($v['file'], $parent_id);
			$items[] = $v;
		}
		return $items;
	}

	public function loadArticles($limitStart = 0, $limit = 10) {
		$q = "SELECT a.id, d.title, d.title_url, p.file FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "LEFT JOIN `" . $this->tablePhotos . "` p ON a.id=p.parent_id WHERE a.active=1 AND d.lang_id='" . _ID . "' ";
		$q.= "GROUP BY a.id ORDER BY a.date_add DESC, a.id DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$v['photo'] = $this->getSrc($v['file'], $v['id']);
			$items[] = $v;
		}
		return $items;
	}

	public function getPages($limit = 10) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` WHERE `active`=1 ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadFirstPage($limit = 4) {
		$q = "SELECT a.id, d.title, d.title_url, p.file FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "LEFT JOIN `" . $this->tablePhotos . "` p ON a.id=p.parent_id WHERE a.active=1 AND d.lang_id='" . _ID . "' GROUP BY a.id ";
		$q.= "LIMIT " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$v['photo'] = $this->getSrc($v['file'], $v['id']);
			$items[] = $v;
		}
		return $items;
	}

	public function loadSiteMap() {
		$q = "SELECT d.title, d.title_url, a.active FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function loadRss() {
		$q = "SELECT d.title, d.title_url, d.desc_short, a.date_add, a.active FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.active=1 AND d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHtml(stripslashes($v['desc_short']));
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$v['category'] = $this->module;
			$items[] = $v;
		}
		return $items;
	}

	public function loadSearch($keyword) {
		$keyword = addslashes($keyword);
		$keyword2 = str_replace('ó', '&oacute;', $keyword);
		$keyword2 = str_replace('Ó', '&Oacute;', $keyword2);
		$q = "SELECT d.title, d.title_url, d.desc_short FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.active=1 AND d.lang_id='" . _ID . "' AND ";
		$q.= "(d.title LIKE '%" . $keyword . "%' OR d.desc LIKE '%" . $keyword2 . "%' OR d.desc_short LIKE '%" . $keyword . "%') ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHTML($v['desc_short'], 350, '...');
			$v['desc_short'] = str_replace($keyword, '<strong>' . $keyword . '</strong>', $v['desc_short']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function loadViews() {
		$q = "SELECT a.id, d.title FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.view='1' AND d.lang_id='" . _ID . "' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$items[] = $v;
		}
		return $items;
	}

	function getSrc($fileName, $parent_id) {
		$fileNameS = changeFileName($fileName, '_s');
		$row = '';
		if (!empty($fileName) and file_exists($this->dir . '/' . $parent_id . '/' . $fileName)) {
			$row['normal'] = $this->url . '/' . $parent_id . '/' . $fileName;
			$row['small'] = $this->url . '/' . $parent_id . '/' . $fileNameS;
		}
		return $row;
	}

}
