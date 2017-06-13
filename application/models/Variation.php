<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');

class Variation extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_variation';
	}

	public function __destruct() {
		
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
	
	public function getFeatureValuesIds() {
		$variations = $this->select($this->table);
		
		$featureValuesIds = [];
		
		if ($variations) {
			foreach ($variations as $variation) {
				if ($variation['feature1_value_id']) {
					$featureValuesIds[] = $variation['feature1_value_id'];
				}
				
				if ($variation['feature2_value_id']) {
					$featureValuesIds[] = $variation['feature2_value_id'];
				}
				
				if ($variation['feature3_value_id']) {
					$featureValuesIds[] = $variation['feature3_value_id'];
				}
			}			
		}
		
		$featureValuesIds = array_unique(array_filter($featureValuesIds));
		
		return $featureValuesIds;
	}
}
