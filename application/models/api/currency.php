<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class ApiCurrency {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'shop_currency';
	}

	public function __destruct() {
		
	}

	public function get_by_code($code = '') {
		if ($code) {
			$q = "SELECT a.* FROM `" . $this->table . "` a WHERE a.name='" . addslashes($code) . "' ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

}
