<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

//use System\Core\BaseModel;
require_once(SYS_DIR . '/core/BaseModel.php');


class UnitTransportGroupUnit extends BaseModel {
    public $table;
    public $tableGroups;
    private static $instance;

	public function __construct() {
		$this->table = DB_PREFIX . 'unitTransport_transport_groups_units';
		$this->tableGroups = DB_PREFIX . 'unitTransport_transport_groups';
		$this->tableUnits = DB_PREFIX . 'unitTransport_units';
	}    
	
	public function findBy($params = [], $fields = []) {	
		if (!$params) {
			return false;
		}		
		
		$where = $this->where($params);

		return $this->select($this->table, $where, '', '', $fields);
	}	
    
	public function findAll(array $params = []) {

		$q = "SELECT tgu.*, tg.name as group_name, u.length, u.width, u.height, u.price "
				. "FROM `" . $this->table . "` tgu "
				. "LEFT JOIN `" . $this->tableGroups . "` tg ON tgu.transport_group_id=tg.id "
				. "LEFT JOIN `" . $this->tableUnits . "` u ON tgu.unit_id=u.id ";

		if (isset($params['group_id'])) {
			$q .= "WHERE tg.id='" . $params['group_id'] . "' ";
		}

		if (isset($params['unit_id'])) {
			if (is_array($params['unit_id'])) {
				$q .= "AND tgu.unit_id IN(" . implode(',', $params['unit_id']) . ") ";
			} else {
				$q .= "AND tgu.unit_id='" . $params['group_id'] . "' ";
			}
			
		}

		$item = Cms::$db->getAll($q);
		if ($item) {
			$this->mss($item);
			return $item;
		}
		return false;
	}
	
	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getByGroupId($groupId = '') {
		if(!$groupId) {
			return false;
		}
		$where = $this->where(["transport_group_id" => $groupId]);
		return $this->select($this->table, $where);
	}
	
	public function getById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["transport_group_id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
        
        if (!$item['transport_group_id'] || !$item['unit_id']) {
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
        
        if (!$item['transport_group_id'] || !$item['unit_id']) {
            return false;
        }
        
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
	
	public function deleteById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["transport_group_id" => $id]);
		return $this->delete($this->table, $where);
	}
	
	
	public function getMaxUnitInGroup($groupId, $items = []) {
		if (!$groupId) {
			return false;
		}
		
		$availableUnits = [];
		
		if ($items) {
			foreach ($items as $item) {
				if ($item['transport_group_id'] == $groupId) {
					$availableUnits[] = $item['transport_unit_id'];
				}
			}			
		}
				
		$acceptableUnits = array_unique($availableUnits);
		$units = $this->findAll(['group_id' => $groupId, 'unit_id' => $acceptableUnits]);

		if ($units) {
			foreach ($units as $key => $unit) {
				if ($key == 0) {
					$biggestUnit = $unit;
					continue;
				}
				
				$unitVolume = $unit['length'] * $unit['width'] * $unit['height'];
				$currentMaxunitVolume = $biggestUnit['length'] * $biggestUnit['width'] * $biggestUnit['height'];
				if ($unitVolume > $currentMaxunitVolume) {
					$biggestUnit = $unit;
				}
				
			}
			
			return $biggestUnit;
		}
		
		return false;
	}
   
}
