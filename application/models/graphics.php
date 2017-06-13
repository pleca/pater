<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class GraphicsModel extends BaseModel {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'graphics';
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

	public function updateById($id = 0, $item = '') {

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
