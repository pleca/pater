<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');

class VariationRelated extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_variation_related';
	}
	
	public function edit() {
		if ($_POST) {

				$where = 'variation_id = ' . $_POST['variation_id'];
				$this->delete($this->table, $where);
				
				$item = array(
					'variation_id' => $_POST['variation_id']
				);
				
                if (isset($_POST['variationRelated'])) {
                    foreach ($_POST['variationRelated'] as $related) {
                        $item['variation_related_id'] = $related;					
                        $this->insert($this->table, $item);
                    }
                }
			
			return true;
		}
		
		return false;
	}
	
//	public function getAll($setIdAsKey = false) {
//        if ($setIdAsKey) {
//            $entities = $this->select($this->table);
//            $list = [];
//            foreach ($entities as $entity) {
//                $list[$entity['id2']] = $entity;
//            }
//            
//            return $list;
//        }
//		return $this->select($this->table);
//	}
	
	public function getAll($setIdAsKey = false) {
        if ($setIdAsKey) {
            $entities = $this->select($this->table);
            $list = [];
            foreach ($entities as $entity) {
                $list[$entity['id2']] = $entity;
            }
            
            return $list;
        }
		return $this->select($this->table);
	}
    
	
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
	}
	
	public function updateByEan($ean = '', $item = '') {
		if(!$ean OR !$item) {
			return false;
		}
		$where = $this->where(["ean" => $ean]);
		return $this->update($this->table, $where, $item);
	}

}
