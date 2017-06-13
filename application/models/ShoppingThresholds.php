<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

//use System\Core\BaseModel;
require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/customer.php');


class ShoppingThresholds extends BaseModel {
	public $table;
    private static $instance;

	public function __construct() {
		$this->table = DB_PREFIX . 'shopping_thresholds';
	}
    
	public function edit($post) {
        
        $reset = false;
        //reset all modules
        $q = "UPDATE " . $this->table . " SET `active`=0 WHERE `type` = 0";
        if (Cms::$db->update($q)) {
            $reset = true;
        }
        
        if (isset($post['modules'])) {
            $modulesIds = implode(',', $post['modules']);
            $q = "UPDATE " . $this->table . " SET `active`=1 WHERE `id` IN(" . $modulesIds . ")";
            if (Cms::$db->update($q)) {
                Cms::$tpl->setInfo('Zapisano zmiany dla modułów.');
                return true;
            }
            
            Cms::$tpl->setError('Zmiana nie powiodła się.');
            return false;            
        } else {
            if ($reset) {
                Cms::$tpl->setInfo('Zapisano zmiany dla modułów.');
                return true;                
            } else {
                Cms::$tpl->setError('Zmiana nie powiodła się.');
                return false;                 
            }
        }
	}
    
	public function getAll() {
		return $this->select($this->table, '1', 'value');
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
        
        if (!$item['value'] || !$item['discount']) {
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
        
        if (!$item['value'] || !$item['discount']) {
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
