<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');

class FeatureValue extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'feature_values';
	}	
	
	public function getAll(array $params = []) {
		$q = "SELECT fv.*, t.translatable_id, t.name, t.locale "
				. "FROM `" . $this->table . "` fv "
				. "LEFT JOIN `" . $this->table . "_translation` t ON fv.id = t.translatable_id "
				. "LEFT JOIN `features_translation` ft ON ft.translatable_id = fv.feature_id ";			

		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 'fv';
				
				if ($key == 'locale') {
					$prefix = 't';
				}		
				
				if (is_array($value)) {
					$prefix = 'fv';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}								
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}		
		
		$q .= " ORDER BY ft.`name`, t.`name` ASC";

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

		if (!$post['feature_id']) {			
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_feature_not_selected']);
			return false;
		}

		$item = array(						
			'feature_id' => $post['feature_id']
		);	
					
		$entities = $this->getAll(['locale' => Cms::$session->get('locale')]);

		if ($entities) {
			foreach ($entities as $entity) {
				if ($entity['feature_id'] == $post['feature_id'] && $entity['name'] == clearName($post[Cms::$session->get('locale')]['name'])) {
					Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_feature_value_already_exists']);
					return false;
				}
			}
		}

		$id = $this->insert($this->table, $item);
	
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
		$localeEntities = $entities[Cms::$session->get('locale')];
//		dump($localeEntities);
////		dump($post[Cms::$session->get('locale')]['name']);
////		dump($id);
//		dump($entitBeforeChanged['trans'][Cms::$session->get('locale')]);
////		die;
		$newName = clearName($post[Cms::$session->get('locale')]['name']);
		
//		dump($entitBeforeChanged['trans'][Cms::$session->get('locale')]['name']);
//		dump($newName);
//		die;

		if ($localeEntities) {
			foreach ($localeEntities as $localeEntity) {
				if ($localeEntity['name'] == $newName && $entitBeforeChanged['trans'][Cms::$session->get('locale')] != $newName
						&& $localeEntity['id'] != $id ) {
					Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_feature_value_already_exists']);
					return false;
				}
			}
		}			
		


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
		
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
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
		if ($entity = $this->getBy(['id' => $id])[0]) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);

			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;		
		
		
		
		
		
		
//		$product = new Products();		
//		$productFeaturesIds = $product->getFeaturesIds();
//		
//		if (in_array($id, $productFeaturesIds)) {
//			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete_feature_assigned_already']);			
//			return false;
//		}				
//		
//		if ($entity = $this->getBy(['id' => $id])[0]) {
//			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
//			Cms::$db->delete($q);
//
//			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
//			return true;
//		}
//
//		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
//		return false;
	}	


}
