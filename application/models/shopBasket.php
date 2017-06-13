<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class Basket {

	public $table;
	public $tableProducts;
	public $dir;
	public $url;

	public function __construct() {
		$this->table = DB_PREFIX . 'shop_basket';
		$this->tableProducts = DB_PREFIX . 'shop_products';
		$this->tablePhotos = DB_PREFIX . 'shop_photos';
		$this->tableTax = DB_PREFIX . 'shop_tax';
		$this->tableCategories = DB_PREFIX . 'shop_categories';
		$this->dir = CMS_DIR . '/files/products';
		$this->url = CMS_URL . '/files/products';
	}

	public function __destruct() {
		
	}

	public function loadBasket() {
		$this->deleteBasketOld();   // usuwamy produkty z koszyka starsze niz 1 godzina

		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "SELECT b.*, p.qty as `qty_magazyn`, t.value as tax_val, ";
		$q.= "(SELECT `file` FROM `" . $this->tablePhotos . "` WHERE parent_id=p.id ORDER BY `order` ASC LIMIT 1) as file ";
		$q.= "FROM `" . $this->table . "` b LEFT JOIN `" . $this->tableProducts . "` p ON b.product_id=p.id LEFT JOIN `" . $this->tableTax . "` t ON p.tax_id=t.id ";
		$q.= "WHERE b.session_id='" . session_id() . "' AND b.customer_id='" . $customer_id . "' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['price_gross'] = formatPrice($v['price'], $v['tax_val']);
			if ($v['promotion'] == 1) {
				$v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax_val']);
				$v['sum'] = formatPrice($v['qty'] * $v['price_promotion_gross']);
			} else {
				$v['sum'] = formatPrice($v['qty'] * $v['price_gross']);
			}
			$v['photo'] = $this->getSrc($v['file']);
			$items[] = $v;
		}
		return $items;
	}

	public function loadBasketOrder() {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "SELECT b.*, p.price_purchase, p.sku, p.producer_id, t.value as tax_val FROM `" . $this->table . "` b ";
		$q.= "LEFT JOIN `" . $this->tableProducts . "` p ON b.product_id=p.id LEFT JOIN `" . $this->tableTax . "` t ON p.tax_id=t.id ";
		$q.= "WHERE b.session_id='" . session_id() . "' AND b.customer_id='" . $customer_id . "' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['price_gross'] = formatPrice($v['price'], $v['tax_val']);
			if ($v['promotion'] == 1) {
				$v['price_promotion_gross'] = formatPrice($v['price_promotion'], $v['tax_val']);
				$v['sum'] = formatPrice($v['qty'] * $v['price_promotion_gross']);
			} else {
				$v['sum'] = formatPrice($v['qty'] * $v['price_gross']);
			}
			$items[] = $v;
		}
		return $items;
	}

	function getInfo() {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "SELECT b.*, t.value as tax_val FROM " . $this->table . " b ";
		$q.= "LEFT JOIN `" . $this->tableProducts . "` p ON b.product_id=p.id LEFT JOIN `" . $this->tableTax . "` t ON p.tax_id=t.id ";
		$q.= "WHERE b.session_id='" . session_id() . "' AND b.customer_id='" . $customer_id . "' ";
		$array = Cms::$db->getAll($q);
		$w['count'] = 0;
		$w['qty'] = 0;
		$w['weight'] = 0;
		$w['sum'] = 0;
		foreach ($array as $v) {
			$w['count'] ++;
			$w['qty']+= $v['qty'];
			$w['weight']+= $v['qty'] * $v['weight'];
			if ($v['promotion'] == 1) {
				$w['sum']+= $v['qty'] * (round($v['price_promotion'] + $v['price_promotion'] * $v['tax_val'] / 100, 2));
			} else {
				$w['sum']+= $v['qty'] * (round($v['price'] + $v['price'] * $v['tax_val'] / 100, 2));
			}
			$w['sum'] = formatPrice($w['sum']);
		}
		return $w;
	}

	public function loadSmallItems() {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "SELECT b.*, t.value as tax_val, (SELECT `file` FROM `" . $this->tablePhotos . "` WHERE parent_id=p.id ORDER BY `order` ASC LIMIT 1) as file ";
		$q.= "FROM `" . $this->table . "` b ";
		$q.= "LEFT JOIN `" . $this->tableProducts . "` p ON b.product_id=p.id LEFT JOIN `" . $this->tableTax . "` t ON p.tax_id=t.id ";
		$q.= "WHERE b.session_id='" . session_id() . "' AND b.customer_id='" . $customer_id . "' ORDER BY `time_add` DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			if (strlen($v['name']) > 13)
				$v['name'] = substr($v['name'], 0, 13) . '..';
			$v['photo'] = $this->getSrc($v['file']);
			if ($v['promotion'] == 1) {
				$v['sum'] = formatPrice($v['qty'] * (round($v['price_promotion'] + $v['price_promotion'] * $v['tax_val'] / 100, 2)));
			} else {
				$v['sum'] = formatPrice($v['qty'] * (round($v['price'] + $v['price'] * $v['tax_val'] / 100, 2)));
			}
			$items[] = $v;
		}
		return $items;
	}

	public function productExists($id) {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "SELECT `product_id` FROM `" . $this->table . "` WHERE `session_id`='" . session_id() . "' ";
		$q.= "AND `product_id`='" . (int) $id . "' AND `customer_id`='" . $customer_id . "' ";
		if (Cms::$db->getRow($q)) {
			return true;
		}
		return false;
	}

	public function deleteBasketOld() {
		$q = "SELECT `session_id`, `customer_id`, `product_id`, `qty` FROM `" . $this->table . "` WHERE `time_add`<DATE_SUB(NOW(), INTERVAL 1 HOUR) ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$q = "UPDATE " . $this->tableProducts . " SET `qty`=`qty`+" . $v['qty'] . " WHERE `id`='" . (int) $v['product_id'] . "' ";
			Cms::$db->update($q);
			$q = "DELETE FROM " . $this->table . " WHERE `session_id`='" . $v['session_id'] . "' AND `customer_id`='" . $v['customer_id'] . "' AND `product_id`='" . $v['product_id'] . "' ";
			Cms::$db->delete($q);
		}
		return true;
	}

	public function deleteBasketAfterOrder() {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "DELETE FROM " . $this->table . " WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' ";
		Cms::$db->delete($q);
	}

	public function updateProducts($basket) {
		foreach ($basket as $v) {
			$q = "UPDATE " . $this->tableProducts . " SET `sold`=`sold`+" . (int) $v['qty'] . " WHERE `id`='" . (int) $v['product_id'] . "' ";
			Cms::$db->update($q);
		}
		return true;
	}

	public function loadProductId($id = 0) {
		if ($id > 0) {
			$q = "SELECT p.*, d.name, d.name_url, c1.name_url as category_url, t.name as tax, ";
			$q.= "(SELECT `name_url` FROM `" . $this->tableCategories . "_desc` WHERE parent_id=c.parent_id LIMIT 1) as parent_url ";
			$q.= "FROM `" . $this->tableProducts . "` p LEFT JOIN `" . $this->tableProducts . "_desc` d ON p.id=d.parent_id ";
			$q.= "LEFT JOIN `" . $this->tableCategories . "` c ON p.category_id=c.id ";
			$q.= "LEFT JOIN `" . $this->tableCategories . "_desc` c1 ON c1.parent_id=c.id ";
			$q.= "LEFT JOIN `" . $this->tableTax . "` t ON p.tax_id=t.id ";
			$q.= "WHERE d.lang_id='" . _ID . "' AND c1.lang_id='" . _ID . "' AND p.id='" . (int) $id . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				if ($v['parent_url'])
					$v['url'] = URL . '/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
				else
					$v['url'] = URL . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
				return $v;
			}
		}
		return false;
	}

	function editAll($products) {
		$i = 0;
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		foreach ($products as $product_id => $amount) {
			$q = "SELECT `qty` FROM `" . $this->table . "` ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			$item = Cms::$db->getRow($q);
			$qty_new = $qty - $item['qty'];  // o ile sie zwiekszylo

			$q = "SELECT `qty` FROM `" . $this->tableProducts . "` WHERE `id`='" . (int) $product_id . "' ";
			$item = Cms::$db->getRow($q);
			if ($item['qty'] < $qty_new)
				$qty_new = $item['qty'];

			$q = "UPDATE " . $this->table . " SET `qty`=`qty`+'" . $qty_new . "' ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			Cms::$db->update($q);

			$q = "UPDATE " . $this->tableProducts . " SET `qty`=`qty`-" . $qty_new . " WHERE `id`='" . (int) $product_id . "' ";
			Cms::$db->update($q);
			$i++;
		}
		if ($i > 0) {
			Cms::$tpl->setInfo($GLOBALS['LANG']['basket_change_amount']);
			return true;
		}
		return false;
	}

	function deleteAll($products) {
		$i = 0;
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		foreach ($products as $product_id) {
			$q = "SELECT `qty` FROM `" . $this->table . "` ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			$item = Cms::$db->getRow($q);

			$q = "DELETE FROM `" . $this->table . "` ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			Cms::$db->delete($q);

			$qty = $item['qty'] ? $item['qty'] : 0;
			$q = "UPDATE `" . $this->tableProducts . "` SET `qty`=`qty`+" . $qty . " WHERE `id`='" . (int) $product_id . "' ";
			Cms::$db->update($q);
			$i++;
		}
		if ($i > 0) {
			Cms::$tpl->setInfo($GLOBALS['LANG']['basket_delete_amount']);
			return true;
		}
		return false;
	}

	function deleteOne($product_id) {
		if ($product_id > 0) {
			$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
			$q = "SELECT `qty` FROM `" . $this->table . "` ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			$item = Cms::$db->getRow($q);

			$q = "DELETE FROM `" . $this->table . "` ";
			$q.= "WHERE `session_id`='" . session_id() . "' AND `customer_id`='" . $customer_id . "' AND `product_id`='" . (int) $product_id . "' ";
			Cms::$db->delete($q);

			$qty = $item['qty'] ? $item['qty'] : 0;
			$q = "UPDATE `" . $this->tableProducts . "` SET `qty`=`qty`+" . $qty . " WHERE `id`='" . (int) $product_id . "' ";
			Cms::$db->update($q);
			Cms::$tpl->setInfo($GLOBALS['LANG']['basket_delete_amount']);
			return true;
		}
		return false;
	}

	function saveBasket() {
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
		$q = "DELETE FROM `" . $this->table . "` WHERE `customer_id`='" . $customer_id . "' AND `customer_id`>0 AND `session_id`!='" . session_id() . "' ";
		Cms::$db->delete($q);
		$q = "UPDATE " . $this->table . " SET `session_id`='" . session_id() . "', `customer_id`='" . $customer_id . "' ";
		$q.= "WHERE (`customer_id`=0 AND `session_id`='" . session_id() . "') OR `customer_id`='" . $customer_id . "' ";
		Cms::$db->update($q);
	}

	function getSrc($fileName) {
		$fileNameS = changeFileName($fileName, '_s');
		$row = '';
		if (!empty($fileName) and file_exists($this->dir . '/' . $fileName)) {
			return $this->url . '/' . $fileNameS;
		}
		return false;
	}

}
