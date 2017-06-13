<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class Module extends BaseModel {
	public $table;	

	public function __construct() {
		$this->table = DB_PREFIX . 'modules';
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
                return true;
            }

            return false;            
        } else {
            if ($reset) {

                return true;                
            } else {

                return false;                 
            }
        }
	}
    
	public function getAll($params = []) {
		
		$where = $params ? $this->where($params): '1';
		return $this->select($this->table, $where);
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
		return $this->insert($this->table, $item);
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id) {
			return false;
		}
		if(!$item) {
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
	
	public function getMenuModules() {
		$entities = $this->getAll(['type' => '1', 'active' => '1']);
		
		if (!$entities) {
			return false;
		}
		
		$menuModules = [];
		$menuModules[] = 'index';
		
		foreach ($entities as $entity) {
			
			if (in_array($entity['name'], Menu::$availableModules)) {
				$menuModules[] = $entity['name'];
			}
			
			if ($entity['name'] == 'shop') {
				$menuModules[] = 'basket';
				$menuModules[] = 'shop';
				$menuModules[] = 'bestsellers';
				$menuModules[] = 'recommended';
				$menuModules[] = 'promotions';
				$menuModules[] = 'products-clearance';
				$menuModules[] = 'new';
                $menuModules[] = 'categories';
                $menuModules[] = 'producers';		
			}
			
			if ($entity['name'] == 'contact') {
				$menuModules[] = 'contact-form';
			}
		}

		return $menuModules;
	}
}
