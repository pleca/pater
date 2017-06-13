<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class TransportTypeModel extends BaseModel {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'transport_type';
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

}
