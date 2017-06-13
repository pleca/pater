<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class CustomerModel extends BaseModel {

    public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'customer';
	}

	public function getAll() {
		return $this->select($this->table);
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id OR !$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}	
}
