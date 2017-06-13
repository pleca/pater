<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class ApiCategories {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'categories';
	}

	public function __destruct() {
		
	}

	public function get_all() {
		$q = "SELECT a.*, b.name FROM `" . $this->table . "` a LEFT JOIN `" . $this->table . "_translation` b ON a.id=b.translatable_id ";
                $q.= "WHERE b.locale='en' ORDER BY a.`parent_id` ASC, a.`order` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

	public function get_by_id($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.*, b.name, b.slug FROM `" . $this->table . "` a LEFT JOIN `" . $this->table . "_translation` b ON a.id=b.translatable_id ";
                        $q.= "WHERE a.id='" . (int) $id . "' AND b.locale='en' ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

}
