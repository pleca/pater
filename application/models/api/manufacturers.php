<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class ApiManufacturers {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_manufacturer';
	}

	public function __destruct() {
		
	}

	public function get_all() {
		$q = "SELECT * FROM `" . $this->table . "` ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

}
