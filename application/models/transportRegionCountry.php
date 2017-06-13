<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class TransportRegionCountryModel extends BaseModel {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'transport_region_country';
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
	
	public function getAllByRegionId($region_id = 0) {

		$this->mas($region_id);
		
		$q = "SELECT * FROM `" . $this->table . "` " .
					"WHERE `region_id`= '" . $region_id . "' ";
		
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
	
	public function getByRegionIdCountryId($region_id = 0, $country_id = 0) {

		$this->mas($region_id);
		$this->mas($country_id);
		
		$q = "SELECT * FROM `" . $this->table . "` " .
					"WHERE `region_id`= '" . $region_id . "' " .
					"AND `country_id`= '" . $country_id . "' ";
		
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
	
	public function deleteById($id = 0) {

		$this->mas($id);

		$q = "DELETE FROM `" . $this->table . "` " .
				  "WHERE `id` = '" . $id . "' ";

		return Cms::$db->delete($q);
	}

}
