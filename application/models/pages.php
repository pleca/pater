<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/mailer.php');

class Pages {

	public $mailer;
	public $module;
	public $table;
	public $tableDesc;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->module = 'pages';
		$this->table = DB_PREFIX . 'pages';
		$this->tableDesc = DB_PREFIX . 'pages_desc';
		$this->tableMenu = DB_PREFIX . 'menu';
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
				$v['url'] = URL . '/' . $v['title_url'] . '.html';
				return $v;
			}
		}
		return false;
	}

	public function loadType($type = '') {
		if ($type) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE a.type IN (" . $type . ") AND d.lang_id='" . _ID . "' ";
			$array = Cms::$db->getAll($q);
			$items = array();
			foreach ($array as $v) {
				$v = mstripslashes($v);
				$v['desc_short'] = substr($v['desc_short'], 0, 150);
				$v['url'] = URL . '/' . $v['title_url'] . '.html';
				$items[] = $v;
			}
			return $items;
		}
		return false;
	}

	public function loadRss() {
		$q = "SELECT d.title, d.title_url, d.desc_short, a.date_add, a.active FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.active=1 AND d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHtml(stripslashes($v['desc_short']));
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
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
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}
    
	public function getByTitle($title = '') {
		if ($title) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE d.title='" . $title . "' AND d.lang_id='" . _ID . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['desc_short'] = clearHTML($v['desc_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $v['title_url'] . '.html';
				return $v;
			}
		}
		return false;
	}    

}
