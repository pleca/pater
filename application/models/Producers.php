<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');

class Producers extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_manufacturer';
        $this->img_dir = CMS_DIR . '/files/producers';
        $this->img_url = CMS_URL . '/files/producers';        
	}

	public function getAll() {
		return $this->select($this->table);
	}

	public function loadAll($filtr = null) {
		$q = "SELECT `id`, `name`, `name_url`, `file` FROM `" . $this->table . "` WHERE `status_id` IN ('1','2') ";
        
        if ($filtr) {
            if (isset($filtr['popular'])) {
                $q .= "AND `popular` = '" . $filtr['popular'] . "' ";
            }
        }
        
        $q .= "ORDER BY `name` ASC ";

		$array = Cms::$db->getAll($q);

		$items = array();
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
			$v['url'] = URL . '/producers/' . $v['name_url'] . '.html';

            if(isset($v['file'])) $v['photo'] = get_photo($this->img_dir, SERVER_URL . $this->img_url, $v['file']);
			$items[] = $v;
		}
		return $items;
	}
	
	public function getById($id = 0) {
		if(!$id) {
			return false;
		}
		$id = addslashes($id);		
		$q = "SELECT * FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
		if ($item = Cms::$db->getRow($q)) {
			$item = mstripslashes($item);
			$item['url'] = URL . '/producers/' . $item['name_url'] . '.html';
			return $item;
		}
		return false;
	}

	public function loadProducers() {
		$q = "SELECT `id`, `name`, `name_url` FROM `" . $this->table . "` WHERE `active`='1' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['url'] = URL . '/producers/' . $v['name_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getProducer($name_url = '') {
		$q = "SELECT `id`, `name` FROM `" . $this->table . "` WHERE `name_url`='" . addslashes($name_url) . "'";
		$item = Cms::$db->getRow($q);
		return $item;
	}

}
