<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class NotificationsStockAvailability extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'notifications_stock_availability';
	}

	public function findBy($params = [], $fields = []) {	
		if (!$params) {
			return false;
		}		
		
		$where = $this->where($params);

		return $this->select($this->table, $where, '', '', $fields);
	}	
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
	}
}
