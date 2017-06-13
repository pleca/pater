<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

//use System\Core\BaseModel;
require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/UnitTransportGroupUnit.php');

class UnitTransportGroup extends BaseModel {
    public $table;
    private static $instance;

	public function __construct() {
		$this->table = DB_PREFIX . 'unitTransport_transport_groups';
	}    
    
	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
        
        if (!$item['name']) {
            return false;
        }
        
		return $this->insert($this->table, $item);
	}
	
	public function updateById($id = '', $item = '') {
//		if(!$id) {
//			echo 'a1';die;
//			return false;
//		}
//		if(!$item) {
//			echo 'a2';die;
//			return false;
//		}
//        
//        if (!$item['name']) {
//			echo 'a3';die;
//            return false;
//        }
        
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
	
	public function deleteById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->delete($this->table, $where);
	}	
   
}
