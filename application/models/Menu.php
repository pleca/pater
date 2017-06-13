<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');

class Menu extends BaseModel {

	public static $availableModules = array(
		'index', 'admin', 'contact-form', 'gallery', 'news', 
		'newsletter', 'basket', 'shop', 'bestsellers', 'recommended', 
		'promotions', 'products-clearance', 'new', 'categories', 'producers'); 
	
	public static $menuGroups = array('top', 'left', 'bottom');
	
	const TYPE_URL = 'url';
	const TYPE_MODULE = 'module';
	const TYPE_PAGE = 'page';
	
	public $table;
	public $tableDesc;
	public $tableModules;
	public $tablePages;

	public function __construct() {
		$this->table = DB_PREFIX . 'menu';
		$this->tableModules = DB_PREFIX . 'modules';
		$this->tablePages = DB_PREFIX . 'pages';
	}

	public function getAll(array $params = []) {
		
		$q = "SELECT m.*,t.translatable_id, t.name, t.url, t.locale "
				. "FROM `" . $this->table . "` m "
				. "LEFT JOIN `" . $this->table . "_translation` t ON m.id = t.translatable_id ";
				
		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 'm';
				
				if ($key == 'locale') {
					$prefix = 't';
				}		
				
				$q .= "$prefix.$key = '" .$value ."' ";
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}
		

		
		$q .= " ORDER BY m.`order` ASC";

		$menus = Cms::$db->getAll($q);
		$this->decorate($menus);

		//get submenus
		$q = "SELECT m.id, m.parent_id, t.name, t.url, m.type, t.locale FROM `" . $this->table . "` m LEFT JOIN `" . $this->table . "_translation` t ON m.id=t.translatable_id ";
		$q.= "WHERE m.parent_id != '0' AND m.group='" . $params['group'] . "' ORDER BY m.order ASC ";
		$submenus = Cms::$db->getAll($q);
		$this->decorate($submenus);
		
		if ($submenus) {
			foreach ($menus as &$menu) {
				foreach ($submenus as $submenu) {
					if ($menu['id'] == $submenu['parent_id'] && $menu['locale'] == $submenu['locale']) {
						$menu['submenu'][] = $submenu;
					}
				}
			}			
		}

		if (!isset($params['locale'])) {
			$menus = $this->groupByTranslation($menus, 'id');
		}				

		return $menus;
	}
	
	protected function decorate(array &$entities) {
		if (!$entities) {
			return false;
		}
		
		foreach ($entities as &$entity) {			
			switch ($entity['type']) {
				case Menu::TYPE_MODULE:
					$entity['module_name'] = $entity['url'];
					
					if ($entity['url'] == 'index') {
						$entity['url'] = URL . '/';
					} else {
						$entity['url'] = URL . '/' . $entity['url'] . '.html';
					}
					break;
				default:
					if ($entity['type'] != Menu::TYPE_URL) {
						$entity['url'] = URL . '/' . $entity['url'] . '.html';
					}
					break;
			}			
		}
	}

	public function add($post) {
		if (isset($post['type'])) {
			$orderMax = $this->getMaxOrder($post['parent_id'], $post['group']);
			$orderMax = $orderMax['0'] + 1;

            if ($orderMax == 256) {
				Cms::getFlashBag()->add('error', 'Przekroczono limit elementow! Skontaktuj się z administratorem admin@idea4me.pl');
                return false;                
            }
            
			$q = "INSERT INTO " . $this->table . " SET `parent_id`='" . $post['parent_id'] . "', `type`='" . $post['type'] . "', `group`='" . $post['group'] . "', ";
			$q.= "`order`='" . $orderMax . "' ";
			$id = Cms::$db->insert($q);

			foreach (Cms::$langs as $v) {
				$name = addslashes($post[$v['code']]['name']);
				
				switch ($post['type']) {
					case 'url':
						$url = $post[$v['code']]['url_www'];
						break;
					case 'module': 
						$url = $post['url_module'];
						break;
					case 'page': 
						$url = $post[$v['code']]['url_page'];
						break;					
				}

				$q = "INSERT INTO `" . $this->table . "_translation` SET `name`='" . $name . "', `url`='" . $url . "', ";
				$q.= "`translatable_id`='" . $id . "', `locale`='" . $v['code'] . "' ";
				Cms::$db->insert($q);
			}
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']); 
			return true;
		}
		else {
			Cms::getFlashBag()->add('error', 'Proszę wybrać typ linka!');
			return false;
		}
	}
	
	public function getMaxOrder($parentId, $group) {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " WHERE `group`='" . $group . "' AND `parent_id`='" . $parentId . "' ";
		return Cms::$db->max($q);
	}	

	public function deleteById($id) {

		if ($entity = $this->getBy(['id' => $id])[0]) {

			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $entity['id'] . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $entity['id'] . "' ";
			Cms::$db->delete($q);            
            
            //find and delete children
            $q = "SELECT `id` FROM " . $this->table . " WHERE `parent_id`='" . (int) $entity['id'] . "' ";
            $array = Cms::$db->getAll($q);

            $children = [];
            if (is_array($array)) {
                foreach ($array as $v) {
                    $children[] = $v['id'];
                }                
            }
            
            if ($children) {
                $children = implode(',', $children);
                $q = "DELETE FROM " . $this->table . " WHERE `id` IN ($children) ";

                Cms::$db->delete($q);

                $q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id` IN ($children) ";
                Cms::$db->delete($q);                
            }
            //end
            
            $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $entity['order'] . "' AND `parent_id`='" . $entity['parent_id'] . "' AND `group`='" . $entity['group'] . "' ";
			Cms::$db->update($q);

			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']); 
			return true;
		}        
        		
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
	
	public function moveDown($id) {
		if ($entity = $this->getBy(['id' => $id])[0]) {
			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 ";
			$q.= "WHERE `group`='" . $entity['group'] . "' AND `parent_id`='" . $entity['parent_id'] . "' AND `order`='" . ($entity['order'] + 1) . "'";
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

	public function moveUp($id) {
		if ($entity = $this->getBy(['id' => $id])[0]) {
			if ($entity['order'] > 1) {
				$q = "UPDATE " . $this->table . " SET `order`=`order`+1 ";
				$q.= "WHERE `group`='" . $entity['group'] . "' AND `parent_id`='" . $entity['parent_id'] . "' AND `order`='" . ($entity['order'] - 1) . "'";
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
	
	public function getMenu($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

		return $result;
	}
	
	public function getById($id) {
		
		$menu = $this->getMenu($id);

		$result = [];		
		$result = $menu;

		$result['trans'] = $this->getTranslation($id);		
		
		return $result;
	}
	
	public function getTranslation($id) {
		$q = "SELECT m.*,t.translatable_id, t.name, t.url, t.locale FROM `" . $this->table . "` m "
				. "LEFT JOIN `" . $this->table . "_translation` t ON m.id = t.translatable_id "
				. "WHERE m.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		return $result;
	}
	
	public function edit($post) {				

		if (isset($post['type'])) {						
			
			$q = "UPDATE " . $this->table . " SET `type`='" . $post['type'] . "', `group`='" . $post['group'] . "' WHERE `id`='" . (int) $post['id'] . "' ";
			Cms::$db->update($q);

			$entities = $this->getAll(['group' => $post['group']]);	
			
			$id = $post['id'];
			
			foreach (Cms::$langs as $v) {
				$name = addslashes($post[$v['code']]['name']);
				
				switch ($post['type']) {
					case 'url':
						$url = $post[$v['code']]['url_www'];
						break;
					case 'module': 
						$url = $post['url_module'];
						break;
					case 'page': 
						$url = $post[$v['code']]['url_page'];
						break;					
				}

				if ($this->existsTranslation($id, $v['code'], $entities)) {
					$q = "UPDATE `" . $this->table . "_translation` SET `name`='" . $name . "', `url`='" . $url . "' ";
					$q.= "WHERE `translatable_id`='" . (int) $post['id'] . "' AND `locale`='" . $v['code'] . "' ";
					Cms::$db->update($q);
				} else {
 					$q = "INSERT INTO `" . $this->table . "_translation` SET `name`='" . $name . "', `url`='" . $url . "', ";
					$q.= "`translatable_id`='" . (int) $post['id'] . "', `locale`='" . $v['code'] . "' ";
					Cms::$db->update($q);
				}				
				
			}			

			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
			return true;
		} else {
			
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_link_type']);
			return false;
		}
	}
	
	public function updateTranslation($id, $locale, $item) {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
		$where = $this->where(["translatable_id" => $id, 'locale' => $locale]);
		return $this->update($this->table . '_translation', $where, $item);
	}		
	
//	public function deleteById($id) {
//		if ($id) {
//			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//			$params['info'] = $GLOBALS['LANG']['info_delete'];
//			
//			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
//			return true;
//		}
//
//		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
//		return false;
//	}

//	function deleteAdmin($id) {
//
//		if ($item = $this->loadByIdAdmin($id)) {
//			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $item['id'] . "' ";
//			Cms::$db->delete($q);
//			$q = "DELETE FROM " . $this->tableDesc . " WHERE `parent_id`='" . (int) $item['id'] . "' ";
//			Cms::$db->delete($q);            
//            
//            //find and delete children
//            $q = "SELECT `id` FROM " . $this->table . " WHERE `parent_id`='" . (int) $item['id'] . "' ";
//            $array = Cms::$db->getAll($q);
//
//            $children = [];
//            if (is_array($array)) {
//                foreach ($array as $v) {
//                    $children[] = $v['id'];
//                }                
//            }
//            
//            if ($children) {
//                $children = implode(',', $children);
//                $q = "DELETE FROM " . $this->table . " WHERE `id` IN ($children) ";
//
//                Cms::$db->delete($q);
//
//                $q = "DELETE FROM " . $this->tableDesc . " WHERE `parent_id` IN ($children) ";
//                Cms::$db->delete($q);                
//            }
//            //end
//            
//            $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `parent_id`='" . $item['parent_id'] . "' AND `group`='" . $item['group'] . "' ";
//			Cms::$db->update($q);
//
//			return true;
//		}        
//        
//		return false;
//	}


	
	
	public function load($group = '') {
		$q = "SELECT m.id, t.name, t.url, m.type FROM `" . $this->table . "` m LEFT JOIN `" . $this->table . "_translation` t ON m.id = t.translatable_id ";
		$q.= "WHERE m.parent_id='0' AND m.group='" . $group . "' AND t.locale='" . LOCALE . "' ORDER BY m.order ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
            
            if ($v['type'] == 'module') {
                $v['module_name'] = $v['url'];
            }
            
			if ($v['type'] == 'module' AND $v['url'] == 'index') {
				$v['url'] = URL . '/';
			}
			elseif ($v['type'] != 'url') {
				$v['url'] = URL . '/' . $v['url'] . '.html';
			}
			$v['submenu'] = array();
			$items[$v['id']] = $v;
		}

		$q = "SELECT m.id, m.parent_id, t.name, t.url, m.type FROM `" . $this->table . "` m LEFT JOIN `" . $this->table . "_translation` t ON m.id=t.translatable_id ";
		$q.= "WHERE m.parent_id != '0' AND m.group='" . $group . "' AND t.locale='" . LOCALE . "' ORDER BY m.order ASC ";
		$array = Cms::$db->getAll($q);
		
//		dump($array);
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
			if ($v['type'] == 'module' AND $v['url'] == 'index') {
				$v['url'] = URL . '/';
			}
			elseif ($v['type'] != 'url') {
				$v['url'] = URL . '/' . $v['url'] . '.html';
			}
			$items[$v['parent_id']]['submenu'][] = $v;
		}
		return $items;
	}
	
	
	public function loadOld($group = '') {
		$q = "SELECT a.id, d.name, d.url, a.type FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.parent_id='0' AND a.group='" . $group . "' AND d.lang_id='" . _ID . "' ORDER BY a.order ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
            
            if ($v['type'] == 'module') {
                $v['module_name'] = $v['url'];
            }
            
			if ($v['type'] == 'module' AND $v['url'] == 'index') {
				$v['url'] = URL . '/';
			}
			elseif ($v['type'] != 'url') {
				$v['url'] = URL . '/' . $v['url'] . '.html';
			}
			$v['submenu'] = array();
			$items[$v['id']] = $v;
		}

		$q = "SELECT a.id, a.parent_id, d.name, d.url, a.type FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.parent_id!='0' AND a.group='" . $group . "' AND d.lang_id='" . _ID . "' ORDER BY a.order ASC ";
		$array = Cms::$db->getAll($q);
		foreach ($array as $v) {
			$v['name'] = stripslashes($v['name']);
			if ($v['type'] == 'module' AND $v['url'] == 'index') {
				$v['url'] = URL . '/';
			}
			elseif ($v['type'] != 'url') {
				$v['url'] = URL . '/' . $v['url'] . '.html';
			}
			$items[$v['parent_id']]['submenu'][] = $v;
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
			if ($v['type'] == 'module' AND $v['url'] == 'index')
				$v['url'] = URL . '/';
			elseif ($v['type'] != 'url')
				$v['url'] = URL . '/' . $v['url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

}
