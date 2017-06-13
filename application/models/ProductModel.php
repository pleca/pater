<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class ProductModel extends BaseModel {

    const IMAGE_DIR = '/files/product/';
    public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product';
	}

	public function getAll() {
		return $this->select($this->table);
	}
}
