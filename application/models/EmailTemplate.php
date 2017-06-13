<?php
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class EmailTemplate extends BaseModel {
	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'email_templates';
	}    
    
	public function getAll() {
		$q = "SELECT et.*,t.* FROM `" . $this->table . "` et "
				. "LEFT JOIN `" . $this->table . "_translation` t ON et.id=t.translatable_id "
				. "ORDER BY et.`name` ";

		$array = Cms::$db->getAll($q);	

		if ($array) {
			foreach ($array as &$entity) {
				$entity = mstripslashes($entity);
			}
		}

		$grouped = $this->groupByTranslation($array);

		return $grouped;
	}
		
	public function getTemplate($templateName, $options = []) {

		$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
		
		$q = "SELECT et.*,t.id as id2, t.translatable_id, t.title, t.content, t.info, t.locale FROM `" . $this->table . "` et "
				. "LEFT JOIN `" . $this->table . "_translation` t ON et.id=t.translatable_id ";	
		
		if (isset($options['translations']) && $options['translations'] === false) {
			$q .= "WHERE et.`name` = '" . $templateName . "' ";			
		} else {
			$q .= "WHERE et.`name` = '" . $templateName . "' AND t.locale = '" . $locale. "' ";
		}

		$result = Cms::$db->getRow($q);	
		
		return $result;
	}
	
//	public function getTemplate($templateName, $locale = LOCALE, $withTranslation = true) {		
//		$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
//		
//		$q = "SELECT et.*,t.id as id2, t.translatable_id, t.title, t.content, t.info, t.locale FROM `" . $this->table . "` et "
//				. "LEFT JOIN `" . $this->table . "_translation` t ON et.id=t.translatable_id ";				
//		
//		if (!$withTranslation) {
//			$q .= "WHERE et.`name` = '" . $templateName . "' ";	
//		} else {
//			$q .= "WHERE et.`name` = '" . $templateName . "' AND t.locale = '" . $locale. "' ";
//		}
//
//		$result = Cms::$db->getRow($q);	
//		
//		return $result;
//	}



	
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
	
	public function findBy($params = [], $fields = []) {	
		if (!$params) {
			return false;
		}		
		
		$where = $this->where($params);

		return $this->select($this->table, $where, '', '', $fields);
	}	
	
	public function clearDefault($id = 0, array $item) {
		if (!$id) {
			return false;
		}

        $q = "UPDATE " . $this->table . " SET `default` = 0";
        $q.= " WHERE `id` !='" . (int) $id . "' ";
		
        Cms::$db->update($q);	
	}
	
	public function isDefaultLanguageSet($id = 0) {
		$q = "SELECT * FROM `" . $this->table . "` l ";
		
		$q.= "WHERE l.default=1";
		
		if ($id) {
			$q.= " AND l.id != '" . $id . "' ";  
		}

		$array = Cms::$db->getAll($q);
		
		if ($array) {
			return true;
		}
		
		return false;
	}
	
	public function isActiveLanguageSet($id = 0) {
		$q = "SELECT * FROM `" . $this->table . "` l ";
		
		$q.= "WHERE l.active=1";
		if ($id) {
			$q.= " AND l.id != '" . $id . "' ";  
		}

		$array = Cms::$db->getAll($q);
		
		if ($array) {
			return false;
		}
		
		return true;
	}
	
	public function set($item = '') {		
		if (!$item) {
			return false;
		}

		return $this->insert($this->table, $item);
	}		
	
	public function edit($post, $entities) {		
		if (!$post) {
			return false;
		}						
		
		$template = $this->getTemplate($post['name'], ['translations' => false]);
		$templateTranslation = $this->getTemplate($post['name']);

		unset($post['action']);
		unset($post['name']);		

		foreach ($post as $locale => $trans) {

			$title = clearName($trans['title']);
			$content = addslashes($trans['content']);
			
			$item = array(
				'translatable_id' => $template['id'],
				'title' => $title,
				'content' => $content,
				'locale' => $locale,
			);
			
			if (!$templateTranslation || !isset($entities[$locale][$template['name']])) {
				$this->insert($this->table . '_translation', $item);
			} else {
				$q = "UPDATE `" . $this->table . "_translation` SET `title`='" . $title . "', `content`='" . $content . "' ";
				$q.= "WHERE `locale`='" . $locale . "' AND `translatable_id`='" . (int) $template['id'] . "' ";

				Cms::$db->update($q);				
			}
		
		}

		Cms::getFlashBag()->add('info', 'Zapisano zmiany dla szablonu.');
		return true;				
	}
	
	
}
