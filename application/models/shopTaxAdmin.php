<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class TaxAdmin {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'taxes';
	}

	public function __destruct() {
		
	}

	public function loadAdmin() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `position` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['value']);
			$items[] = $v;
		}
		return $items;
	}

	public function loadByIdAdmin($id) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}

	public function addAdmin($post) {
		$value = isset($post['value']) ? str_replace(',', '.', $post['value']) : '';
		$orderMax = $this->getMaxOrder();
		$orderMax = $orderMax['0'] + 1;

		$q = "INSERT INTO " . $this->table . " SET `value`='" . $value . "', `position`='" . $orderMax . "' ";
		$id = Cms::$db->insert($q);

		return true;
	}

	public function getMaxOrder() {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " ";
		return Cms::$db->max($q);
	}

	public function editAdmin($post) {
		$value = isset($post['value']) ? str_replace(',', '.', $post['value']) : '';

		$q = "UPDATE " . $this->table . " SET `value`='" . $value . "' WHERE `id`='" . (int) $post['id'] . "' ";
		Cms::$db->update($q);
		Cms::$tpl->setInfo('Zapisano zmiany dla wybranego elementu.');
		return true;
	}

	function deleteAdmin($id) {
		if ($item = $this->loadByIdAdmin($id)) {
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $item['id'] . "' ";
			return Cms::$db->delete($q);
			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `position`>'" . $item['order'] . "' ";
			Cms::$db->update($q);
		}

		return false;
	}

}
