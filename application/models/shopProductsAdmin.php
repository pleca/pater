<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/uploadAdmin.php');
require_once(MODEL_DIR . '/NotificationsStockAvailability.php');
require_once(CMS_DIR . '/application/models/mailer.php');
require_once(MODEL_DIR . '/EmailTemplate.php');

require_once(MODEL_DIR . '/imageUploader.php');

class ProductsAdmin extends Products {

	public $module;
	public $table;
	public $tableDesc;
	public $url;
	public $dir;
	private $upload;

	public function __construct() {
		$this->module = 'shop';
		$this->table = DB_PREFIX . 'product';
		$this->tableDesc = DB_PREFIX . 'product_desc';
		$this->tableCategories = DB_PREFIX . 'product_category';
		$this->tableProducers = DB_PREFIX . 'product_manufacturer';
		$this->tableVariations = DB_PREFIX . 'product_variation';
		$this->tablePhotos = DB_PREFIX . 'product_image';
		$this->tableTax = DB_PREFIX . 'taxes';
		$this->upload = new UploadAdmin();
		$this->mailer = new Mailer();
		$this->dir = CMS_DIR . '/files/product';
		$this->url = CMS_URL . '/files/product';
		$this->ratio = 'k'; // y - wysokosc auto, x - szerokosc auto, c - kadrowanie, k - dluzszy bok
		$this->widthS = 160;
		$this->heightS = 160;
		$this->widthM = 350;
		$this->heightM = 350;
	}

	public function __destruct() {
		
	}

	public function loadAdmin($filtr = []) {
		$filtr = maddslashes($filtr);
		
		$q = "SELECT p.*, c.name_url as `category_url`, ";
		$q.= "(SELECT `name_url` FROM `" . $this->tableCategories . "` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` ";
		$q.= ",(SELECT max(promotion) FROM `" . $this->tableVariations . "` v WHERE v.`product_id`=p.id) as `promotion` ";        
		$q.= ",(SELECT max(recommended) FROM `" . $this->tableVariations . "` v WHERE v.`product_id`=p.id) as `recommended` ";
		$q.= ",(SELECT max(bestseller) FROM `" . $this->tableVariations . "` v WHERE v.`product_id`=p.id) as `bestseller` ";
		$q.= "FROM `" . $this->table . "` p ";
		$q.= "LEFT JOIN `" . $this->tableCategories . "` c ON p.category_id=c.id ";
		$q.= "WHERE 1 ";	

		if ($filtr['id']) {
			$q.= "AND p.id='" . (int) $filtr['id'] . "' ";
		}        
		if ($filtr['name']) {
			$q.= "AND p.name LIKE '%" . $filtr['name'] . "%' ";
		}
		if ($filtr['category_id'] > 0) {
			$q.= " AND p.category_id='" . (int) $filtr['category_id'] . "' ";
		}
		if ($filtr['producer_id'] > 0) {
			$q.= " AND p.producer_id='" . (int) $filtr['producer_id'] . "' ";
		}
		$q.= "ORDER BY p.name ASC ";
		$q.= "LIMIT " . $filtr['start'] . ", " . $filtr['limit'];

		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			if ($v['parent_url']) {
				$v['url'] = URL . '/product/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			}
			else {
				$v['url'] = URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			}			
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($filtr = []) {
		$filtr = maddslashes($filtr);
		
		$q = "SELECT COUNT(p.id) FROM `" . $this->table . "` p ";
		$q.= "WHERE 1 ";
		if ($filtr['id']) {
			$q.= "AND p.id='" . (int) $filtr['id'] . "' ";
		}
		if ($filtr['name']) {
			$q.= "AND p.name LIKE '%" . $filtr['name'] . "%' ";
		}
		if ($filtr['category_id'] > 0) {
			$q.= " AND p.category_id='" . (int) $filtr['category_id'] . "' ";
		}
		if ($filtr['producer_id'] > 0) {
			$q.= " AND p.producer_id='" . (int) $filtr['producer_id'] . "' ";
		}

		$array = Cms::$db->getAll($q);
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $filtr['limit']);
	}

	public function loadByIdAdmin($id = 0) {
		
		$locale  = Cms::$defaultLocale;
		
		if ($id > 0) {
			$id = addslashes($id);
			$q = "SELECT p.*, pt.name, pt.seo_title, pt.slug as name_url, ct.slug as `category_url`, ";
			$q.= "(SELECT `slug` FROM `categories_translation` WHERE `translatable_id`=c.parent_id LIMIT 1) as `parent_url`, ";
			$q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='".$locale."' LIMIT 1) as `feature1_name`, ";
			$q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='".$locale."' LIMIT 1) as `feature2_name`, ";
			$q.= "(SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='".$locale."' LIMIT 1) as `feature3_name` ";
			$q.= "FROM `" . $this->table . "` p ";
			$q.= "LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id AND pt.locale = '". $locale . "' ";
			$q.= "LEFT JOIN `categories` c ON p.category_id=c.id ";
			$q.= "LEFT JOIN `categories_translation` ct ON p.category_id=ct.translatable_id AND ct.locale = '". $locale . "' ";
			$q.= "WHERE p.id='" . (int) $id . "' ";
			
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				if ($v['parent_url']) {
				$v['url'] = URL . '/product/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
				}
				else {
					$v['url'] = URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
				}	
								
				$v['trans'] = $this->getTranslation($id);

				return $v;
			}
		}
		return false;
	}
	
	public function getTranslation($id) {
		$q = "SELECT t.translatable_id, t.name, t.slug, t.content, t.content_short, t.seo_title, t.locale FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "WHERE p.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);		
		$result = getArrayByKey($result, 'locale');
		
		if ($result) {
			foreach ($result as &$entity) {
				$entity = mstripslashes($entity);
			}
		}		

		return $result;
	}	

	protected function convertToTranslationData(&$data) {
		if (!$data) {
			return false;
		}
		
		foreach ($data as $key => $value) {
			if (!in_array($key, Cms::$locales)) {
				unset($data[$key]);
			}			
		}		
	}
	
	public function addAdmin($post) {
		$post = maddslashes($post);
        
		if (empty($post[Cms::$defaultLocale]['name'])) {
			return 'Nie wpisano nazwy produktu.';
		} elseif ($post['category_id'] < 1) {
			return 'Nie wybrano kategorii.';
		}					

		$q = "INSERT INTO " . $this->table . " SET `category_id`='" . $post['category_id'] . "', `producer_id`='" . $post['producer_id'] . "', ";
		$q.= "`status_id`='" . $post['status_id'] . "', `type`='" . $post['type'] . "', ";
		$q.= "`date_add`=NOW(), `date_mod`=NOW() ";
//dump($q);
		if ($id = Cms::$db->insert($q)) {
			$this->convertToTranslationData($post);	
			
			foreach ($post as $locale => $trans) {
				if (!$trans['content_short']) {
					$trans['content_short'] = substr(strip_tags($trans['content']), 0, 250);
				}
		
                $name = clearName($trans['name']);
                $slug = makeUrl($name);
        
				$item = array(
					'translatable_id' => $id,					
					'name' => $name,					
					'slug' => $slug,					
					'content_short' => $trans['content_short'],					
					'content' => $trans['content'],					
					'locale' => $locale,
				);
				
				$this->insert($this->table . '_translation', $item);				
			}			
		
			return $id;
		} else {
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
	
	protected function existsTranslation($entityId, $locale, $entities) {
		
		if (isset($entities[$locale][$entityId])) {
			return true;
		}
		
		return false;
	}	
	
	public function getAll(array $params = []) {
		$q = "SELECT p.*, t.translatable_id, t.content, t.content_short, t.locale "
				. "FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "ORDER BY p.`id` ";

		$array = Cms::$db->getAll($q);

		$grouped = $this->groupByTranslation($array, 'id');
		
		return $grouped;
	}
	
	public function editAdmin($post) {		
		$post = maddslashes($post);

		if (empty($post[Cms::$defaultLocale]['name'])) {
			return 'Nie wpisano nazwy produktu.';
		} elseif ($post['category_id'] < 1) {
			return 'Nie wybrano kategorii.';
		}		

		$q = "UPDATE " . $this->table . " SET `category_id`='" . $post['category_id'] . "', `producer_id`='" . $post['producer_id'] . "', ";
		$q.= "`status_id`='" . $post['status_id'] . "', `type`='" . $post['type'] . "', ";
		$q.= "`date_mod`=NOW() ";
		$q.= "WHERE `id`='" . $post['id'] . "' ";
		
		$entities = $this->getAll();

		Cms::$db->update($q);

		$id = $post['id'];
		$this->convertToTranslationData($post);
		$entities = $this->getAll();

		foreach ($post as $locale => $trans) {

            $name = clearName($trans['name']);
            $slug = makeUrl($name);
        
			$item = array(
				'translatable_id' => $id,
				'name' => $name,					
				'slug' => $slug,		
				'content' => $trans['content'],					
				'seo_title' => $trans['seo_title'],					
				'content_short' => $trans['content_short'],					
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
	
	public function expandedAdmin($post) {	
		$post = maddslashes($post);

		$q = "UPDATE " . $this->table;
		$q.= " SET `feature1_id`='" . $post['feature1_id'] . "', `feature2_id`='" . $post['feature2_id'] . "', `feature3_id`='" . $post['feature3_id'] . "', ";
		$q.= "`tag1`='" . $post['tag1'] . "', `tag2`='" . $post['tag2'] . "', `tag3`='" . $post['tag3'] . "', `date_mod`=NOW() ";
		$q.= "WHERE `id`='" . $post['id'] . "' ";		
		
		if (Cms::$db->update($q)) {			
			return true;
		} else {
			return false;
		}
	}
	
    protected function customizeFilesFormat($files) {        
        $formatedPhotos = [];
        if (isset($files['photos'])) {
            
            $numFiles = count($files['photos']['name']);
            
            for ($i = 0; $i < $numFiles; $i++) {                
                foreach ($files['photos'] as $key => $value) {
                    $formatedPhotos['photos_' . $i][$key] = $value[$i];
                }                
            }
            
        } else {
            return $files;
        }
        
        return $formatedPhotos;
    }
    
	public function imageAdmin($post, $files) {		
		$post = maddslashes($post);

		$item = $this->loadByIdAdmin($post['id']);
        $files = $this->customizeFilesFormat($files);

		if ($this->addPhoto($files, $post['id'], $item['name_url'])) {
			$this->updateProductDateMod($post['id']);
			return true;
		} else {
			return false;
		}
	}

	public function nameExists($name_url, $parent_id = 0, $lang_id = '') {
		if (empty($name_url))
			$name_url = '-';
		$temp_url = $name_url;
		$i = 0;
		do {
			$q = "SELECT `name_url` FROM `" . $this->tableDesc . "` WHERE `name_url`='" . $temp_url . "' AND `lang_id`='" . (int) $lang_id . "' ";
			if ($parent_id > 0)
				$q.= "AND `parent_id`!='" . (int) $parent_id . "' ";
			if ($row = Cms::$db->getRow($q)) {
				$i++;
				$temp_url = $name_url . '_' . $i;
			} else {
				return $temp_url;
			}
		} while ($name_url != $temp_url);
		return $temp_url;
	}

	public function unlinkPhotos($photos = []) {
        if ($photos) {
            foreach ($photos as $photo) {
                
                $img_name = $photo['file'];
                $img_name_m = change_file_name($photo['file'], '_m');
                $img_name_s = change_file_name($photo['file'], '_s');                

                if (file_exists($this->dir . '/' . $img_name)) {
                    unlink($this->dir . '/' . $img_name);
                }

                if (file_exists($this->dir . '/' . $img_name_m)) {
                    unlink($this->dir . '/' . $img_name_m);
                }

                if (file_exists($this->dir . '/' . $img_name_s)) {
                    unlink($this->dir . '/' . $img_name_s);
                }              
            }
        }        
    }
    
	public function deleteAdmin($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			$result = Cms::$db->delete($q);
            
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			$result = Cms::$db->delete($q);
            
			$q = "DELETE FROM " . $this->tableVariations . " WHERE `product_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);

            $q = "DELETE FROM " . $this->tablePhotos . " WHERE `product_id`='" . (int) $id . "' ";
            Cms::$db->delete($q);
            
            $q = "SELECT * FROM " . $this->tablePhotos . " WHERE `product_id`='" . (int) $id . "' ";
            $array = Cms::$db->getAll($q);
            
            $photos = array();
            foreach ($array as $v) {
                $v['photo'] = $this->getSrc($v['file']);
                $photos[] = $v;
            }            

            $this->unlinkPhotos($photos);

			return $result;
		}

		return false;
	}
    
	public function addPhoto($files, $id, $name_url, $variation_id = 0, $notPost = 0) {
		$error = '';
        
		for ($i = 0; $i < count($files); $i++) {
			if (isset($files['photos_' . $i]) AND $files['photos_' . $i]['error'] == 0) {
				$idMax = $this->getMaxId();
				$idMax = $idMax['0'] + 1;
				$img_name = makeUrl(substr($name_url, 0, 50)) . '_' . $idMax;
                
				if($img_name = $this->upload->add_image($files['photos_' . $i], $img_name, $this->dir, $this->ratio, $this->widthM, $this->heightM, $this->widthS, $this->heightS)) {

					$orderMax = $this->getMaxOrder($id, $variation_id);
					$orderMax = $orderMax['0'] + 1;
					$q = "INSERT INTO " . $this->tablePhotos . " SET `product_id`='" . $id . "', `variation_id`='" . $variation_id . "', `file`='" . $img_name . "', `order`='" . $orderMax . "' ";
					Cms::$db->insert($q);
					$q = "UPDATE " . $this->tablePhotos . " SET `date_mod`=NOW() WHERE `product_id`='" . $id . "' AND `variation_id`='" . $variation_id . "' ";
					Cms::$db->update($q);
				} else {
//					$error .= $this->uploader->ErrorMsg();
				}
                
            } elseif($files[$i]['error'] == 0) {
                
				$idMax = $this->getMaxId();
				$idMax = $idMax['0'] + 1;
				$img_name = makeUrl(substr($name_url, 0, 50)) . '_' . $idMax;

				if ($img_name = $this->upload->add_image($files[$i], $img_name, $this->dir, $this->ratio, $this->widthM, $this->heightM, $this->widthS, $this->heightS)) {
					$orderMax = $this->getMaxOrder($id, $variation_id);
					$orderMax = $orderMax['0'] + 1;
					$q = "INSERT INTO " . $this->tablePhotos . " SET `product_id`='" . $id . "', `variation_id`='" . $variation_id . "', `file`='" . $img_name . "', `order`='" . $orderMax . "' ";
					Cms::$db->insert($q);
					$q = "UPDATE " . $this->tablePhotos . " SET `date_mod`=NOW() WHERE `product_id`='" . $id . "' AND `variation_id`='" . $variation_id . "' ";
					Cms::$db->update($q);
				} else {
					return false;
//                    echo 'nie udalo sie zapisac';
//					$error .= $this->uploader->ErrorMsg();
				}                
            }
		}


		if (empty($error))
			return true;
		else {
			Cms::$tpl->setError($error);
			return false;
		}
	}

	public function getMaxId() {
		$q = "SELECT MAX(`id`) FROM " . $this->tablePhotos . " ";
		return Cms::$db->max($q);
	}

	public function getMaxOrder($parent_id, $variation_id = 0) {
		$q = "SELECT MAX(`order`) FROM " . $this->tablePhotos . " WHERE `product_id`='" . $parent_id . "' ";
        $q .= "AND `variation_id`='" . $variation_id . "' ";        
        
		return Cms::$db->max($q);
	}

	public function loadTax() {
		$q = "SELECT * FROM `" . $this->tableTax . "` ORDER BY `order` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function loadPhotoById($id) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->tablePhotos . "` WHERE `id`='" . (int) $id . "' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}

	public function moveDownPhotoAdmin($id) {
		if ($item = $this->loadPhotoById($id)) {
			$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 ";
			$q.= "WHERE `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' AND `order`='" . ($item['order'] + 1) . "'";
			if (Cms::$db->update($q)) {
				$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`+1 WHERE `id`='" . (int) $id . "'";
				if (Cms::$db->update($q)) {
					$q = "UPDATE " . $this->tablePhotos . " SET `date_mod`=NOW() WHERE `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' ";
					Cms::$db->update($q);
					return true;
				}
			}
		}

		return false;
	}

	public function moveUpPhotoAdmin($id) {
		if ($item = $this->loadPhotoById($id)) {

			if ($item['order'] > 1) {
				$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`+1 ";
				$q.= "WHERE `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' AND `order`='" . ($item['order'] - 1) . "'";

                if (Cms::$db->update($q)) {
					$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 WHERE `id`='" . (int) $id . "'";
					if (Cms::$db->update($q)) {
						$q = "UPDATE " . $this->tablePhotos . " SET `date_mod`=NOW() WHERE `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' ";
						Cms::$db->update($q);
						return true;
					}
				}
			}
		}

		return false;
	}

	function deletePhotoAdmin($id, $productId = null) {
		if ($item = $this->loadPhotoById($id)) {
			if ($item['file'])
				unlink($this->dir . '/' . $item['file']);
			if ($item['file'])
				unlink($this->dir . '/' . changeFileName($item['file'], '_s'));
			if ($item['file'])
				unlink($this->dir . '/' . changeFileName($item['file'], '_m'));
			$q = "DELETE FROM " . $this->tablePhotos . " WHERE `id`='" . (int) $item['id'] . "' ";
			$result = Cms::$db->delete($q);
			$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' ";
			Cms::$db->update($q);
			$q = "UPDATE " . $this->tablePhotos . " SET `date_mod`=NOW() WHERE `product_id`='" . $item['product_id'] . "' AND `variation_id`='" . $item['variation_id'] . "' ";
			Cms::$db->update($q);

			if ($productId) {
				$this->updateProductDateMod($productId);
			}

			return $result;
		}

		return false;
	}
    
//    public function photo_delete($id) {
//        $q = "SELECT * FROM `" . $this->tablePhotos . "` WHERE `id`='" . (int) $id . "' ";
//        if ($item = $this->db->getRow($q)) {
//            $img_name = $item['file'];
//            $img_name_m = change_file_name($item['file'], '_m');
//            $img_name_s = change_file_name($item['file'], '_s');
//            $img_name_e = change_file_name($item['file'], '_e');
//            if (!empty($item['file'])) {
//                if (file_exists($this->img_dir . '/' . $img_name))
//                    unlink($this->img_dir . '/' . $img_name);
//                if (file_exists($this->img_dir . '/' . $img_name_m))
//                    unlink($this->img_dir . '/' . $img_name_m);
//                if (file_exists($this->img_dir . '/' . $img_name_s))
//                    unlink($this->img_dir . '/' . $img_name_s);
//                if (file_exists($this->img_dir . '/' . $img_name_e))
//                    unlink($this->img_dir . '/' . $img_name_e);
//				}
//					$q = "DELETE FROM `" . $this->tablePhotos . "` WHERE `id`='" . $item['id'] . "'";
//					$ok = $this->db->delete($q);
//					unset($q);
//					if($item['product_id'] != 0) {
//					  $q = "UPDATE ".$this -> tablePhotos." SET `order`=`order`-1 WHERE `order`>'".$item['order']."' AND `product_id`='".$item['product_id']."' ";
//					  $this -> db -> update($q);
//					} else {
//					if($item['variation_id'] != 0)
//					  $q = "UPDATE ".$this -> tablePhotos." SET `order`=`order`-1 WHERE `order`>'".$item['order']."' AND `variation_id`='".$item['variation_id']."' ";
//					  $this -> db -> update($q);
//					}
//					if($ok == 1)
//					{
//					  $this->tpl->setInfo($GLOBALS['LANG']['info_delete']);
//					  return true;
//				  }
//        }
//        $this->tpl->setError($GLOBALS['LANG']['error_delete']);
//        return false;
//    }    

	public function getMainProduct($type) {
		$q = "SELECT m.id, p.promotion, p.price, p.price_promotion, p.price_rrp, d.name, d.name_url, ";
		$q.= "(SELECT `file` FROM `" . $this->tablePhotos . "` WHERE parent_id=p.id ORDER BY `order` ASC LIMIT 1) as file, ";
		$q.= "(SELECT `name_url` FROM `" . $this->tableCategories . "_desc` WHERE `lang_id`='" . _ID . "' AND `parent_id`=c.id LIMIT 1) as category_url, ";
		$q.= "(SELECT `name_url` FROM `" . $this->tableCategories . "_desc` WHERE `lang_id`='" . _ID . "' AND `parent_id`=c.parent_id LIMIT 1) as parent_url, ";
		$q.= "(SELECT `value` FROM `" . DB_PREFIX . "shop_tax` WHERE id=p.tax_id LIMIT 1) as tax ";
		$q.= "FROM `" . DB_PREFIX . "main_products` m LEFT JOIN `" . DB_PREFIX . "shop_products` p ON m.parent_id=p.id ";
		$q.= "LEFT JOIN `" . DB_PREFIX . "shop_products_desc` d ON p.id=d.parent_id LEFT JOIN `" . DB_PREFIX . "shop_categories` c ON p.category_id=c.id ";
		$q.= "WHERE d.lang_id=1 AND m.type='" . $type . "' ORDER BY m.order ASC";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['price_gross'] = formatPrice($v['price'], $v['tax']);
			$v['price_rrp'] = formatPrice($v['price_rrp']);
			if ($v['promotion'] == 1)
				$v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
			if ($v['parent_url'])
				$v['url'] = SERVER_URL . CMS_URL . '/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			else
				$v['url'] = SERVER_URL . CMS_URL . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			$v['photo'] = $this->getSrc($v['file']);
			$items[] = $v;
		}
		return $items;
	}

	public function addMainProduct($type, $parent_id) {
		if ($parent_id > 0) {
			$q = "SELECT MAX(`order`) FROM `" . DB_PREFIX . "main_products` WHERE `type`='" . $type . "' ";
			$item = Cms::$db->max($q);
			if ($item[0] < 1)
				$orderMax = 1;
			else
				$orderMax = $item[0] + 1;
			$q = "INSERT INTO `" . DB_PREFIX . "main_products` SET `type`='" . $type . "', `parent_id`='" . (int) $parent_id . "', `order`='" . $orderMax . "' ";
			Cms::$db->update($q);
			Cms::$tpl->setInfo('Dodano produkt.');
			return true;
		}
		Cms::$tpl->setError('Nie wybrano produktu.');
		return false;
	}

	public function moveDownMainProduct($id) {
		$q = "SELECT * FROM `" . DB_PREFIX . "main_products` WHERE `id`='" . (int) $id . "' ";
		if ($item = Cms::$db->getRow($q)) {
			$q = "UPDATE `" . DB_PREFIX . "main_products` SET `order`=`order`-1 ";
			$q.= "WHERE `type`='" . $item['type'] . "' AND `order`='" . ($item['order'] + 1) . "' ";
			if (Cms::$db->update($q)) {
				$q = "UPDATE `" . DB_PREFIX . "main_products` SET `order`=`order`+1 WHERE `id`='" . (int) $id . "'";
				if (Cms::$db->update($q)) {
					Cms::$tpl->setInfo('Przeniesiono element o jeden poziom niżej!');
					return true;
				}
			}
		}
		Cms::$tpl->setError('Zmiana nie powiodła się!');
		return false;
	}

	public function moveUpMainProduct($id) {
		$q = "SELECT * FROM `" . DB_PREFIX . "main_products` WHERE `id`='" . (int) $id . "' ";
		if ($item = Cms::$db->getRow($q)) {
			if ($item['order'] > 1) {
				$q = "UPDATE `" . DB_PREFIX . "main_products` SET `order`=`order`+1 ";
				$q.= "WHERE `type`='" . $item['type'] . "' AND `order`='" . ($item['order'] - 1) . "' ";
				if (Cms::$db->update($q)) {
					$q = "UPDATE `" . DB_PREFIX . "main_products` SET `order`=`order`-1 WHERE `id`='" . (int) $id . "'";
					if (Cms::$db->update($q)) {
						Cms::$tpl->setInfo('Przeniesiono element o jeden poziom wyżej!');
						return true;
					}
				}
			}
		}
		Cms::$tpl->setError('Zmiana nie powiodła się!');
		return false;
	}

	public function deleteMainProduct($id) {
		$q = "SELECT * FROM `" . DB_PREFIX . "main_products` WHERE `id`='" . (int) $id . "' ";
		if ($item = Cms::$db->getRow($q)) {
			$q = "DELETE FROM `" . DB_PREFIX . "main_products` WHERE `id`='" . (int) $item['id'] . "' ";
			Cms::$db->delete($q);
			$q = "UPDATE `" . DB_PREFIX . "main_products` SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `type`='" . $item['type'] . "' ";
			Cms::$db->update($q);
			Cms::$tpl->setError('Wybrany produkt usunięto z listy.');
			return true;
		}
		Cms::$tpl->setError('Usuwanie elementu nie powiodło się!');
		return false;
	}

	
	public function loadVariationsByProductId($product_id = 0) {
		$product_id = addslashes($product_id);
		
		$q = "SELECT v.*, t.value as `tax`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature1_value`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature2_value`, ";
		$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature3_value` ";
		$q .= "FROM `" . $this->table . "` p ";
		$q .= "LEFT JOIN `" . $this->table . "_variation` v ON p.id=v.product_id ";
		$q .= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";
		$q .= "WHERE p.id='" . (int) $product_id . "' ";
		$q .= "ORDER BY price ASC, feature1_value ASC, feature2_value ASC, feature3_value ASC ";
		$array = Cms::$db->getAll($q);

		$items = array();
		foreach ($array as $v) {
			$v['price_gross'] = formatPrice($v['price'], $v['tax']);
			$v['price_rrp'] = formatPrice($v['price_rrp']);
			if ($v['promotion'] == 1) {
				$v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax']);
			}	
			$items[] = $v;
		}
		return $items;
	}
	
	public function loadVariationById($id = 0) {
        
		if ($id > 0) {
			$q = "SELECT v.*, t.value as `tax`,";
			$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature1_value`, ";
			$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature2_value`, ";
			$q .= "(SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='".Cms::$defaultLocale."' LIMIT 1) as `feature3_value` ";
			$q .= "FROM `" . $this->table . "_variation` v ";
            $q .= "LEFT JOIN `" . DB_PREFIX . "taxes` t ON v.tax_id=t.id ";
            $q .= "WHERE v.id2='" . (int) $id . "' ";                        

			if($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);                
                $this->decorateVariation($v);               
            
				return $v;
            }
		}
		return false;
	}
	
	protected function validateVariationForm($post) {
		if (CMS::$modules['unit_transport']) {
			$errors = false;
			if (!$post['length']) {
				Cms::getFlashBag()->add('error', 'Nie podano długości.');
				$errors = true;
			}
			if (!$post['width']) {
				Cms::getFlashBag()->add('error', 'Nie podano szerokości.');
				$errors = true;
			}
			
			if (!$post['height']) {
				Cms::getFlashBag()->add('error', 'Nie podano wysokości.');
				$errors = true;
			}
			
//			if (!$post['is_advertaising_material']) {
				if (!$post['transport_group_id']) {
					Cms::getFlashBag()->add('error', 'Nie wybrano grupy transportowej.');
					$errors = true;
				}
				if (!$post['transport_unit_id']) {
					Cms::getFlashBag()->add('error', 'Nie wybrano jednostki transportowej.');
					$errors = true;
				}
//			}
			
			if ($errors) {
				return false;
			}
		}
		
		return true;
	}
	
	public function addVariation($post) {
		if (empty($post['sku'])) {
			Cms::getFlashBag()->add('error', 'Brak unikatowego kodu produktu.');
			return false;
		} elseif ($post['price'] < 0.01) {
			Cms::getFlashBag()->add('error', 'Produkt nie posiada ceny sprzedaży, nie może być zapisany.');
			return false;
		}
		$q = "SELECT `id2` FROM `" . $this->table . "_variation` WHERE `sku`='" . $post['sku'] . "' ";		
		
		$code = Cms::$db->getRow($q);
		if ($code['id2'] > 0) {
			Cms::getFlashBag()->add('error', 'Istnieje już produkt o wpisanym unikatowym kodzie.');
			return false;
		}
		
		$tax_id = isset($post['tax_id']) ? $post['tax_id'] : 0;
		$price_purchase = str_replace(',', '.', $post['price_purchase']);
		$price = str_replace(',', '.', $post['price']);
		$price_rrp = str_replace(',', '.', $post['price_rrp']);
		$promotion = isset($post['promotion']) ? 1 : 0;
		$bestseller = isset($post['bestseller']) ? 1 : 0;
		$recommended = isset($post['recommended']) ? 1 : 0;
		$mainPage = isset($post['main_page']) ? 1 : 0;
		$megaOffer = isset($post['mega_offer']) ? 1 : 0;
        
		if ($promotion == 1) {
			$price_promotion = str_replace(',', '.', $post['price_promotion']);
		} else {
			$price_promotion = 0;
			$post['date_promotion'] = '';
		}
		$weight = isset($post['weight']) ? str_replace(',', '.', $post['weight']) : '';
		$qty = isset($post['qty']) ? $post['qty'] : 0;

		$q = "INSERT INTO `" . $this->table . "_variation` SET `product_id`='" . $post['id'] . "', `price_purchase`='" . $price_purchase . "', ";
		$q.= "`price`='" . $price . "', `price_rrp`='" . $price_rrp . "', `promotion`='" . $promotion . "', `bestseller`='" . $bestseller . "', `recommended`='" . $recommended . "', ";
		$q.= "`main_page`='" . $mainPage . "',`mega_offer`='" . $megaOffer . "',`price_promotion`='" . $price_promotion . "', ";
		$q.= "`tax_id`='" . $tax_id . "', `weight`='" . $weight . "', `qty`='" . $qty . "', `sku`='" . $post['sku'] . "', `ean`='" . $post['ean'] . "', ";
		if (isset($post['feature1_value_id'])) $q.= "`feature1_value_id`='" . $post['feature1_value_id']. "', ";
		if (isset($post['feature2_value_id'])) $q.= "`feature2_value_id`='" . $post['feature2_value_id']. "', ";
		if (isset($post['feature3_value_id'])) $q.= "`feature3_value_id`='" . $post['feature3_value_id']. "', ";
		if (isset($post['special_link']['content'])) $q.= "`special_link_content`='" . $post['special_link']['content']. "', ";
		if (isset($post['special_link']['url'])) $q.= "`special_link_url`='" . $post['special_link']['url']. "', ";
        
        if (CMS::$modules['price_groups']) {
            $q.= "`price2`='" . $post['price2'] . "', ";
            $q.= "`price3`='" . $post['price3'] . "', ";            
        }
		
        if (CMS::$modules['unit_transport']) {
            $q.= "`length`='" . $post['length'] . "', ";
            $q.= "`width`='" . $post['width'] . "', ";            
            $q.= "`height`='" . $post['height'] . "', ";            
			$q.= "`transport_group_id`='" . $post['transport_group_id'] . "', ";         
			$q.= "`transport_unit_id`='" . $post['transport_unit_id'] . "', ";         
        } 		
        
		$q.= "`date_promotion`='" . $post['date_promotion'] . "' ";
		
		if (!$this->validateVariationForm($post)) {
			return false;
		}
		
		if ($id = Cms::$db->insert($q)) {
			$this->updateProductDateMod($post['id']);
			
			Cms::getFlashBag()->add('info', 'Dodano nową wariację.');
			return $id;
		} else {
			Cms::getFlashBag()->add('error', 'Błąd dodawania wariacji.');
			return false;
		}
	}
	
	public function editVariation($post) {
		if (empty($post['sku'])) {
			return 'Brak unikatowego kodu produktu.';
		} elseif ($post['price'] < 0.01) {
			return 'Produkt nie posiada ceny sprzedaży, nie może być zapisany.';
		}        
        
		$q = "SELECT `id2` FROM `" . $this->table . "_variation` WHERE `sku`='" . $post['sku'] . "' AND `id2`!='" . $post['variation_id'] . "' ";
		$code = Cms::$db->getRow($q);
		if ($code['id2'] > 0) {
			return 'Istnieje już produkt o wpisanym unikatowym kodzie.';
		}
		
		$tax_id = isset($post['tax_id']) ? $post['tax_id'] : 0;
		$price_purchase = str_replace(',', '.', $post['price_purchase']);
		$price = str_replace(',', '.', $post['price']);
		$price_rrp = str_replace(',', '.', $post['price_rrp']);
		$promotion = isset($post['promotion']) ? 1 : 0;
		$bestseller = isset($post['bestseller']) ? 1 : 0;
		$recommended = isset($post['recommended']) ? 1 : 0;
        $mainPage = isset($post['main_page']) ? 1 : 0;
        $megaOffer = isset($post['mega_offer']) ? 1 : 0;
		if ($promotion == 1) {
			$price_promotion = str_replace(',', '.', $post['price_promotion']);
		} else {
			$price_promotion = 0;
			$post['date_promotion'] = '';
		}
		$weight = isset($post['weight']) ? str_replace(',', '.', $post['weight']) : '';
		$qty = isset($post['qty']) ? $post['qty'] : 0;

		$q = "UPDATE `" . $this->table . "_variation` SET `product_id`='" . $post['id'] . "', `price_purchase`='" . $price_purchase . "', ";
		$q.= "`price`='" . $price . "', `price_rrp`='" . $price_rrp . "', `promotion`='" . $promotion . "', `bestseller`='" . $bestseller . "', `recommended`='" . $recommended . "', ";
		$q.= "`main_page`='" . $mainPage . "',`mega_offer`='" . $megaOffer . "', `price_promotion`='" . $price_promotion . "', ";
		$q.= "`tax_id`='" . $tax_id . "', `weight`='" . $weight . "', `qty`='" . $qty . "', `sku`='" . $post['sku'] . "', `ean`='" . $post['ean'] . "', ";
		if (isset($post['feature1_value_id'])) $q.= "`feature1_value_id`='" . $post['feature1_value_id']. "', ";
		if (isset($post['feature2_value_id'])) $q.= "`feature2_value_id`='" . $post['feature2_value_id']. "', ";
		if (isset($post['feature3_value_id'])) $q.= "`feature3_value_id`='" . $post['feature3_value_id']. "', ";
		if (isset($post['special_link']['content'])) $q.= "`special_link_content`='" . $post['special_link']['content']. "', ";
		if (isset($post['special_link']['url'])) $q.= "`special_link_url`='" . $post['special_link']['url']. "', ";
        
        if (CMS::$modules['price_groups']) {
            $q.= "`price2`='" . $post['price2'] . "', ";
            $q.= "`price3`='" . $post['price3'] . "', ";            
        } 
        
        if (CMS::$modules['unit_transport']) {
            $q.= "`length`='" . $post['length'] . "', ";
            $q.= "`width`='" . $post['width'] . "', ";            
            $q.= "`height`='" . $post['height'] . "', ";  
			$q.= "`transport_group_id`='" . $post['transport_group_id'] . "', ";
			$q.= "`transport_unit_id`='" . $post['transport_unit_id'] . "', ";  
        } 
		
		$q.= "`date_promotion`='" . $post['date_promotion'] . "' ";
		$q.= "WHERE `id2`= '" . (int) $post['variation_id'] . "' ";
		
		$this->processStockAvailability($post);	

		if (!$this->validateVariationForm($post)) {
			return false;
		}
		
		if (Cms::$db->update($q)) {
			$this->updateProductDateMod($post['id']);
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);			
			return true;
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_change']);			
			return false;
		}
	}
    
	public function processStockAvailability($post) {

		if (!Cms::$conf['stock_availability']) {			
			return false;
		}

		$q = "SELECT `id2`, `product_id`, `qty` FROM `" . $this->table . "_variation` WHERE `id2`='" . $post['variation_id'] . "' ";
		$beforeChangeEntity = Cms::$db->getRow($q);	
		
		if ($beforeChangeEntity['qty'] == 0 && $post['qty'] > 0) {				
			$this->stockAvailabilityNotificationsSend($beforeChangeEntity);
		}
		
	}
	/*
	 * send email to recipients
	 * remove recipients from notifications for that variation
	 */
	public function stockAvailabilityNotificationsSend($beforeChangeEntity) {
		$nsa = new NotificationsStockAvailability();
		$params = array(
			'variation_id' => $beforeChangeEntity['id2']		
		);
	
//		$q = "SELECT pt.`name` FROM `" . $this->table . "` p"
//				. " LEFT JOIN `product_translation` pt ON pt.`translatable_id` = p.id AND pt.locale = '". Cms::$defaultLocale . "'"
//				. " WHERE pt.`id`='" . $beforeChangeEntity['product_id'] . "' ";
//		
//		$product = Cms::$db->getRow($q);
        
        $product = $this->loadByIdAdmin($beforeChangeEntity['product_id']);        
        $productUrl = '<a href="' . $product['url'] . '">' . $product['name'] . '</a>';
				
		$fields = ['email'];
		$notifiers = $nsa->findBy($params, $fields);

		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate('notifications_stock_availability');		
		
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';        
        
        //title
		$searchTitle = array('#COMPANY_NAME#', '#DOMAIN#', '#PRODUCT#');
		$replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $product['name']);
		$title = str_replace($searchTitle, $replaceTitle, $template['title']);
        
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
		$search = array('#PRODUCT#','#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
		$replace = array($productUrl, Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
		$content = str_replace($search, $replace, $template['content']);

		// wysylanie do klienta
		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);		

		if ($notifiers) {
			foreach ($notifiers as $notifier) {
				$this->mailer->sendHTML($notifier['email']);
				$this->mailer->ClearAllRecipients();				
			}
			
			$q = "DELETE FROM `" . DB_PREFIX . "notifications_stock_availability` WHERE `variation_id`='" . (int) $beforeChangeEntity['id2'] . "' ";
			Cms::$db->delete($q);			
		}
		
		return true;
	}	
	
	public function deleteVariationPhotos($variationId, $productId) {
        if (!$variationId) {
            return false;
        }
        
        $photos = $this->getImage($productId, $variationId);

        $q = "DELETE FROM " . $this->tablePhotos . " WHERE `variation_id`='" . (int) $variationId . "' ";
        Cms::$db->delete($q); 
        
        if ($photos) {
            foreach ($photos as $photo) {
                
                $img_name = $photo['file'];
                $img_name_m = change_file_name($photo['file'], '_m');
                $img_name_s = change_file_name($photo['file'], '_s');                

                if (file_exists($this->dir . '/' . $img_name)) {
                    unlink($this->dir . '/' . $img_name);
                }

                if (file_exists($this->dir . '/' . $img_name_m)) {
                    unlink($this->dir . '/' . $img_name_m);
                }

                if (file_exists($this->dir . '/' . $img_name_s)) {
                    unlink($this->dir . '/' . $img_name_s);
                }              
            }
        }
  
    }
    
	public function deleteVariationAdmin($id, $productId = null) {
		if ($id) {
			$q = "DELETE FROM " . $this->tableVariations . " WHERE `id2`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			
            $this->deleteVariationPhotos($id, $productId);
            
			if ($productId) {
				$this->updateProductDateMod($productId);
			}
			
			return true;
		}

		return false;
	}

	protected function updateProductDateMod($productId) {
		if (!$productId) {
			return false;
		}
		
		$q = "UPDATE `" . $this->table . "` SET `date_mod`=NOW() WHERE `id` = '" . (int) $productId ."' ";
		Cms::$db->update($q);		
	}
	
	public function variationImageAdmin($post, $files) {		
		$post = maddslashes($post);

		$item = $this->loadByIdAdmin($post['id']);
        $files = $this->customizeFilesFormat($files);

		if ($this->addPhoto($files, $post['id'], $item['name_url'], $post['variation_id'])) {
			$this->updateProductDateMod($post['id']);

			return true;
		} else {
			return false;
		}
	}
    
    protected function decorateVariation(&$variation) {
        
        $variation['price_gross'] = formatPrice($variation['price'], $variation['tax']);
        $variation['price_rrp'] = formatPrice($variation['price_rrp']);
        if ($variation['promotion'] == 1) {
            $variation['price_promotion_gross'] = formatPrice($variation['price_promotion'], $variation['tax']);
        }
            
        //shopping thresholds decorator
        $lastShoppingThreshold = Cms::$shoppingThresholds->getLast();     

        if ($lastShoppingThreshold) {          
            $priceGross = ($variation['promotion'] == 1) ? $variation['price_promotion_gross'] : $variation['price_gross'];

            $variation['lastShoppingThreshold'] = $lastShoppingThreshold;
            $variation['lastShoppingThreshold']['priceAfterDiscount'] = formatPrice($priceGross - $priceGross * $lastShoppingThreshold['discount'] / 100);                    
        }
		
		
		$photos = $this->getImage($variation['product_id'], $variation['id2']);
        
        // jesli wariacja nie ma zdjecia przypisz jej zdjecia produktu
		if (!$photos) {
			$photos = $this->getImage($variation['product_id']);
		}

		$variation['photos'] = $photos;		
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
	
	
	
}
