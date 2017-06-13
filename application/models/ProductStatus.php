<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class ProductStatus extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_status';
	}

	public function getAll(array $params = []) {
		$q = "SELECT s.*, t.translatable_id, t.name, t.locale "
				. "FROM `" . $this->table . "` s "
				. "LEFT JOIN `" . $this->table . "_translation` t ON s.id = t.translatable_id ";				

		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 's';
				
				if ($key == 'locale') {
					$prefix = 't';
				}		
				
				if (is_array($value)) {
					$prefix = 's';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}								
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}			
		}		
		
		$q .= " ORDER BY s.`order`, t.`name` ASC";

		$entities = Cms::$db->getAll($q);
		
		if (!isset($params['locale'])) {
			$entities = $this->groupByTranslation($entities, 'id');
		}		
		
		return $entities;
	}
	
	public function getById($id) {
		
		$status = $this->getStatus($id);

		$result = [];		
		$result = $status;
		$result['trans'] = $this->getTranslation($id);
		
		return $result;
	}
	
	public function getStatus($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

		return $result;
	}
	
	public function getTranslation($id) {
		$q = "SELECT s.*,t.translatable_id, t.name, t.locale FROM `" . $this->table . "` s "
				. "LEFT JOIN `" . $this->table . "_translation` t ON s.id = t.translatable_id "
				. "WHERE s.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		return $result;
    }   
    

    
    
//	public function set($item = '') {		
//		if(!$item) {
//			return false;
//		}
//		return $this->insert($this->table, $item);
//	}
//    
//	public function getAll($setIdAsKey = false) {
//        if ($setIdAsKey) {
//            $entities = $this->select($this->table);
//            $list = [];
//            foreach ($entities as $entity) {
//                $list[$entity['id']] = $entity;
//            }
//            
//            return $list;
//        }
//		return $this->select($this->table);
//	}
//    
//	public function getMaxOrder() {
//		$q = "SELECT MAX(`order`) FROM " . $this->table . " ";
//		return Cms::$db->max($q);
//	}    
}
