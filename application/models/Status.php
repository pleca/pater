<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class Status extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'status';
	}

	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
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
    
	public function getMaxOrder() {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " ";
		return Cms::$db->max($q);
	}    
}
