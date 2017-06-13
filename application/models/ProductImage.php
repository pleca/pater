<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');

class ProductImage extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_image';
	}

	public function getAll($setIdAsKey = false) {
        if ($setIdAsKey) {
            $entities = $this->select($this->table);
            $list = [];
            foreach ($entities as $entity) {
                $list[$entity['id']] = $entity;
            }
            
            return $list;
        }
		return $this->select($this->table);
	}
}
