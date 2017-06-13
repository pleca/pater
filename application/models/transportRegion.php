<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class TransportRegionModel extends BaseModel {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'transport_region';
	}

	public function __destruct() {
		
	}

	public function getAll() {

		$q = "SELECT * FROM `" . $this->table . "` ";
		$item = Cms::$db->getAll($q);

		if ($item) {

			$this->mss($item);
			return $item;
		}
		return false;
	}
	
	public function getAllByCourierId($courier_id = 0) {

		$this->mas($courier_id);
		
		$q = "SELECT * FROM `" . $this->table . "` " .
					"WHERE `courier_id`= '" . $courier_id . "' ";
		
		$item = Cms::$db->getAll($q);

		if ($item) {

			$this->mss($item);
			return $item;
		}
		return false;
	}
	
	public function getAllByCourierIdTypeId($courier_id = 0, $type_id = 0) {

		$this->mas($courier_id);
		$this->mas($type_id);
		
		$q = "SELECT * FROM `" . $this->table . "` " .
					"WHERE `courier_id`= '" . $courier_id . "' " .
				   "AND `type_id`= '" . $type_id . "' ";
		
		$item = Cms::$db->getAll($q);

		if ($item) {

			$this->mss($item);
			return $item;
		}
		return false;
	}

	public function getById($id = 0) {

		$this->mas($id);

		$q = "SELECT * FROM `" . $this->table . "` " .
				  "WHERE `id` = '" . $id . "' ";

		$item = Cms::$db->getAll($q);

		if ($item) {

			$this->mss($item);
			return $item;
		}
		return false;
	}

	public function set($item = '') {

		$tableFields = $this->getTableFields($this->table);

		$fields = $this->getFields($item, $tableFields);

		$this->mas($fields);

		$q = "INSERT INTO `" . $this->table . "` " .
				  $this->createInsertFields($fields) .
				  $this->createInsertValues($fields);

		return Cms::$db->insert($q);
	}

	public function updateByID($id = 0, $item = '') {

		unset($item['id']);
		$this->mas($id);
		$this->mas($item);

		$tableFields = $this->getTableFields($this->table);

		$fields = $this->getFields($item, $tableFields);
		$this->mas($fields);

		$q = "UPDATE `" . $this->table . "` " .
				  $this->createUpdate($fields) .
				  "WHERE `id`='" . $id . "' ";

		return Cms::$db->update($q);
	}
	
	public function getCountryByCourierId($courier_ids = []) {
		$courier = implode(",", $courier_ids);
		$this->mas($courier);
		
		$q = "SELECT c.* FROM `" . $this->table . "` a "
				. "LEFT JOIN `" . $this->table . "_country` b ON a.id=b.region_id "
				. "LEFT JOIN `" . DB_PREFIX . "transport_country` c ON b.country_id=c.id "
				. "WHERE a.courier_id IN (" . $courier . ") "
				. "AND a.status_id IN (1,2) "
				. "AND c.status_id IN (1,2) "
				. "GROUP BY c.id ";

		$item = Cms::$db->getAll($q);
		if ($item) {
			$this->mss($item);
			return $item;
		}
		return false;
	}

}
