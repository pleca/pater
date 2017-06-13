<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class OrderTransport extends BaseModel {

    public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'order_transport';
	}

	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getAllForGa() {
		$q = "SELECT ot.*, ts.name_online FROM `" . $this->table . "` ot LEFT JOIN `" . DB_PREFIX . "transport_service` ts ON ot.service_id=ts.id ";

		$array = Cms::$db->getAll($q);
		
		foreach ($array as &$v) {
			$v = mstripslashes($v);
			$v['price_gross'] = formatPrice($v['price'], $v['tax']);
		}
		
		return $array;
	}	
}
