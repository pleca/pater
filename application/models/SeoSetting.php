<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class SeoSetting extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'seo_settings';
	}
    
	public function getAll(array $params = []) {
		$q = "SELECT * FROM `" . $this->table . "`";			
		
		$entities = Cms::$db->getAll($q);
		$entities = getArrayByKey($entities, 'locale');

		return $entities;
	}
	
//	public function getBy($params = []) {
//
//		$where = $this->where($params);
//		return $this->select($this->table, $where);
//	}	
	
	
	public function edit($post) {		
		$entities = $this->getAll();	

		foreach (Cms::$langs as $v) {
			$title = addslashes($post[$v['code']]['title']);
			$metaDescription = addslashes($post[$v['code']]['meta_description']);
			$metaKeywords = addslashes($post[$v['code']]['meta_keywords']);
			$logoAlt = addslashes($post[$v['code']]['logo_alt']);
			$accordionHeader1 = addslashes($post[$v['code']]['accordion_header1']);
			$accordionContent1 = addslashes($post[$v['code']]['accordion_content1']);
			$accordionHeader2 = addslashes($post[$v['code']]['accordion_header2']);
			$accordionContent2 = addslashes($post[$v['code']]['accordion_content2']);
			$accordionHeader3 = addslashes($post[$v['code']]['accordion_header3']);
			$accordionContent3 = addslashes($post[$v['code']]['accordion_content3']);

			$item = array(
				'title' => $title,
				'meta_description' => $metaDescription,
				'meta_keywords' => $metaKeywords,
				'logo_alt' => $logoAlt,
				'accordion_header1' => $accordionHeader1,
				'accordion_content1' => $accordionContent1,
				'accordion_header2' => $accordionHeader2,
				'accordion_content2' => $accordionContent2,
				'accordion_header3' => $accordionHeader3,
				'accordion_content3' => $accordionContent3,
				'locale' => $v['code'],
			);						
			
			if (isset($entities[$v['code']])) {
				$where = $this->where(['locale' => $v['code']]);
				$this->update($this->table, $where, $item);
			} else {
				$this->insert($this->table, $item);
			}		
		}			

		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		return true;

	}
	

	public function getByName($name) {
		if (!$name) {
			return false;
		}

		$where = $this->where(["name" => $name]);
		return $this->select($this->table, $where)[0];
	}

	
	public function getById($id = '') {
		if (!$id) {
			return false;
		}
		
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
	
}
