<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class TransportServiceModel extends BaseModel {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'transport_service';
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

}
