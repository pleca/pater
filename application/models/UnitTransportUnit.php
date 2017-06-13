<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

//use System\Core\BaseModel;
require_once(SYS_DIR . '/core/BaseModel.php');


class UnitTransportUnit extends BaseModel {
    public $table;
    private static $instance;
	const MAX_WEIGHT = 30000;	//30 kg

	public function __construct() {
		$this->table = DB_PREFIX . 'unitTransport_units';
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
        
        if (!$item['length'] || !$item['width'] || !$item['height'] || !$item['price']) {
            return false;
        }
        
		return $this->insert($this->table, $item);
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
        
        if (!$item['length'] || !$item['width'] || !$item['height'] || !$item['price']) {
            return false;
        }
        
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
