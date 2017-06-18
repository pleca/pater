<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/Variation.php');

class Product extends BaseModel {

    const IMAGE_DIR = '/files/product/';
    public $table;
	public $decorate;

	public function __construct($params = []) {
		$this->table = DB_PREFIX . 'product';
		if (isset($params['decorate'])) {
			$this->decorate = $params['decorate'];
		}
	}

	public function getAll(array $params = [], $fields = []) {

		$productColumns = $this->getTableFields($this->table);	
		$translationColumns = array_diff($this->getTableFields($this->table . '_translation'), ['id']);

		if ($fields) {
			$lastField = end($fields);
			
			$q = "SELECT p.id, t.locale,"; 
			
			if ($this->decorate && isset($params['locale'])) {
				$q .= "(SELECT `slug` FROM `categories_translation` WHERE translatable_id=c.parent_id AND locale='" . $params['locale'] ."' LIMIT 1) as `parent_url`,";	
				$q .= "ct.slug as `category_url`, t.slug,";	
			}						

			foreach ($fields as $field) {
				if (in_array($field, $productColumns)) {
					$q .= "p." . $field;
				}
				
				if (in_array($field, $translationColumns)) {
					$q .= "t." . $field;
				}
				
				if ($field !== $lastField) {
					$q .= ",";
				}				
			}
			
			$q .= " ";
			
		} else {
			$q = "SELECT p.*,t.translatable_id, t.content, t.content_short, t.locale";			
			
			if ($this->decorate && isset($params['locale'])) {
				$q .= ",(SELECT `slug` FROM `categories_translation` WHERE translatable_id=c.parent_id AND locale='" . $params['locale'] ."' LIMIT 1) as `parent_url`,";	
				$q .= "ct.slug as `category_url`, t.slug";	
			}

			$q .= " ";			
		}
		
		$q .= "FROM `" . $this->table . "` p "
			. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id ";
		
		if ($this->decorate && isset($params['locale'])) {
			$q .= "LEFT JOIN `categories` c ON p.category_id = c.id "	
			. "LEFT JOIN `categories_translation` ct ON p.category_id = ct.translatable_id and ct.locale='" . $params['locale'] ."' ";	
		}		

		
//(SELECT `slug` FROM `categories_translation` WHERE translatable_id=c.parent_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `parent_url``
//	. `			
		$limit = false;
		
		if (isset($params['limit'])) {
			$limit = $params['limit'];
			unset($params['limit']);
		}
		
		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 'p';
				
				if (in_array($key, $translationColumns)) {
					$prefix = 't';
				}		
				
				$pos = strpos($key, 'date');
				
				if ($pos !== false) {
					if (is_array($value)) {
						
						if (isset($value['from']) && isset($value['to'])) {
							$q .= "`" . $key . "`>='" . addslashes($value['from']) . " 00:00:00' ";
							$q .= "AND `" . $key . "`<='" . addslashes($value['to']) . " 23:59:59' ";
							
						} elseif (isset($value['from'])) {
							$q .= "`" . $key . "`>='" . addslashes($value['from']) . " 00:00:00' ";
						} elseif (isset($value['to'])) {
							$q .= "`" . $key . "`<='" . addslashes($value['to']) . " 23:59:59' ";
						}
					}
				} elseif (is_array($value)) {
					$prefix = 'p';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}				
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}		
		
//		$q .= " ORDER BY c.`order` ASC";
		if ($limit) {
			$q .= " LIMIT " . $limit;
		}


        // 1. czyli narazie mam produkty bez wariacji (602)
        // $entities
        // [0]=>[id=>1,category_id=>8,...]
        // [1]=>[id=>2,category_id=>12,...]
		$entities = Cms::$db->getAll($q);
		
		$variation = new Variation();
		$productVariations = [];



        // 2. tu ściągam wszystkie wariacje jedna po drugiej (2802)
        // $variations
        // [0]=>[id2=>1,product_id=>1,sku=>1234]
        // [1]=>[id2=>2,product_id=>1,sku=>46454]
		if (isset($params['id'])) {
			$variations = $variation->getBy(['product_id' => $params['id']]);
		} else {
			$variations = $variation->getAll();						
		}
		
		if ($variations) {
            // 3. tu tworzę tymczasową tablicę wariacji, każdą wariację wrzucam do
            // klucza $variation['product_id']
            // $productVariations (600)
            //[1]=>[
            // [0]=>[id2=>1,product_id=>1,sku=>1234]
            // [1]=>[id2=>2,product_id=>1,sku=>46454]
            //]
			foreach ($variations as $variation) {
				$productVariations[$variation['product_id']][] = $variation;
			}
            // 4. tu wartści elementów powyższej tablicy z wariacjami wrzucam do
            // tablicy produktów $entity pod kluczem 'variations'
            // $entity
			foreach ($entities as &$entity) {
				if (isset($productVariations[$entity['id']])) {
					$entity['variations'] = $productVariations[$entity['id']];
				}				
			}
		}

		if (!isset($params['locale'])) {
			$entities = $this->groupByTranslation($entities, 'id');
		}				

		if ($this->decorate) {
			$this->decorate($entities);
		}
		
		return $entities;
	}
	
	public function decorate(&$entities) {
		if ($entities) {
			foreach ($entities as &$entity) {
                if ($entity['parent_url']) {
                    $entity['url'] = URL . '/product/' . $entity['parent_url'] . '/' . $entity['category_url'] . '/' . $entity['slug'] . '.html';
                } else {
                    $entity['url'] = URL . '/product/' . $entity['category_url'] . '/' . $entity['slug'] . '.html';
                }				
			}
		}
	}
    
   public function count() {
       $q = "SELECT COUNT(*) as rows FROM `" . $this->table . "`";
       $result = Cms::$db->getRow($q);
       
       return $result['rows'];
   }
	
//	public function getAll(array $params = []) {
//		$q = "SELECT p.*, t.translatable_id, t.content, t.content_short, t.locale "
//				. "FROM `" . $this->table . "` p "
//				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
//				. "ORDER BY p.`id` ";
//
//		$array = Cms::$db->getAll($q);
//
//		$grouped = $this->groupByTranslation($array, 'id');
//		
//		return $grouped;
//	}	
}
