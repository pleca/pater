<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class SliderModel extends BaseModel {

	public $table;
	private $dir;
	private $url;

	public function __construct() {
		$this->table = DB_PREFIX . 'slider';
		$this->dir = CMS_DIR . '/files/slider';
		$this->url = CMS_URL . '/files/slider';
	}

	public function __destruct() {
		
	}

	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getSrc($file = '') {
		$item = '';
		if (!empty($file) and file_exists($this->dir . '/' . $file)) {
			$item = $this->url . '/' . $file;
		}
		return $item;
	}

}
