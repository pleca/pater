<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
    die('No access to files!');
}

require_once(MODEL_DIR . '/BasketModel.php');
require_once(CLASS_DIR . '/ShoppingThresholdsHelper.php');

class Products extends BaseModel {

    public $table;
    public $dir;
    public $url;		

    public function __construct() {
        $this->table = DB_PREFIX . 'product';
        $this->tableVariations = DB_PREFIX . 'product_variation';
        $this->dir = CMS_DIR . '/files/product';
        $this->url = CMS_URL . '/files/product';
    }

    public function getFeaturesIds() {
        $entities = $this->getAll();
		
		$featuresIds = [];
		if ($entities) {
			foreach ($entities as $entity) {
				$featuresIds[] = $entity['feature1_id'];
				$featuresIds[] = $entity['feature2_id'];
				$featuresIds[] = $entity['feature3_id'];
			}
		}

		$featuresIds = array_unique(array_filter($featuresIds));	
		
		return $featuresIds;
    }
	
    public function getFeaturesValuesIds() {
        $entities = $this->getAll();
		//todo
		dump($entities);
//		
//		$featuresIds = [];
//		if ($entities) {
//			foreach ($entities as $entity) {
//				$featuresIds[] = $entity['feature1_id'];
//				$featuresIds[] = $entity['feature2_id'];
//				$featuresIds[] = $entity['feature3_id'];
//			}
//		}
//
//		$featuresIds = array_unique(array_filter($featuresIds));	
//		
//		return $featuresIds;
    }
	
    public function getFeatures() {
        $entities = $this->getAll();
		
		$features= [];
		if ($entities) {
			foreach ($entities as $entity) {
				$features[] = $entity['feature1_name'];
				$features[] = $entity['feature2_name'];
				$features[] = $entity['feature3_name'];
			}
		}

		$features = array_unique(array_filter($features));	
		
		return $features;
    }

    public function getById($id = 0, $variation_id = 0) {
        if (!$id OR !$variation_id) {
            return false;
        }

        $id = addslashes($id);
        $variation_id = addslashes($variation_id);
        
        $locale  = Cms::$session->get('locale') ? Cms::$session->get('locale') : Cms::$defaultLocale;

        $q = "SELECT p.*, pt.name, pt.slug as 'name_url', v.*, ct.slug as `category_url`, pt.content_short, pt.content, "
                . "(SELECT `value` FROM `" . DB_PREFIX . "taxes` WHERE `id`=v.tax_id LIMIT 1) as `tax`, "
                . "(SELECT `file` FROM `" . $this->table . "_image` WHERE `product_id`=p.id ORDER BY `order` ASC LIMIT 1) as `file`, "
                . "(SELECT `file` FROM `" . $this->table . "_image` WHERE `product_id`='".(int) $id."' AND variation_id = '" . (int) $variation_id . "' ORDER BY `order` ASC LIMIT 1) as `file_variation`, "
                . "(SELECT `slug` FROM `categories_translation` WHERE `translatable_id`=c.parent_id LIMIT 1) as `parent_url`, "
				. "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='".$locale."' LIMIT 1) as `feature1_name`, "
				. "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='".$locale."' LIMIT 1) as `feature2_name`, "
				. "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='".$locale."' LIMIT 1) as `feature3_name`, "				
				. "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='".$locale."' LIMIT 1) as `feature1_value`, "
				. "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='".$locale."' LIMIT 1) as `feature2_value`, "
				. "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='".$locale."' LIMIT 1) as `feature3_value` "				
                . "FROM `" . $this->table . "` p "
				. "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id AND pt.locale = '". $locale . "' "				
				. "LEFT JOIN `categories` c ON p.category_id=c.id "
				. "LEFT JOIN `categories_translation` ct ON p.category_id=ct.translatable_id AND ct.locale = '". $locale . "' "				
                . "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id "
                . "WHERE p.id='" . (int) $id . "' AND v.id2= '" . (int) $variation_id . "' ";
        
        if ($item = Cms::$db->getRow($q)) {
            $item = mstripslashes($item);
 
            $logged = Customer::isLogged();

            if (CMS::$modules['price_groups'] && $logged) {
                if ($_SESSION[CUSTOMER_CODE]['price_group'] != 1) {
                    $item['price'] = $item['price' . $_SESSION[CUSTOMER_CODE]['price_group']];
                }
            }
            
            if (CMS::$modules['price_groups'] && !$logged) {
                $v['price'] = 0;
            }            
            
            if ($item['promotion'] == 1) {
                $item['price'] = $item['price_promotion'];
            }
            $item['url'] = URL . '/' . $GLOBALS['LANG']['url_product'] . '/';
            if ($item['parent_url']) {
                $item['url'] .= $item['parent_url'] . '/';
            }
            $item['url'] .= $item['category_url'] . '/' . $item['name_url'] . '.html';

            if ($item['file_variation']) {
                $item['photo'] = $this->getSrc($item['file_variation']);
            } else {
                $item['photo'] = $this->getSrc($item['file']);
            }
        
            return $item;
        }
        return false;
    }

    public function updateVariationById($id = '', $item = '') {
        if (!$id OR ! $item) {
            return false;
        }
        $id = addslashes($id);
        $item = maddslashes($item);

        $q = "UPDATE `" . $this->table . "_variation` SET `qty`='" . $item['qty'] . "' WHERE `id2`='" . $id . "' ";
        if (Cms::$db->update($q)) {
            return true;
        } else {
            return false;
        }
    }

    public function updateById($id = '', $item = '') {
        if (!$id OR ! $item) {
            return false;
        }
        $id = addslashes($id);
        $item = maddslashes($item);

        $q = "UPDATE `" . $this->table . "` SET `qty`='" . $item['qty'] . "' WHERE `id`='" . $id . "' ";
        if (Cms::$db->update($q)) {
            return true;
        } else {
            return false;
        }
    }

    public function findBy(array $params = []) {

        if (isset($params['slug'])) {
            $categoryId = addslashes($params['category_id']);
            $slug = addslashes($params['slug']);

            $q = "SELECT p.*, pt.name, pt.slug, pt.seo_title, pt.content_short, pt.content, ";
            $q.= "(SELECT `name` FROM `" . $this->table . "_manufacturer` WHERE `id`=p.producer_id LIMIT 1) as `producer`, ";
            $q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `feature1_name`, ";
            $q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `feature2_name`, ";
            $q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `feature3_name` ";
            $q.= "FROM `" . $this->table . "` p ";
			$q.= "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id AND pt.locale = '". Cms::$session->get('locale') . "' ";
            $q.= "WHERE p.category_id='" . (int) $categoryId . "' AND pt.slug='" . $slug . "' ";

            if ($v = Cms::$db->getRow($q)) {
                $v = mstripslashes($v);

                $v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
                $v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
                $v['tag3_url'] = str_replace(' ', '-', $v['tag3']);

                return $v;
            } else {
                return false;
            }
        }
        return false;
	}
	
	public function findBySlug($slug = null) {
		if (!$slug) {
			return false;
		}
		
		$q = "SELECT p.*,t.translatable_id, t.name, t.slug, t.locale "
				. "FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "	
				. "WHERE t.slug = '" . $slug . "' ";

		$result = Cms::$db->getRow($q);

		return $result;
	}
	
	public function getCurrentLocaleSlug($slug) {
		$entity = $this->findBySlug($slug);

		if ($entity) {
			$trans = $this->getTranslation($entity['id']);		

			$localeSlug = $trans[Cms::$session->get('locale')]['slug'];
			return $localeSlug;
		}
		
		return false;
	}	
	
	public function getTranslation($id) {
		$q = "SELECT p.*,t.translatable_id, t.name, t.slug, t.locale FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "WHERE p.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		return $result;
	}	
	
    public function load($kat_id = 0, $name_url = '') {
        if ($name_url) {
            $kat_id = addslashes($kat_id);
            $name_url = addslashes($name_url);

            $q = "SELECT p.*, ";
            $q.= "(SELECT `name` FROM `" . $this->table . "_manufacturer` WHERE `id`=p.producer_id LIMIT 1) as `producer` ";
            $q.= "FROM `" . $this->table . "` p ";
            $q.= "WHERE p.category_id='" . (int) $kat_id . "' AND p.name_url='" . $name_url . "' ";

            if ($v = Cms::$db->getRow($q)) {
                $v = mstripslashes($v);

                $v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
                $v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
                $v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
                
                return $v;
            } else {
                return false;
            }
        }
        return false;
    }

	protected function getQuery($resultType, array $params = []) {
		if (!$resultType) {
			return false;
		}

		switch($resultType) {
			case 'search':
				$q = "SELECT p.*, pt.name as `name`, pt.slug as 'name_url', pt.`locale`, ct.slug as `category_url`, v.ean, v.price_rrp, v.price, v.price2, v.price3, v.price_promotion, v.promotion, t.value as `tax`, ";
				$q.= "IF(v.promotion='1', (v.price_promotion + v.price_promotion * t.value / 100), (v.price + v.price * t.value / 100)) as `price_sort`, ";
				$q.= "(SELECT `slug` FROM `categories_translation` WHERE translatable_id=c.parent_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `parent_url`, ";
				$q.= "(SELECT `file` FROM `" . $this->table . "_image` WHERE `product_id`=p.id ORDER BY `variation_id`, `order` ASC LIMIT 1) as file ";								
				$q.= "FROM `" . $this->table . "` p ";
				$q.= "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id ";
				$q.= "LEFT JOIN `categories` c ON p.category_id=c.id ";
				$q.= "LEFT JOIN `categories_translation` ct ON ct.translatable_id = p.category_id ";
				$q.= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
				$q.= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";								
				break;
			case 'list':		
				$q = "SELECT p.*, pt.name as `name`, pt.slug as 'name_url', pt.`locale`, ct.slug as `category_url`, v.price_rrp, v.price, v.price2, v.price3, v.price_promotion, v.promotion, t.value as `tax`, ";
				$q.= "IF(v.promotion='1', (v.price_promotion + v.price_promotion * t.value / 100), (v.price + v.price * t.value / 100)) as `price_sort`, ";
				$q.= "(SELECT max(mega_offer) FROM `" . $this->tableVariations . "` v WHERE v.`product_id`=p.id) as `mega_offer`, ";
				$q.= "(SELECT `slug` FROM `categories_translation` WHERE translatable_id=c.parent_id AND locale='" . Cms::$session->get('locale') ."' LIMIT 1) as `parent_url`, ";
				$q.= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature1_value`, ";
				$q.= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature2_value`, ";
				$q.= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature3_value`, ";			
				$q.= "(SELECT `file` FROM `" . $this->table . "_image` WHERE `product_id`=p.id ORDER BY `variation_id`, `order` ASC LIMIT 1) as file ";				
				$q.= "FROM `" . $this->table . "` p ";
				$q.= "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id ";
				$q.= "LEFT JOIN `categories` c ON p.category_id=c.id ";
				$q.= "LEFT JOIN `categories_translation` ct ON ct.translatable_id = p.category_id ";
				$q.= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
				$q.= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";		
				break;
			case 'count':
				$q = "SELECT p.id ";
				$q.= "FROM `" . $this->table . "` p ";
				$q.= "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id ";
				$q.= "LEFT JOIN `categories` c ON p.category_id=c.id ";
				$q.= "LEFT JOIN `categories_translation` ct ON ct.translatable_id = p.category_id ";
				$q.= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
				$q.= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";	
				break;
			default :
				throw new \Exception('Nieobsługiwany typ wyniku zapytania.' . __FUNCTION__);
				break;
		}

        if (isset($params['new']) AND $params['new'] == 1) {
            $q.= "WHERE p.status_id=1 ";
        } else {
            $q.= "WHERE p.status_id IN (1,2) ";
        }
		
		$q .= "AND pt.locale='" . Cms::$session->get('locale') . "' ";
		$q .= "AND ct.locale='" . Cms::$session->get('locale') . "' ";
      
        // dla produktu rodzica wybieramy jedna wariacje ktora spelnia kryteria i sortowanie wedlug wartosci cechy		
		if (Cms::$conf['stock_availability'] == 1) {
			$q.= "AND v.id2=(SELECT `id2` FROM `" . $this->table . "_variation` ";
			$q.= "LEFT JOIN `feature_values_translation` fvt1 ON fvt1.`translatable_id` = `feature1_value_id` AND fvt1.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "LEFT JOIN `feature_values_translation` fvt2 ON fvt2.`translatable_id` = `feature2_value_id` AND fvt2.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "LEFT JOIN `feature_values_translation` fvt3 ON fvt3.`translatable_id` = `feature3_value_id` AND fvt3.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "WHERE `product_id`=p.id ";
		} else {
			$q.= "AND v.id2=(SELECT `id2` FROM `" . $this->table . "_variation` ";
			$q.= "LEFT JOIN `feature_values_translation` fvt1 ON fvt1.`translatable_id` = `feature1_value_id` AND fvt1.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "LEFT JOIN `feature_values_translation` fvt2 ON fvt2.`translatable_id` = `feature2_value_id` AND fvt2.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "LEFT JOIN `feature_values_translation` fvt3 ON fvt3.`translatable_id` = `feature3_value_id` AND fvt3.`locale` ='".Cms::$session->get('locale')."' ";
			$q.= "WHERE `product_id`=p.id AND `qty`>0 ";
		}
		
        if (isset($params['ids'])) {
            $q.= "AND p.id IN (".implode(',', $params['ids']).") ";
        }
        
        if (isset($params['bestseller']) AND $params['bestseller'] == 1) {
            $q.= "AND `bestseller`='1' ";
        }
        if (isset($params['recommended']) AND $params['recommended'] == 1) {
            $q.= "AND `recommended`='1' ";
        }
        if (isset($params['promotion']) AND $params['promotion'] == 1) {
            $q.= "AND `promotion`='1' ";
        }
        if (isset($params['mainPage']) AND $params['mainPage'] == 1) {
            $q.= "AND `main_page`='1' ";
        }
        
		if (isset($params['eans'])) {
			$q.= "AND v.`ean` IN (".implode(',', $params['eans']).") ";
		}		        
        
//		if (isset($params['keyword']) AND $params['keyword']) {
//			$q.= "OR v.`ean` LIKE '%" . $params['keyword'] . "%' ";
//		}
		
        $firstMainPageProducts = false;
        if (isset(Cms::$conf['product_list_first_main_page_products']) &&
                Cms::$conf['product_list_first_main_page_products'] == 1) {
            $firstMainPageProducts = true;
        }
        
        if ($firstMainPageProducts) {          
            $q.= "ORDER BY `main_page` DESC, `price` ASC, v.`ean` ASC, fvt1.`name` ASC, fvt2.`name` ASC, fvt3.`name` ASC LIMIT 1) ";            
//            $q.= "ORDER BY `main_page` DESC, v.`ean` ASC, `feature1_value` ASC, `feature2_value` ASC, `feature3_value` ASC LIMIT 1) ";            
        } else {           
            $q.= "ORDER BY `price` ASC, v.`ean` ASC, fvt1.`name` ASC, fvt2.`name` ASC, fvt3.`name` ASC LIMIT 1) ";            
//            $q.= "ORDER BY v.`ean` ASC, fvt1.`name` ASC, fvt2.`name` ASC, fvt3.`name` ASC LIMIT 1) ";            
//            $q.= "ORDER BY v.`ean` ASC, v.`feature1_value` ASC, fvt1.`name` ASC, fvt2.`name` ASC, fvt3.`name` ASC LIMIT 1) ";            
//            $q.= "ORDER BY v.`ean` ASC, fvt1.`feature1_value` ASC, fvt2.`feature2_value` ASC, fvt3.`feature3_value` ASC LIMIT 1) ";            
        }   
                
        // kryteria dla produktu rodzica
        if (isset($params['name']) AND $params['name']) {
            $q.= "AND pt.name LIKE '%" . $params['name'] . "%' ";
        }
        if (isset($params['category_id']) AND $params['category_id'] > 0) {
            $q.= " AND (c.id='" . (int) $params['category_id'] . "' OR c.parent_id='" . (int) $params['category_id'] . "') ";
        }
        if (isset($params['producer_id']) AND $params['producer_id'] > 0) {
            $q.= " AND p.producer_id='" . (int) $params['producer_id'] . "' ";
        }
        if (isset($params['keyword']) AND $params['keyword']) {
            $q.= "AND (pt.name LIKE '%" . $params['keyword'] . "%' ";
            $q.= "OR p.tag1 LIKE '%" . $params['keyword'] . "%' OR p.tag2 LIKE '%" . $params['keyword'] . "%' OR p.tag3 LIKE '%" . $params['keyword'] . "%' ";			
			$q.= "OR v.ean LIKE '%" . $params['keyword'] . "%') ";
        }
        
        if (isset($params['onlyProducts'])) {
            $q.= "AND p.id IN (".$params['onlyProducts'].") ";
        }
		
        if (isset($params['producers'])) {
            $q.= "AND p.producer_id IN (".implode(',', $params['producers']).") ";
        }		
		
        if (isset($params['price_from'])) {
            $q.= "AND IF(v.promotion='1', (v.price_promotion + v.price_promotion * t.value / 100), (v.price + v.price * t.value / 100)) >= '". $params['price_from'] . "'";
        }
		
        if (isset($params['price_to'])) {
            $q.= "AND IF(v.promotion='1', (v.price_promotion + v.price_promotion * t.value / 100), (v.price + v.price * t.value / 100)) <= '". $params['price_to'] . "'";
        }			
		
		
				
        $q.= "GROUP BY p.id ";
//        $q.= "GROUP BY p.id,price_sort,mega_offer,parent_url,file ";

        if ($params['resultType'] == 'list' || $params['resultType'] == 'search') { // lista produktow
            $q .= 'ORDER BY '; 
            if ($firstMainPageProducts) {
                $q .= 'v.main_page DESC, ';
            }
            
            // sortowanie
            if ($params['sort'] == 'name_asc') {
                $q.= "pt.name ASC ";
            } elseif ($params['sort'] == 'name_desc') {
                $q.= "pt.name DESC ";
            } elseif ($params['sort'] == 'price_asc') {
                $q.= "`price_sort` ASC ";
            } elseif ($params['sort'] == 'price_desc') {
                $q.= "`price_sort` DESC ";
            } elseif ($params['sort'] == 'date_asc') {
                $q.= "p.date_add ASC ";
            } elseif ($params['sort'] == 'date_desc') {
                $q.= "p.date_add DESC ";
            } else {
                $q.= "p.id ASC ";
            }

            $q.= "LIMIT " . $params['start'] . ", " . $params['limit'];
        }		

		return $q;		
	}
	
    public function getBy(array $params = [], $fields = '') {
        $params = maddslashes($params);

		$resultType = isset($params['resultType']) ? $params['resultType'] : false;		

		$query = $this->getQuery($resultType, $params);

        $array = Cms::$db->getAll($query); 
        $items = array();
        
        $priceGroupEnabled = false;
        $logged = Customer::isLogged();
        if (CMS::$modules['price_groups'] && $logged) {
            $priceGroupEnabled = true;
        }

        $lastShoppingThreshold = Cms::$shoppingThresholds->getLast();
        
        foreach ($array as $v) {
            $v = mstripslashes($v);
            unset($v['desc']); // na liscie nie jest potrzebne, a ma spory rozmiar

            if ($params['resultType'] == 'list' || $params['resultType'] == 'search') { // lista produktow
                $v['photo'] = $this->getSrc($v['file']);
                if ($v['parent_url']) {
                    $v['url'] = URL . '/product/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
                } else {
                    $v['url'] = URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
                }

                if (strlen($v['name']) >= 40) {
                    $v['name_short'] = substr($v['name'], 0, 40) . '...';
                } else {
                    $v['name_short'] = $v['name'];
                }

                $v['price_gross'] = formatPrice($v['price'], $v['tax']);
                $v['price_rrp'] = formatPrice($v['price_rrp']);
                if ($v['promotion'] == 1) {
                    $v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
                }
                
                if ($priceGroupEnabled) {
                    if ($_SESSION[CUSTOMER_CODE]['price_group'] != 1) {
                        $v['price_gross'] = formatPrice($v['price' . $_SESSION[CUSTOMER_CODE]['price_group']], $v['tax']);
                    }
                }
                
                if (CMS::$modules['price_groups'] && !$logged) {
                    $v['price_gross'] = 0;
                }
                
                if ($lastShoppingThreshold) {
                    $priceGross = ($v['promotion'] == 1) ? $v['price_promotion_gross'] : $v['price_gross'];
                    
                    $v['lastShoppingThreshold'] = $lastShoppingThreshold;
                    $v['lastShoppingThreshold']['priceAfterDiscount'] = formatPrice($priceGross - $priceGross * $lastShoppingThreshold['discount'] / 100);                    
                }           
                                
            }

            $items[] = $v;
        }

        return $items;
    }
	
	public function getAll() {
		return $this->select($this->table);
	}	
	
    public function getAll2(array $filtr = []) {
        $filtr = maddslashes($filtr);

        if ($filtr['type'] == 'list') { // lista produktow	
            $q = "SELECT p.*, c.name_url as `category_url`, v.price_rrp, v.price, v.price2, v.price3, v.price_promotion, v.promotion, t.value as `tax`, ";
            $q.= "IF(v.promotion='1', (v.price_promotion + v.price_promotion * t.value / 100), (v.price + v.price * t.value / 100)) as `price_sort`, ";
            $q.= "(SELECT max(mega_offer) FROM `" . $this->tableVariations . "` v WHERE v.`product_id`=p.id) as `mega_offer`, ";
            $q.= "(SELECT `name_url` FROM `" . $this->table . "_category` WHERE `id`=c.parent_id LIMIT 1) as `parent_url`, ";
            $q.= "(SELECT `file` FROM `" . $this->table . "_image` WHERE `product_id`=p.id ORDER BY `variation_id`, `order` ASC LIMIT 1) as file ";
        } else {
            $q = "SELECT count(*) ";
//            $q = "SELECT p.id ";
        }
        $q.= "FROM `" . $this->table . "` p ";
        $q.= "LEFT JOIN `" . $this->table . "_category` c ON p.category_id=c.id ";
        $q.= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
        $q.= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";

        if (isset($filtr['new']) AND $filtr['new'] == 1) {
            $q.= "WHERE p.status_id=1 ";
        } else {
            $q.= "WHERE p.status_id IN (1,2) ";
        }
      
        // dla produktu rodzica wybieramy jedna wariacje ktora spelnia kryteria i sortowanie wedlug wartosci cechy
		
		if (Cms::$conf['stock_availability'] == 1) {
			$q.= "AND v.id2=(SELECT `id2` FROM `" . $this->table . "_variation` WHERE `product_id`=p.id ";
		} else {
			$q.= "AND v.id2=(SELECT `id2` FROM `" . $this->table . "_variation` WHERE `product_id`=p.id AND `qty`>0 ";
		}
		
        if (isset($filtr['bestseller']) AND $filtr['bestseller'] == 1) {
            $q.= "AND v.`bestseller`='1' ";
        }
        if (isset($filtr['recommended']) AND $filtr['recommended'] == 1) {
            $q.= "AND v.`recommended`='1' ";
        }
        if (isset($filtr['promotion']) AND $filtr['promotion'] == 1) {
            $q.= "AND v.`promotion`='1' ";
        }
        if (isset($filtr['mainPage']) AND $filtr['mainPage'] == 1) {
            $q.= "AND v.`main_page`='1' ";
        }
		if (isset($filtr['keyword']) AND $filtr['keyword']) {
			$q.= "OR v.`ean` LIKE '%" . $filtr['keyword'] . "%' ";
		}
		
        $q.= "ORDER BY v.`ean` ASC, v.`feature1_value` ASC, v.`feature2_value` ASC, v.`feature3_value` ASC LIMIT 1) ";

        // kryteria dla produktu rodzica
        if (isset($filtr['name']) AND $filtr['name']) {
            $q.= "AND p.name LIKE '%" . $filtr['name'] . "%' ";
        }
        if (isset($filtr['category_id']) AND $filtr['category_id'] > 0) {
            $q.= " AND (c.id='" . (int) $filtr['category_id'] . "' OR c.parent_id='" . (int) $filtr['category_id'] . "') ";
        }
        if (isset($filtr['producer_id']) AND $filtr['producer_id'] > 0) {
            $q.= " AND p.producer_id='" . (int) $filtr['producer_id'] . "' ";
        }
        if (isset($filtr['keyword']) AND $filtr['keyword']) {
            $q.= "AND (p.name LIKE '%" . $filtr['keyword'] . "%' ";
//            $q.= "AND (p.name LIKE '%" . $filtr['keyword'] . "%' OR p.desc LIKE '%" . $filtr['keyword'] . "%' ";
			$q.= "OR v.ean LIKE '%" . $filtr['keyword'] . "%' ";
            $q.= "OR p.tag1 LIKE '%" . $filtr['keyword'] . "%' OR p.tag2 LIKE '%" . $filtr['keyword'] . "%' OR p.tag3 LIKE '%" . $filtr['keyword'] . "%') ";
        }
        
        if (isset($filtr['onlyProducts'])) {
            $q.= "AND p.id IN (".$filtr['onlyProducts'].") ";
        }
        
        $q.= "GROUP BY p.id ";
//        $q.= "GROUP BY p.id,price_sort,mega_offer,parent_url,file ";

        if ($filtr['type'] == 'list') { // lista produktow
            // sortowanie
            if ($filtr['sort'] == 'name_asc') {
                $q.= "ORDER BY p.name ASC ";
            } elseif ($filtr['sort'] == 'name_desc') {
                $q.= "ORDER BY p.name DESC ";
            } elseif ($filtr['sort'] == 'price_asc') {
                $q.= "ORDER BY `price_sort` ASC ";
            } elseif ($filtr['sort'] == 'price_desc') {
                $q.= "ORDER BY `price_sort` DESC ";
            } elseif ($filtr['sort'] == 'date_asc') {
                $q.= "ORDER BY p.date_add ASC ";
            } elseif ($filtr['sort'] == 'date_desc') {
                $q.= "ORDER BY p.date_add DESC ";
            } else {
                $q.= "ORDER BY p.id ASC ";
            }

            $q.= "LIMIT " . $filtr['start'] . ", " . $filtr['limit'];
        }
//dump($q);
        $array = Cms::$db->getAll($q);
        $items = array();
        
        $priceGroupEnabled = false;
        $logged = Customer::isLogged();
        if (CMS::$modules['price_groups'] && $logged) {
            $priceGroupEnabled = true;
        }

        $lastShoppingThreshold = Cms::$shoppingThresholds->getLast();
        
        foreach ($array as $v) {
            $v = mstripslashes($v);
            unset($v['desc']); // na liscie nie jest potrzebne, a ma spory rozmiar

            if ($filtr['type'] == 'list') { // lista produktow
                $v['photo'] = $this->getSrc($v['file']);
                if ($v['parent_url']) {
                    $v['url'] = URL . '/product/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
                } else {
                    $v['url'] = URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
                }

                if (strlen($v['name']) >= 40) {
                    $v['name_short'] = substr($v['name'], 0, 40) . '...';
                } else {
                    $v['name_short'] = $v['name'];
                }

                $v['price_gross'] = formatPrice($v['price'], $v['tax']);
                $v['price_rrp'] = formatPrice($v['price_rrp']);
                if ($v['promotion'] == 1) {
                    $v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
                }
                
                if ($priceGroupEnabled) {
                    if ($_SESSION[CUSTOMER_CODE]['price_group'] != 1) {
                        $v['price_gross'] = formatPrice($v['price' . $_SESSION[CUSTOMER_CODE]['price_group']], $v['tax']);
                    }
                }
                
                if (CMS::$modules['price_groups'] && !$logged) {
                    $v['price_gross'] = 0;
                }
                
                if ($lastShoppingThreshold) {
                    $priceGross = ($v['promotion'] == 1) ? $v['price_promotion_gross'] : $v['price_gross'];
                    
                    $v['lastShoppingThreshold'] = $lastShoppingThreshold;
                    $v['lastShoppingThreshold']['priceAfterDiscount'] = formatPrice($priceGross - $priceGross * $lastShoppingThreshold['discount'] / 100);                    
                }           
                                
            }

            $items[] = $v;
        }

        return $items;
    }	
    
    public function getVariationsBy(array $params = []) {
        $product_id = addslashes($params['product_id']);
		$feature1_value_id = isset($params['feature1_value_id']) ? addslashes($params['feature1_value_id']) : null;
		$feature2_value_id = isset($params['feature2_value_id']) ? addslashes($params['feature2_value_id']) : null;

        $q = "SELECT v.*, MAX(qty) as qty, t.value as `tax`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id` = v.feature1_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature1_value`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id` = v.feature2_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature2_value`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id` = v.feature3_value_id AND locale='".Cms::$session->get('locale')."' LIMIT 1) as `feature3_value` ";					
        $q .= "FROM `" . $this->table . "_variation` v ";
        $q .= "LEFT JOIN `" . $this->table . "` p ON p.id=v.product_id ";
        $q .= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";
        $q .= "WHERE v.product_id='" . (int) $product_id . "' ";
		
        if ($feature2_value_id) {
            $q.= "AND v.feature1_value_id='" . $feature1_value_id . "' AND v.feature2_value_id='" . $feature2_value_id . "'GROUP BY v.feature3_value_id ";
        } elseif ($feature1_value_id) {
            $q.= "AND v.feature1_value_id='" . $feature1_value_id . "' GROUP BY v.feature2_value_id ";
        } else {
            $q.= "GROUP BY v.feature1_value_id ";
        }
		
        $q.= "ORDER BY price ASC, feature1_value ASC, feature2_value ASC, feature3_value ASC, qty ";

        $array = Cms::$db->getAll($q);

        $items = array();

        $lastShoppingThreshold = Cms::$shoppingThresholds->getLast();
        
        foreach ($array as $v) {
			
            $v['price_gross'] = formatPrice($v['price'], $v['tax']);
            $v['price_rrp'] = formatPrice($v['price_rrp']);
            if ($v['promotion'] == 1) {
                $v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
            }
            
            $v['price2_gross'] = formatPrice($v['price2'], $v['tax']);
            $v['price3_gross'] = formatPrice($v['price3'], $v['tax']);         
            
            if ($lastShoppingThreshold) {
                $priceGross = ($v['promotion'] == 1) ? $v['price_promotion_gross'] : $v['price_gross'];

                $v['lastShoppingThreshold'] = $lastShoppingThreshold;
                $v['lastShoppingThreshold']['priceAfterDiscount'] = formatPrice($priceGross - $priceGross * $lastShoppingThreshold['discount'] / 100);                    
            }         
            
            // jesli wariacja nie ma zdjecia przypisz jej zdjecia produktu
            $photos = $this->getImage($product_id, $v['id2']);
            
            if (!$photos) {
                $photos = $this->getImage($product_id);
            }
            
            $v['photos'] = $photos;

            $items[] = $v;
        }
        return $items;
    }
	
    public function getVariationsByProductId($product_id = 0, $feature1_value_id = '', $feature2_value_id = '') {
        $product_id = addslashes($product_id);
        $feature1_value = addslashes($feature1_value);
        $feature2_value = addslashes($feature2_value);

        $q = "SELECT v.*, t.value as `tax` ";
        $q.= "FROM `" . $this->table . "` p ";
        $q.= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
        $q.= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";
        $q.= "WHERE p.id='" . (int) $product_id . "' ";
		
        if ($feature2_value_id) {
            $q.= "AND v.feature1_value_id='" . $feature1_value_id . "' AND v.feature2_value_id='" . $feature2_value_id . "'GROUP BY v.feature3_value_id ";
        } elseif ($feature1_value_id) {
            $q.= "AND v.feature1_value_id='" . $feature1_value_id . "' GROUP BY v.feature2_value_id ";
        } else {
            $q.= "GROUP BY v.feature1_value_id ";
        }
		
        $q.= "ORDER BY feature1_value ASC, feature2_value ASC, feature3_value ASC, qty ";
//		
//        if ($feature2_value) {
//            $q.= "AND v.feature1_value='" . $feature1_value . "' AND v.feature2_value='" . $feature2_value . "'GROUP BY v.feature3_value ";
//        } elseif ($feature1_value) {
//            $q.= "AND v.feature1_value='" . $feature1_value . "' GROUP BY v.feature2_value ";
//        } else {
//            $q.= "GROUP BY v.feature1_value ";
//        }
//        $q.= "ORDER BY v.feature1_value ASC, v.feature2_value ASC, v.feature3_value ASC, v.qty ";
//        $q.= "ORDER BY v.qty, v.feature1_value ASC, v.feature2_value ASC, v.feature3_value ASC ";

        $array = Cms::$db->getAll($q);

        $items = array();

        $lastShoppingThreshold = Cms::$shoppingThresholds->getLast();
        
        foreach ($array as $v) {
			
            $v['price_gross'] = formatPrice($v['price'], $v['tax']);
            $v['price_rrp'] = formatPrice($v['price_rrp']);
            if ($v['promotion'] == 1) {
                $v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
            }
            
            $v['price2_gross'] = formatPrice($v['price2'], $v['tax']);
            $v['price3_gross'] = formatPrice($v['price3'], $v['tax']);         
            
            if ($lastShoppingThreshold) {
                $priceGross = ($v['promotion'] == 1) ? $v['price_promotion_gross'] : $v['price_gross'];

                $v['lastShoppingThreshold'] = $lastShoppingThreshold;
                $v['lastShoppingThreshold']['priceAfterDiscount'] = formatPrice($priceGross - $priceGross * $lastShoppingThreshold['discount'] / 100);                    
            }         
            
            // jesli wariacja nie ma zdjecia przypisz jej zdjecia produktu
            $photos = $this->getImage($product_id, $v['id2']);
            
            if (!$photos) {
                $photos = $this->getImage($product_id);
            }
            
            $v['photos'] = $photos;

            $items[] = $v;
        }
        return $items;
    }

    public function getImage($product_id = 0, $variation_id = 0) {
        $product_id = addslashes($product_id);

		$q = "SELECT * FROM `" . $this->table . "_image` WHERE `product_id`='" . (int) $product_id . "' AND `variation_id`='" . $variation_id . "' ORDER BY `order` ASC ";
        
        $array = Cms::$db->getAll($q);
        $items = array();
        foreach ($array as $v) {
            $v['photo'] = $this->getSrc($v['file']);
            $items[] = $v;
        }
        return $items;
    }      

    function getSrc($fileName) {

        $fileNameS = changeFileName($fileName, '_s');
        $fileNameM = changeFileName($fileName, '_m');
        $row = '';

        if (!empty($fileName) and file_exists($this->dir . '/' . $fileName)) {
            $row['normal'] = $this->url . '/' . $fileName;
            $row['small'] = $this->url . '/' . $fileNameS;
            $row['middle'] = $this->url . '/' . $fileNameM;
        }
        return $row;
    }

    public function loadAlsoBought($id) {                        
        $q = "SELECT op2.product_id, SUM(op2.qty) as qty FROM `" . DB_PREFIX . "order_product` op1 LEFT JOIN `" . DB_PREFIX . "order` o1 ON op1.order_id=o1.id ";
        $q.= "LEFT JOIN `" . DB_PREFIX . "order_product` op2 ON o1.id=op2.order_id LEFT JOIN `" . DB_PREFIX . "product` p ON op2.product_id=p.id ";
        $q.= "WHERE op1.product_id='" . (int) $id . "' AND op2.product_id!='" . (int) $id . "' AND op2.product_id!='' AND p.status_id IN (1,2) AND (SELECT MAX(qty) FROM `product_variation` WHERE product_id = '" . $id . "')>0 AND (SELECT MAX(price) FROM `product_variation` WHERE product_id = '" . $id . "')>0 ";
        $q.= "GROUP BY op2.product_id ORDER BY qty DESC LIMIT 5 ";
        
        $array = Cms::$db->getAll($q);

        $items = array();
        foreach ($array as $v) {
            $items[] = $v['product_id'];
        }
//		dump($items);
		
        if ($items) {
            $where = implode(',', $items);
            $params = [];
            $params['limit'] = 5;
            $params['resultType'] = 'list';
            $params['sort'] = 'asc';
            $params['start'] = 0;
            $params['onlyProducts'] = $where;

			$best = $this->getBy($params);			

            if ($best) {
                foreach ($best as $key => $item) {
                    foreach ($array as $product) {
                        if ($product['product_id'] == $item['id']) {
                            $best[$key]['qty'] = $product['qty'];
                        }
                    }
                }
            }

            $sortedBest = arrayOrderByKey($best, 'qty', SORT_DESC);

            return $sortedBest;
        }
        return false;
    }
	
    public function loadAlsoBoughtOld($id) {                        
        $q = "SELECT op2.product_id, SUM(op2.qty) as qty FROM `" . DB_PREFIX . "order_product` op1 LEFT JOIN `" . DB_PREFIX . "order` o1 ON op1.order_id=o1.id ";
        $q.= "LEFT JOIN `" . DB_PREFIX . "order_product` op2 ON o1.id=op2.order_id LEFT JOIN `" . DB_PREFIX . "product` p ON op2.product_id=p.id ";
        $q.= "WHERE op1.product_id='" . (int) $id . "' AND op2.product_id!='" . (int) $id . "' AND op2.product_id!='' AND p.status_id IN (1,2) AND (SELECT MAX(qty) FROM `product_variation` WHERE product_id = '" . $id . "')>0 AND (SELECT MAX(price) FROM `product_variation` WHERE product_id = '" . $id . "')>0 ";
        $q.= "GROUP BY op2.product_id ORDER BY qty DESC LIMIT 5 ";
        
        $array = Cms::$db->getAll($q);

        $items = array();
        foreach ($array as $v) {
            $items[] = $v['product_id'];
        }
        if ($items) {
            $where = implode(',', $items);
            $filtr = [];
            $filtr['limit'] = 5;
            $filtr['resultType'] = 'list';
            $filtr['sort'] = 'asc';
            $filtr['start'] = 0;
            $filtr['onlyProducts'] = $where;

            $best = $this->getAll($filtr);
            
            if ($best) {
                foreach ($best as $key => $item) {
                    foreach ($array as $product) {
                        if ($product['product_id'] == $item['id']) {
                            $best[$key]['qty'] = $product['qty'];
                        }
                    }
                }
            }

            $sortedBest = arrayOrderByKey($best, 'qty', SORT_DESC);

            return $sortedBest;
        }
        return false;
    } 
    
    /*
     * Get first variation with qty > 0 from variations
     */
    public function getDefaultVariation(array $variations) {
				
        if ($variations) {
			
			if (Cms::$conf['stock_availability'] == 1) {
				return $variations[0];
			}
			
            foreach ($variations as $variation) {
                if ($variation['qty']) {
                    return $variation;                    
                }
            } 
        }
        
        return false; 
    }
    
//    public function loadAlsoBought($id) {
//        $q = "SELECT p2.product_id, SUM(p2.qty) as qty FROM `" . DB_PREFIX . "order_product` p1 LEFT JOIN `" . DB_PREFIX . "order` o1 ON p1.order_id=o1.id ";
//        $q.= "LEFT JOIN `" . DB_PREFIX . "order_product` p2 ON o1.id=p2.order_id LEFT JOIN `" . DB_PREFIX . "product` p3 ON p2.product_id=p3.id ";
//        $q.= "WHERE p1.product_id='" . (int) $id . "' AND p2.product_id!='" . (int) $id . "' AND p2.product_id!='' AND p3.active='1' AND p3.available>0 AND p3.price>0 ";
//        $q.= "GROUP BY p2.product_id ORDER BY qty DESC LIMIT 4 ";
//        
//        dump($q);
//        $array = Cms::$db->getAll($q);
//        $items = array();
//        foreach ($array as $v) {
//            $items[] = $v['product_id'];
//        }
//        if ($items) {
//            $where = implode(',', $items);
//            $best = $this->loadProducts('rowniez', $where, 0, 4);
//            return $best;
//        }
//        return false;
//    }

}
