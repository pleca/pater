<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class NewsModel extends BaseModel {

	public $table;
	private $dir;
	private $url;

	public function __construct() {
		$this->table = DB_PREFIX . 'news';
		$this->dir = CMS_DIR . '/files/news';
		$this->url = CMS_URL . '/files/news';
	}

	public function __destruct() {
		
	}

	public function getAllNews($params = '') {
		$this->mas($params);
		
		$q = "SELECT a.*, b.name, b.name_url, b.desc_short "
				. "FROM `" . $this->table . "` a "
				. "LEFT JOIN `" . $this->table . "_desc` b ON a.id=b.parent_id "
				. "WHERE a.active='" . $params['active'] . "' "
				. "AND b.lang_id='" . $params['lang_id'] . "' "
				. "ORDER BY a.date_add DESC, a.id DESC "
				. "LIMIT " . $params['limit'] . ", " . $params['offset'] . " ";
		$item = Cms::$db->getAll($q);
		if ($item) {
			$this->mss($item);
			return $item;
		}
		return false;
	}	
	
	// w modelu funkcje ktore ustawiaja tabele i korzystaja z BaseModel z uniwersalnych funkcji, select, insert, update, delete
	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);	// trzeba miec swiadomosc jaki sa pola w bazie | $where = "`name`='Joe' AND `status`='boss' OR `status`='active' " | AND lub OR
		return $this->select($this->table, $where);
	}
	
	public function getDescByNameUrlLangId($name_url = '', $lang_id = _ID) {
		if(!$name_url) {
			return false;
		}
		$where = $this->where(["name_url" => $name_url, "lang_id" => $lang_id]);
		return $this->select($this->table . '_desc', $where);
	}
	
	public function getDescByParentIdLangId($parent_id = '', $lang_id = _ID) {
		if(!$parent_id) {
			return false;
		}
		$where = $this->where(["parent_id" => $parent_id, "lang_id" => $lang_id]);
		return $this->select($this->table . '_desc', $where);
	}
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
	}
	
	public function setDesc($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table . '_desc', $item);
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
	
	public function updateDescById($id = '', $item = '') {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
		$where = $this->where(["id2" => $id]);
		return $this->update($this->table . '_desc', $where, $item);
	}
	
	public function deleteById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->delete($this->table, $where);
	}
	
	public function deleteDescById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id2" => $id]);
		return $this->delete($this->table . '_desc', $where);
	}
	
	
		
	
	
	
	
	
	
	
	// OLD
	
	

	public function load($url = '') {
		if ($url) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE d.title_url='" . $url . "' AND d.lang_id='" . _ID . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['photo'] = $this->getSrc($v['file']);
				$v['desc_short'] = clearHTML($v['desc_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
				return $v;
			}
		}
		return false;
	}

	public function loadArticles($limitStart = 0, $limit = 10) {
		$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.title!='' AND a.active=1 AND d.lang_id='" . _ID . "' ";
		$q.= "ORDER BY a.date_add DESC, a.id DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['desc_short'] = clearHTML($v['desc_short'], 200, '...');
			$v['photo'] = $this->getSrc($v['file']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getPages($limit = 10) {
		$q = "SELECT COUNT(a.id) FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id WHERE d.title!='' AND a.active=1 AND d.lang_id='" . _ID . "' ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadFirstPage() {
		$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.title!='' AND a.active=1 AND d.lang_id='" . _ID . "' ";
		$q.= "ORDER BY a.date_add DESC, a.id DESC ";
		$q.= "LIMIT 5";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHTML($v['desc_short'], 350, '...');
			$v['photo'] = $this->getSrc($v['file']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function loadSiteMap() {
		$q = "SELECT d.title, d.title_url, a.active FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.title!='' AND d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
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
		$q.= "WHERE d.title!='' AND a.active=1 AND d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['title'] = clearHtml($v['title']);
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
		$q.= "WHERE d.title!='' AND a.active=1 AND d.lang_id='" . _ID . "' AND ";
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

	function getSrc($fileName) {
		$fileNameS = changeFileName($fileName, '_s');
		$row = '';
		if (!empty($fileName) and file_exists($this->dir . '/' . $fileName)) {
			$row['normal'] = $this->url . '/' . $fileName;
			$row['small'] = $this->url . '/' . $fileNameS;
		}
		return $row;
	}

}
