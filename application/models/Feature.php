<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/FeatureValue.php');

class Feature extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'features';
	}	
	
	public function getAll(array $params = []) {
		$q = "SELECT f.*, t.translatable_id, t.name, t.locale "
				. "FROM `" . $this->table . "` f "
				. "LEFT JOIN `" . $this->table . "_translation` t ON f.id = t.translatable_id ";				

		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 'f';
				
				if ($key == 'locale') {
					$prefix = 't';
				}		
				
				if (is_array($value)) {
					$prefix = 'f';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}								
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}		
		
		$q .= " ORDER BY t.`name` ASC";

		$entities = Cms::$db->getAll($q);
		
		if (!isset($params['locale'])) {
			$entities = $this->groupByTranslation($entities, 'id');
		}		
		
		return $entities;
	}
	
	public function getById($id) {
		
		$feature = $this->getFeature($id);

		$result = [];		
		$result = $feature;
		$result['trans'] = $this->getTranslation($id);
		
		return $result;
	}
	
	public function getFeature($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

		return $result;
	}
	
	public function getTranslation($id) {
		$q = "SELECT f.*,t.translatable_id, t.name, t.locale FROM `" . $this->table . "` f "
				. "LEFT JOIN `" . $this->table . "_translation` t ON f.id = t.translatable_id "
				. "WHERE f.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		return $result;
	}	
	
	public function add($post) {
		$post = maddslashes($post);

		$q = "INSERT INTO `" . $this->table . "` VALUES(null)";
		$id = Cms::$db->insert($q);
	
		$this->convertToTranslationData($post);		

		if ($id) {
			foreach ($post as $locale => $trans) {
				$name = clearName($trans['name']);

				$item = array(
					'translatable_id' => $id,					
					'name' => $name,				
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
	
	public function edit($post) {
		$id = $post['id'];
		$entitBeforeChanged = $this->getById($post['id']);
		$this->convertToTranslationData($post);

		$entities = $this->getAll();

		foreach ($post as $locale => $trans) {
			$name = clearName($trans['name']);

			$item = array(
				'translatable_id' => $id,
				'name' => $name,					
				'locale' => $locale,
			);

			if ($this->existsTranslation($id, $locale, $entities)) {
				$this->updateTranslation($id, $locale, $item);											
			} else {					
				$this->insert($this->table . '_translation', $item);
			}

		}
		
		return true;
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

	public function deleteById($id) {	
		$product = new Products();		
		$productFeaturesIds = $product->getFeaturesIds();
		
		if (in_array($id, $productFeaturesIds)) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete_feature_assigned_already']);			
			return false;
		}				
		
		if ($entity = $this->getBy(['id' => $id])[0]) {

			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			
			$this->deleteFeatureValues($id);

			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}	
	
	protected function deleteFeatureValues($featureId) {

		$featureValue = new FeatureValue();
		$entities = $featureValue->getAll(['feature_id' => $featureId, 'locale' => Cms::$session->get('locale')]);	

		if ($entities) {
			$entities = getArrayByKey($entities, 'id');
			
			$ids = array_keys($entities);
			$ids = implode(',', $ids);

			$q = "DELETE FROM `" . DB_PREFIX . "feature_values` WHERE `id` IN(" . $ids . ")";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . DB_PREFIX . "feature_values_translation` WHERE `translatable_id` IN(" . $ids . ")";
			Cms::$db->delete($q);				
		}
	}
	
	
	
	
	
	
	
	
	

//	
//	
//	
//	public function deleteById($id) {
//		
//		if ($entity = $this->getBy(['id' => $id])[0]) {
//			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $entity['order'] . "' AND `parent_id`='" . $entity['parent_id'] . "' ";
//			Cms::$db->update($q);
//			
//			$this->removeParentChildren($entity);
//
//			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
//			return true;
//		}
//
//		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
//		return false;
//	}
//	
//	public function getCategory($id) {
//		if (!$id) {
//			return false;
//		}
//		
//		$q = "SELECT * FROM `" . $this->table ."` "
//				. "WHERE `id` = '" . (int) $id . "' ";
//		
//		$result = Cms::$db->getRow($q);	
//
//		return $result;
//	}
//	
//	public function getTranslation($id) {
//		$q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.locale FROM `" . $this->table . "` c "
//				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id "
//				. "WHERE c.id = '" . (int) $id . "' ";
//
//		$result = Cms::$db->getAll($q);			
//		$result = getArrayByKey($result, 'locale');
//
//		return $result;
//	}
//	
//	public function getById($id) {
//		
//		$category = $this->getCategory($id);
//
//		$result = [];		
//		$result = $category;
//		$result['trans'] = $this->getTranslation($id);
//		
//		return $result;
//	}	
//	
//	public function edit($post) {				
//
//		$q = "UPDATE " . $this->table . " SET `status_id`='" . $post['status_id'] . "', `parent_id`='" . $post['parent_id'] . "' WHERE `id`='" . (int) $post['id'] . "' ";
//		Cms::$db->update($q);
//
//		$entities = $this->getAll(['parent_id' => $post['parent_id']]);			
//		$id = $post['id'];
//
//		foreach (Cms::$langs as $v) {
//			$name = addslashes($post[$v['code']]['name']);
//			$slug = makeUrl($name);		
//
//
//			if ($this->existsTranslation($id, $v['code'], $entities)) {
//				$q = "UPDATE `" . $this->table . "_translation` SET `name`='" . $name . "', `slug`='" . $slug . "' ";
//				$q.= "WHERE `translatable_id`='" . (int) $post['id'] . "' AND `locale`='" . $v['code'] . "' ";
//				Cms::$db->update($q);
//			} else {
//				$q = "INSERT INTO `" . $this->table . "_translation` SET `name`='" . $name . "', `slug`='" . $slug . "', ";
//				$q.= "`translatable_id`='" . (int) $post['id'] . "', `locale`='" . $v['code'] . "' ";
//				Cms::$db->update($q);
//			}				
//
//		}			
//
//		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//		return true;
//
//	}	
//	
//	public function getCurrentLocaleSlug($slug) {
//		$entity = $this->findBySlug($slug);
//
//		if ($entity) {
//			$trans = $this->getTranslation($entity['id']);		
//			$localeSlug = $trans[Cms::$session->get('locale')]['slug'];
//			return $localeSlug;
//		}
//		
//		return false;
//	}
//	
//	
//
//	
//	public function editAdmin($post) {
//		$post = maddslashes($post);
//		$post['name'] = clearName($post['name']);
//		$post['name_url'] = makeUrl($post['name']);
//
//		$q = "UPDATE " . $this->table . " SET "
//				. "`status_id`='" . $post['status_id'] . "', `name`='" . $post['name'] . "', `name_url`='" . $post['name_url'] . "'"
//				. "WHERE `id`='" . (int) $post['id'] . "' ";
//		if (Cms::$db->update($q)) {
//			return true;
//		} else {
//			return false;
//		}
//	}	
//	
//	
//	
//	public function getAll2($setIdAsKey = false) {
//        if ($setIdAsKey) {
//            $entities = $this->select($this->table);
//            $list = [];
//            foreach ($entities as $entity) {
//                $list[$entity['id']] = $entity;
//            }
//            
//            return $list;
//        }
//		return $this->select($this->table);
//	}    
//	
//	public function getAll22() {
//		
//		$q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.locale, (SELECT `slug` FROM `" . $this->table . "` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` "
//				. "FROM `" . $this->table . "` c "
//				. "LEFT JOIN `" . $this->table . "_translation` t ON c.id = t.translatable_id "
//				. "ORDER BY c.`order`";
//
//		$array = Cms::$db->getAll($q);
//		$this->decorate($array);
//	
//		$grouped = $this->groupByTranslation($array, 'id');
//
//		return $grouped;
//	}	

}
