<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class Language extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'languages';
	}
    
	public function getAll() {
		return $this->select($this->table);
	}

	public function getById($id = '') {
		if (!$id) {
			return false;
		}
		
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function updateById($id = '', $item = '') {
		if (!$id) {
			return false;
		}
		
		if (!$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
	
	public function findBy($params = [], $fields = []) {	
		if (!$params) {
			return false;
		}		
		
		$where = $this->where($params);

		return $this->select($this->table, $where, '', '', $fields);
	}	
	
	public function clearDefault($id = 0, array $item) {
		if (!$id) {
			return false;
		}

        $q = "UPDATE " . $this->table . " SET `default` = 0";
        $q.= " WHERE `id` !='" . (int) $id . "' ";
		
        Cms::$db->update($q);	
	}
	
	public function isDefaultLanguageSet($id = 0) {
		$q = "SELECT * FROM `" . $this->table . "` l ";
		
		$q.= "WHERE l.default=1";
		
		if ($id) {
			$q.= " AND l.id != '" . $id . "' ";  
		}

		$array = Cms::$db->getAll($q);
		
		if ($array) {
			return true;
		}
		
		return false;
	}
	
	public function isActiveLanguageSet($id = 0) {
		$q = "SELECT * FROM `" . $this->table . "` l ";
		
		$q.= "WHERE l.active=1";
		if ($id) {
			$q.= " AND l.id != '" . $id . "' ";  
		}

		$array = Cms::$db->getAll($q);
		
		if ($array) {
			return false;
		}
		
		return true;
	}
	
	public function getFrontendLanguages() {
		$params = array(
			'active' => 1,
			'active_front' => 1
		);
		
		return $this->getBy($params);		
	}

	
	
}
