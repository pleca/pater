<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/uploadAdmin.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');

class ApiProducts {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product';
		$this->upload = new UploadAdmin();
		$this->img_dir = CMS_DIR . '/files/product';
		$this->img_url = CMS_URL . '/files/product';
		$this->ratio = 'k'; // y - wysokosc auto, x - szerokosc auto, c - kadrowanie, k - dluzszy bok
		$this->widthS = 160;
		$this->heightS = 160;
		$this->widthM = 350;
		$this->heightM = 350;
	}

	public function __destruct() {
		
	}

	public function get_first_variation_by_product_id($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.* FROM `" . $this->table . "_variation` a WHERE a.product_id='" . (int) $id . "' AND (a.status_id='1' OR a.status_id='2') LIMIT 1 ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

	public function get_by_id($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.*, b.name, b.slug FROM `" . $this->table . "` a LEFT JOIN `" . $this->table . "_translation` b ON a.id=b.translatable_id ";
            $q.= "WHERE a.id='" . (int) $id . "' AND b.locale='en' ";            

			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

	public function get_variation_by_id($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.*, b.value as `tax` FROM `" . $this->table . "_variation` a LEFT JOIN `" . DB_PREFIX . "taxes` b ON a.tax_id=b.id ";
                        $q.= "WHERE a.id2='" . (int) $id . "' ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}
    
	public function get_variation_by_sku($id = 0, $sku = '') {
		if ($id > 0) {
			$q = "SELECT a.*, b.value as `tax` FROM `" . $this->table . "_variation` a LEFT JOIN `" . DB_PREFIX . "taxes` b ON a.tax_id=b.id ";
                        $q.= "WHERE a.product_id='" . (int) $id . "' AND a.sku='" . addslashes($sku) . "' ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

	public function add($data) {
		$data = maddslashes($data);
		$data['name_url'] = makeUrl($data['name']);

		$q = "INSERT INTO `" . $this->table . "` SET `category_id`='" . (int) $data['category_id'] . "', `producer_id`='" . (int) $data['manufacturer_id'] . "', ";
		$q.= "`status_id`='" . (int) $data['status_id'] . "', `type`='" . $data['model_id'] . "', `name`='" . $data['name'] . "', `name_url`='" . $data['name_url'] . "', ";
		$q.= "`desc`='" . $data['desc'] . "', ";
		$q.= "`feature1_name`='" . $data['feature1_name'] . "', `feature2_name`='" . $data['feature2_name'] . "', `feature3_name`='" . $data['feature3_name'] . "', ";
		$q.= "`date_add`=NOW(), `date_mod`=NOW() ";
		$id = Cms::$db->insert($q);

		return $id;
	}

	public function set_variations($data = '', $id = 0) {
		foreach ($data as $v) {
			$v = maddslashes($v);
			$tax = $this->get_tax_by_value($v['tax']);
			$v['price'] = $v['price'] / (1 + $v['tax'] / 100); // dostajemy brutto, liczymy netto
			
			if($item = $this->get_variation_by_sku($id , $v['sku'])) {
				$q = "UPDATE `" . $this->table . "_variation` SET `tax_id`='" . $tax['id'] . "', ";
				$q.= "`price_purchase`='" . $v['price_purchase'] . "', `price_rrp`='" . $v['price_rrp'] . "',  `price`='" . $v['price'] . "', ";
				$q.= "`qty`='" . $v['qty'] . "', `weight`='" . $v['weight'] . "', ";
				$q.= "`feature1_value`='" . $v['feature1_value'] . "', `feature2_value`='" . $v['feature2_value'] . "', `feature3_value`='" . $v['feature3_value'] . "' ";
				$q.= "WHERE `id`='" . (int) $item['id'] . "' ";
				Cms::$db->update($q);
			} else {			
				$q = "INSERT INTO `" . $this->table . "_variation` SET `product_id`='" . (int) $id . "', `tax_id`='" . $tax['id'] . "', `sku`='" . $v['sku'] . "', ";
				$q.= "`price_purchase`='" . $v['price_purchase'] . "', `price_rrp`='" . $v['price_rrp'] . "',  `price`='" . $v['price'] . "', ";
				$q.= "`qty`='" . $v['qty'] . "', `weight`='" . $v['weight'] . "', ";
				$q.= "`feature1_value`='" . $v['feature1_value'] . "', `feature2_value`='" . $v['feature2_value'] . "', `feature3_value`='" . $v['feature3_value'] . "' ";
				Cms::$db->insert($q);
			}
		}
		return true;
	}

	public function add_images($data = '', $id = 0) {
		$item = $this->get_by_id($id);

		foreach ($data as $v) {
			$img_src = $v['url'];
			$img_dst = 'tmp.jpg';
			if (copy($img_src, CMS_DIR . '/files/' . $img_dst)) {
				$image = array();
				$image['name'] = $img_dst;
				$image['type'] = 'image/jpeg';
				$image['tmp_name'] = CMS_DIR . '/files/' . $img_dst;
				$image['error'] = 0;
				$image['size'] = filesize(CMS_DIR . '/files/' . $img_dst);
				$image['local'] = 1; // ustawaimy zmianna local gdy plik nie pochodzi z formularza

				if (!empty($image['name']) AND $image['error'] == 0) {
					$q = "SELECT MAX(`order`) FROM `" . $this->table . "_image` WHERE `product_id`='" . (int) $id . "' ";
					$t = Cms::$db->max($q);
					$img_ord = $t[0] + 1;
					$img_name = makeUrl(substr($item['name'], 0, 50)) . '_' . $id . '_' . $img_ord;

					$file = $this->upload->add_image($image, $img_name, $this->img_dir, $this->ratio, $this->widthM, $this->heightM, $this->widthS, $this->heightS);

					$q = "INSERT INTO `" . $this->table . "_image` SET `product_id`='" . (int) $id . "', `ga_image_id`='" . (int) $v['image_id'] . "', `file`='" . $file . "', `order`='" . $img_ord . "', `date_mod`=NOW() ";
					Cms::$db->insert($q);
				}
			}
		}
		return true;
	}

	public function edit($data) {
		$data = maddslashes($data);
		$data['name_url'] = makeUrl($data['name']);
		$id = $data['online_id'];
		$bullet = $data['bullet1'] . '|' . $data['bullet2'] . '|' . $data['bullet3'] . '|' . $data['bullet4'] . '|' . $data['bullet5'];

		$q = "UPDATE `" . $this->table . "` SET `category_id`='" . (int) $data['category_id'] . "', `producer_id`='" . (int) $data['manufacturer_id'] . "', ";
		$q.= "`active`='" . (int) $data['active'] . "', `date_mod`=NOW() ";
		$q.= "WHERE `id`='" . (int) $id . "' ";
		Cms::$db->update($q);

		$q = "UPDATE `" . $this->table . "_desc` SET `title`='" . $data['name'] . "', `title_url`='" . $data['name_url'] . "', ";
		$q.= "`desc`='" . $data['desc_1'] . "', `ingredients`='" . $data['desc_2'] . "', `rda`='" . $data['desc_3'] . "', `contrain`='" . $data['desc_4'] . "', ";
		$q.= "`bullet`='" . $bullet . "' ";
		$q.= "WHERE `parent_id`='" . (int) $id . "' AND `lang_id`='" . (int) $data['language_id'] . "' ";
		Cms::$db->update($q);

		return $id;
	}

	public function edit_images($data = '', $id = 0) {
		$item = $this->get_by_id($id);

		foreach ($data as $v) {
			$img_src = $v['url'];
			$img_dst = 'tmp.jpg';
			if (copy($img_src, CMS_DIR . '/files/' . $img_dst)) {
				$image = array();
				$image['name'] = $img_dst;
				$image['type'] = 'image/jpeg';
				$image['tmp_name'] = CMS_DIR . '/files/' . $img_dst;
				$image['error'] = 0;
				$image['size'] = filesize(CMS_DIR . '/files/' . $img_dst);
				$image['local'] = 1; // ustawaimy zmianna local gdy plik nie pochodzi z formularza

				if (!empty($image['name']) AND $image['error'] == 0) {
					$q = "SELECT MAX(`order`) FROM `" . DB_PREFIX . "shop_photos` WHERE `product_id`='" . (int) $id . "' ";
					$t = Cms::$db->max($q);
					$img_ord = $t[0] + 1;
					$img_name = make_url(substr($item['name'], 0, 50)) . '_' . $id . '_' . $img_ord;

					$file = $this->upload->add_image($image, $img_name, $this->img_dir, $this->ratio, $this->widthM, $this->heightM, $this->widthS, $this->heightS);

					$q = "INSERT INTO `" . DB_PREFIX . "shop_photos` SET `product_id`='" . (int) $id . "', `ga_image_id`='" . (int) $v['image_id'] . "', `file`='" . $file . "', `order`='" . $img_ord . "', `date_mod`=NOW() ";
					Cms::$db->insert($q);
				}
			}
		}
		return true;
	}

	public function set_status_id($data = '') {
		$q = "UPDATE `" . $this->table . "` SET `status_id`='" . $data['status_id'] . "' ";
		$q.= "WHERE `id`='" . (int) $data['online_id'] . "' ";
		if (Cms::$db->update($q)) {
			$item['id'] = $data['online_id'];
			return $item;
		}
		return false;
	}

	public function edit_variation_by_id($id = 0, $data = '') {
		if (isset($data['price'])) {
			$tax = $this->get_tax_by_value($data['tax']);
			$data['price'] = $data['price'] / (1 + $data['tax'] / 100); // dostajemy brutto, liczymy netto
		}
        
		$q = "SELECT `id2`, `product_id`, `qty` FROM `" . $this->table . "_variation` WHERE `id2`='" . $id . "' ";
		$beforeChangeEntity = Cms::$db->getRow($q);	
		        
        $product = new ProductsAdmin();
        
		if ($beforeChangeEntity['qty'] == 0 && $data['qty'] > 0) {				
			$product->stockAvailabilityNotificationsSend($beforeChangeEntity);
		}                
        
		$q = "UPDATE `" . $this->table . "_variation` SET `qty`='" . (int) $data['qty'] . "' ";
		if (isset($data['price']))
			$q.= ", `tax_id`='" . $tax['id'] . "', `price`='" . $data['price'] . "' ";
		$q.= "WHERE `id2`='" . (int) $id . "' ";                
        
		if (Cms::$db->update($q)) {
			return true;
		}
		return false;
	}

	public function get_tax_by_value($val = 0) {
		$q = "SELECT a.* FROM `" . DB_PREFIX . "taxes` a WHERE a.value='" . addslashes($val) . "' ";
		if ($item = Cms::$db->getRow($q)) {
			$item = mstripslashes($item);
			return $item;
		}
		return false;
	}

	// pobieranie do GA

	public function get_products_updated($last_day = 1) {
		$time_last = time() - 60 * 60 * 24 * $last_day;

		$q = "SELECT a.*, b.name, b.content FROM `" . $this->table . "` a LEFT JOIN `" . $this->table . "_translation` b ON a.id=b.translatable_id ";
		$q.= "WHERE a.date_mod>FROM_UNIXTIME('" . $time_last . "') AND b.locale='en' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['variations'] = array();
			$v['images'] = array();

			$q = "SELECT a.*, b.value as `tax` FROM `" . $this->table . "_variation` a LEFT JOIN `" . DB_PREFIX . "taxes` b ON a.tax_id=b.id ";
			$q.= "WHERE a.product_id='" . (int) $v['id'] . "' ";
			$array2 = Cms::$db->getAll($q);
			foreach ($array2 as $v2) {
				$v2 = mstripslashes($v2);
				$v['variations'][] = $v2;
			}

			$q = "SELECT a.* FROM `" . $this->table . "_image` a WHERE a.product_id='" . (int) $v['id'] . "' ";
			$array2 = Cms::$db->getAll($q);
			foreach ($array2 as $v2) {
				$v2 = mstripslashes($v2);
				$tmp = get_photo($this->img_dir, SERVER_URL . $this->img_url, $v2['file']);
				$v2['url'] = $tmp['normal'];
				$v['images'][] = $v2;
			}

			$items[] = $v;
		}
		return $items;
	}

}
