<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class OrderAddress extends BaseModel {

    public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'order_address';
	}

	
	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getAllForGa() {
		$q = "SELECT oa.*, tc.code as `country_code` FROM `" . $this->table . "` oa LEFT JOIN `" . DB_PREFIX . "transport_country` tc ON oa.country=tc.id ";
		$q.= "WHERE oa.model='billing'";

		$array = Cms::$db->getAll($q);
		
		foreach ($array as &$v) {
			$v = mstripslashes($v);
		}
		
		return $array;
	}	
}
