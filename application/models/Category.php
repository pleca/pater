<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');

class Category extends BaseModel {

	public $table;
	public $tableDesc;

	public function __construct() {
		$this->table = DB_PREFIX . 'categories';
	}
	
	public function getAll(array $params = [], $fields = []) {
		$categoryColumns = $this->getTableFields($this->table);		
		$translationColumns = array_diff($this->getTableFields($this->table . '_translation'), ['id']);

		if ($fields) {
			$lastField = end($fields);
			
			$q = "SELECT c.id,t.locale,";

			foreach ($fields as $field) {
				if (in_array($field, $categoryColumns)) {
					$q .= "c." . $field;
				}
				
				if (in_array($field, $translationColumns)) {
					$q .= "t." . $field;
				}
				
//				if ($field == 'parent_url') {
//					$q .= "(SELECT `slug` FROM `" . $this->table . "_translation` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` ";
//				}
				
				if ($field !== $lastField) {
					$q .= ",";
				}
				
			}
			
			$q .= " FROM `" . $this->table . "` c "
				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id ";				
			
		} else {
			$q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.locale, (SELECT `slug` FROM `" . $this->table . "_translation` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` "
					. "FROM `" . $this->table . "` c "
					. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id ";				
		}
		
		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 'c';
				
				if (in_array($key, $translationColumns)) {
					$prefix = 't';
				}		
				
				if (is_array($value)) {
					$prefix = 'c';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}								
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}		
		
		$q .= " ORDER BY c.`order` ASC";
//dump($q);
		$entities = Cms::$db->getAll($q);
		$this->decorate($entities);

		//get submenus
		$q = "SELECT c.id, c.parent_id, t.name, t.slug, t.locale, (SELECT `slug` FROM `" . $this->table . "_translation` WHERE translatable_id=c.parent_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `parent_url` FROM `" . $this->table . "` c LEFT JOIN `" . $this->table . "_translation` t ON c.id=t.translatable_id ";
		$q.= "WHERE c.parent_id != '0' ORDER BY c.order ASC ";
				
		$subEntities = Cms::$db->getAll($q);
		$this->decorate($subEntities);
		
		if ($subEntities) {
			foreach ($entities as &$entity) {
				foreach ($subEntities as $subEntity) {
					if ($entity['id'] == $subEntity['parent_id'] && $entity['locale'] == $subEntity['locale']) {
						$entity['subcategories'][] = $subEntity;
					}
				}
			}			
		}
		
		if (!isset($params['locale'])) {
			$entities = $this->groupByTranslation($entities, 'id');
		}				

		return $entities;
	}	
	
	public function findBySlug($slug = null, $mainCategory = null, $locale = null) {

		if (!$slug) {
			return false;
		}

		if (!$locale) {
			$locale = Cms::$session->get('locale');
		}
		
		$q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.seo_title, t.meta_description, t.accordion_header1, t.accordion_content1, t.accordion_header2, t.accordion_content2, t.accordion_header3, t.accordion_content3, t.locale, (SELECT `slug` FROM `" . $this->table . "` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` "
				. "FROM `" . $this->table . "` c "
				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id "	
				. "WHERE t.slug = '" . $slug . "' AND t.locale = '" . $locale ."' ";

		if ($mainCategory) {
			$q .= "AND c.parent_id = '" . (int) $mainCategory['id'] . "' ";
		}

		$result = Cms::$db->getRow($q);

		return $result;
	}		
	
	protected function decorate(array &$entities) {
		if (!$entities) {
			return false;
		}
		
		if (!defined('URL')) {
			define('URL', SERVER_URL . CMS_URL);	
		}
		
		foreach ($entities as &$entity) {		
			$entity = mstripslashes($entity);
			
			if (isset($entity['slug'])) {
				if (isset($entity['parent_url']) && !empty($entity['parent_url'])) {
					$entity['url'] = URL . '/shop/' . $entity['parent_url'] . '/' . $entity['slug'] . '.html';
				} else {
					$entity['url'] = URL . '/shop/' . $entity['slug'] . '.html';
				}		
			}
		}		
	}	
	
	public function add($post) {
		$post = maddslashes($post);

		$post['show_expanded'] = isset($post['show_expanded']) ? $post['show_expanded'] : 0;
		$orderMax = $this->getMaxOrder($post['parent_id']);
		$order = $orderMax['0'] + 1;
		
		$item = array(						
			'parent_id' => $post['parent_id'],
			'status_id' => $post['status_id'],
			'show_expanded' => $post['show_expanded'],
			'order'		=> $order
		);

		$id = $this->insert($this->table, $item);
		$this->convertToTranslationData($post);

		if ($id) {
			foreach ($post as $locale => $trans) {
				$name = clearName($trans['name']);
				$seoTitle = addslashes($trans['seo_title']);
				$metaDescription = addslashes($trans['meta_description']);
				
				$accordionHeader1 = addslashes($trans['accordion_header1']);
				$accordionContent1 = addslashes($trans['accordion_content1']);
				$accordionHeader2 = addslashes($trans['accordion_header2']);
				$accordionContent2 = addslashes($trans['accordion_content2']);
				$accordionHeader3 = addslashes($trans['accordion_header3']);
				$accordionContent3 = addslashes($trans['accordion_content3']);
			
				$slug = makeUrl($name);		

				$item = array(
					'translatable_id' => $id,					
					'name' => $name,					
					'slug' => $slug,					
					'seo_title' => $seoTitle,			
					'meta_description' => $metaDescription,			
					'accordion_header1' => $accordionHeader1,			
					'accordion_content1' => $accordionContent1,			
					'accordion_header2' => $accordionHeader2,			
					'accordion_content2' => $accordionContent2,			
					'accordion_header3' => $accordionHeader3,			
					'accordion_content3' => $accordionContent3,			
					'locale' => $locale,
				);
				
				$this->insert($this->table . '_translation', $item);
			}
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
		}

		return $id;
	}	
	
	public function getMaxOrder($parentId) {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " WHERE `parent_id`='" . $parentId . "' ";
		return Cms::$db->max($q);
	}	
	
	public function moveUp($id) {
		if ($entity = $this->getBy(['id' => $id])[0]) {
			if ($entity['order'] > 1) {
				$q = "UPDATE " . $this->table . " SET `order`=`order`+1 ";
				$q.= "WHERE `parent_id`='" . $entity['parent_id'] . "' AND `order`='" . ($entity['order'] - 1) . "'";
				if (Cms::$db->update($q)) {
					$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `id`='" . (int) $id . "'";
					if (Cms::$db->update($q)) {						
						Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_up']);
						return true;
					}
				}
			}
		}
		
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
		return false;
	}

	public function moveDown($id) {
		if ($entity = $this->getBy(['id' => $id])[0]) {
			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 ";
			$q.= "WHERE `parent_id`='" . $entity['parent_id'] . "' AND `order`='" . ($entity['order'] + 1) . "'";
			if (Cms::$db->update($q)) {
				$q = "UPDATE " . $this->table . " SET `order`=`order`+1 WHERE `id`='" . (int) $id . "'";
				if (Cms::$db->update($q)) {
					Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_down']);
					return true;
				}
			}
		}
		
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);
		return false;
	}	

	protected function removeParentChildren($parent) {
		$children = $this->getBy(['parent_id' => $parent['id']]);
		
		if ($children) {
			$childrenIds = [];
			foreach ($children as $child) {
				$childrenIds[] = $child['id'];
			}

			$q = "DELETE FROM `" . $this->table . "` WHERE `parent_id`='" . (int) $parent['id'] . "' ";
			Cms::$db->delete($q);

			$childrenIds = implode(',', $childrenIds);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id` IN(" . $childrenIds . ")";
			Cms::$db->delete($q);				
		}		
	}
	
	public function deleteById($id) {
		
		if ($entity = $this->getBy(['id' => $id])[0]) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $entity['order'] . "' AND `parent_id`='" . $entity['parent_id'] . "' ";
			Cms::$db->update($q);
			
			$this->removeParentChildren($entity);

			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
	
	public function getCategory($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

		return $result;
	}
	
	public function getTranslation($id) {
		$q = "SELECT c.*,t.translatable_id, t.name, t.seo_title, t.meta_description, t.accordion_header1, t.accordion_content1, t.accordion_header2, t.accordion_content2, t.accordion_header3, t.accordion_content3, t.slug, t.locale FROM `" . $this->table . "` c "
				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id "
				. "WHERE c.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		return $result;
	}
	
	public function getById($id) {
		
		$category = $this->getCategory($id);

		$result = [];		
		$result = $category;
		$result['trans'] = $this->getTranslation($id);
		
		return $result;
	}	
	
	public function edit($post) {		
		$post['show_expanded'] = isset($post['show_expanded']) ? $post['show_expanded'] : 0;		

		$q = "UPDATE " . $this->table . " SET `status_id`='" . $post['status_id'] . "', `parent_id`='" . $post['parent_id'] . "', `show_expanded`='" . $post['show_expanded'] . "' WHERE `id`='" . (int) $post['id'] . "' ";
		Cms::$db->update($q);

		$entities = $this->getAll(['parent_id' => $post['parent_id']]);			
		$id = $post['id'];

		foreach (Cms::$langs as $v) {
			$name = addslashes($post[$v['code']]['name']);
			$seoTitle = addslashes($post[$v['code']]['seo_title']);
			$metaDescription = addslashes($post[$v['code']]['meta_description']);
			
			$accordionHeader1 = addslashes($post[$v['code']]['accordion_header1']);
			$accordionContent1 = addslashes($post[$v['code']]['accordion_content1']);
			$accordionHeader2 = addslashes($post[$v['code']]['accordion_header2']);
			$accordionContent2 = addslashes($post[$v['code']]['accordion_content2']);
			$accordionHeader3 = addslashes($post[$v['code']]['accordion_header3']);
			$accordionContent3 = addslashes($post[$v['code']]['accordion_content3']);
			$slug = makeUrl($name);		

			if ($this->existsTranslation($id, $v['code'], $entities)) {
				$q = "UPDATE `" . $this->table . "_translation` SET `name`='" . $name . "', `slug`='" . $slug . "', `meta_description`='" . $metaDescription . "', `seo_title`='" . $seoTitle .
					"', `accordion_header1`='" . $accordionHeader1 . "', `accordion_content1`='" . $accordionContent1 . "', `accordion_header2`='" . $accordionHeader2 . "', `accordion_content2`='" . $accordionContent2 . "', `accordion_header3`='" . $accordionHeader3 . "', `accordion_content3`='" . $accordionContent3 . "' ";
				$q.= "WHERE `translatable_id`='" . (int) $post['id'] . "' AND `locale`='" . $v['code'] . "' ";
				Cms::$db->update($q);
			} else {
				$q = "INSERT INTO `" . $this->table . "_translation` SET `name`='" . $name . "', `slug`='" . $slug . "', `meta_description`='" . $metaDescription . "', `seo_title`='" . $seoTitle .
						"', `accordion_header1`='" . $accordionHeader1 . "', `accordion_content1`='" . $accordionContent1 . "', `accordion_header2`='" . $accordionHeader2 . "', `accordion_content2`='" . $accordionContent2 . "', `accordion_header3`='" . $accordionHeader3 . "', `accordion_content3`='" . $accordionContent3 . "', ";
				$q.= "`translatable_id`='" . (int) $post['id'] . "', `locale`='" . $v['code'] . "' ";
				Cms::$db->update($q);
			}				

		}			

		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		return true;

	}	
	
	public function getCurrentLocaleSlug($slug, $locale = false) {
		$entity = $this->findBySlug($slug, null, $locale);

		if ($entity) {
			$trans = $this->getTranslation($entity['id']);		
			$localeSlug = $trans[Cms::$session->get('locale')]['slug'];
			return $localeSlug;
		}
		
		return false;
	}
	
	
	
	
	public function add22($post) {
		$post = maddslashes($post);
		$parentId = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0;
		$orderMax = $this->getMaxOrder($parentId);
		$order = $orderMax['0'] + 1;
		
		$item = array(						
			'parent_id' => $parentId,
			'status_id' => $post['status_id'],
			'order'		=> $order
		);

		$id = $this->insert($this->table, $item);		
		$this->convertToTranslationData($post);		

		if ($id) {
			foreach ($post as $locale => $trans) {
				$name = clearName($trans['name']);
				$slug = makeUrl($name);		

				$item = array(
					'translatable_id' => $id,					
					'name' => $name,					
					'slug' => $slug,					
					'locale' => $locale,
				);
				
				$this->insert($this->table . '_translation', $item);
			}
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
		}

		return $id;
	}	
		
	
	
	
	
	
	
	
	public function editAdmin($post) {
		$post = maddslashes($post);
		$post['name'] = clearName($post['name']);
		$post['name_url'] = makeUrl($post['name']);

		$q = "UPDATE " . $this->table . " SET "
				. "`status_id`='" . $post['status_id'] . "', `name`='" . $post['name'] . "', `name_url`='" . $post['name_url'] . "'"
				. "WHERE `id`='" . (int) $post['id'] . "' ";
		if (Cms::$db->update($q)) {
			return true;
		} else {
			return false;
		}
	}	
	
	
	
	
	
	
	
	
	
	public function load($url = '', $parent_id = 0) {
		if ($url) {
			$url = addslashes($url);
			$parent_id = addslashes($parent_id);
			
			$q = "SELECT a.* FROM `" . $this->table . "` a  ";
			$q.= "WHERE a.name_url='" . $url . "' ";
			if($parent_id > 0) $q.= "AND a.parent_id='" . (int) $parent_id . "' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}

	public function loadAll($submenu = 0) {
		$q = "SELECT * FROM `" . $this->table . "` a ";
		$q.= "WHERE a.parent_id=0 AND a.status_id IN (1,2) ";
        
        $order = '';
        
        if (isset(CMS::$conf['display_type']) && CMS::$conf['display_type'] == 1) {
            $order = "ORDER BY a.order ASC ";
        } else {            
            $order = "ORDER BY a.name ASC ";
        }
        
        $q.= $order;
        
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['url'] = URL . '/' . $GLOBALS['LANG']['url_shop'] . '/' . $v['name_url'] . '.html';
			if ($submenu == 1)
				$v['submenu'] = array();
			$items[$v['id']] = $v;
		}
		if ($submenu == 1) {
			$q = "SELECT * FROM `" . $this->table . "` a ";
			$q.= "WHERE a.parent_id>0 AND a.status_id IN (1,2) ";
			$q.= $order;
			$array = Cms::$db->getAll($q);
			foreach ($array as $v) {
				if(isset($items[$v['parent_id']])) {
					$v = mstripslashes($v);				
					$v['url'] = URL . '/' . $GLOBALS['LANG']['url_shop'] . '/' . $items[$v['parent_id']]['name_url'] . '/' . $v['name_url'] . '.html';
					$items[$v['parent_id']]['submenu'][] = $v;
				}
			}
		}
		return $items;
	}

	public function loadSiteMap() {
		$q = "SELECT d.name, d.url, a.type FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.lang_id='" . _ID . "' GROUP BY d.name ORDER BY a.group ASC, a.order ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
			$v['url'] = URL . '/' . $GLOBALS['LANG']['url_shop'] . '/' . $v['url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}
    
	public function loadCategoriesSelect($subcategories = 0) {
		$q = "SELECT * FROM `" . $this->table . "` a ";
		$q.= "WHERE a.parent_id='0' ORDER BY a.order ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['subcategories'] = array();
			$items[$v['id']] = $v;
		}
		if ($subcategories == 1) {
			$q = "SELECT * FROM `" . $this->table . "` a ";
			$q.= "WHERE a.parent_id!='0' ORDER BY a.order ASC ";
			$array = Cms::$db->getAll($q);
			foreach ($array as $v) {
				$v = mstripslashes($v);
				$items[$v['parent_id']]['subcategories'][] = $v;
			}
		}
		return $items;
	}
	
	public function getAll2($setIdAsKey = false) {
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
	
	public function getAll22() {
		
		$q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.locale, (SELECT `slug` FROM `" . $this->table . "` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` "
				. "FROM `" . $this->table . "` c "
				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id "
				. "ORDER BY c.`order`";

		$array = Cms::$db->getAll($q);
		$this->decorate($array);
	
		$grouped = $this->groupByTranslation($array, 'id');

		return $grouped;
	}	

}
