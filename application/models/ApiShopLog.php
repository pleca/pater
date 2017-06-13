<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class ApiShopLog extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'api_shop_log';
	}

	public function getAll(array $params = []) {
		$limitStart = ''; 
		$limit = '';
		
		if (isset($params['limitStart'])) {
			$limitStart = $params['limitStart'];
		}
		
		if (isset($params['limit'])) {
			$limit = $params['limit'];
		}		
		

		return $this->select($this->table, '1', 'id', 'DESC', null, $limitStart , $limit);
	}

}
