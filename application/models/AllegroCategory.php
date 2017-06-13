<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class AllegroCategory extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'allegro_categories';
	}
    
	public function getAll() {
		return $this->select($this->table);
	}

	public function add($post) {
		
		if (!$post) {
			return false;
		}
		
		$name = isset($post['name']) ? $post['name'] : null;
		$category_id = isset($post['category_id']) ? $post['category_id'] : null;

		if (!$name || !$category_id) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
			return false;
		}
		
		$item = array(
			'name' => $name,					
			'category_id' => $category_id,					
		);
		
		if ($this->insert($this->table, $item)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
			return true;			
		} else {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
				return false;
		}	
	}
	
	public function getById($id = '') {
		if (!$id) {
			return false;
		}
		
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function updateById($id = '', $item = '') {
		if (!$id) {
			return false;
		}
		
		if (!$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
    
	public function deleteById($id) {
		
		if ($entity = $this->getBy(['id' => $id])[0]) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
    
//	
//	public function findBy($params = [], $fields = []) {	
//		if (!$params) {
//			return false;
//		}		
//		
//		$where = $this->where($params);
//
//		return $this->select($this->table, $where, '', '', $fields);
//	}	

}
