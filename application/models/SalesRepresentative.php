<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class SalesRepresentative extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'sales_representatives';
		$this->tableCustomer = DB_PREFIX . 'customer';
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
	
	public function set($item = '') {		
		if (!$item) {
			return false;
		}
		
		return $this->insert($this->table, $item);
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
	
	public function deleteById($id = '') {
		if (!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->delete($this->table, $where);
	}
	
	public function isAssignment($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT id FROM `" . $this->tableCustomer . "` c ";
		$q.= "WHERE c.sales_representative = '" . $id . "' ";
        
		$array = Cms::$db->getAll($q);		

		if ($array) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['sales_representative_is_assignment_to_customer']);
			return true;
		}
		
		return false;		
	}

}
