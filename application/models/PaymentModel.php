<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class PaymentModel extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'payment';
	}

	public function __destruct() {
		
	}

	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
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
	
	public function deleteById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->delete($this->table, $where);
	}

}
