<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
require_once(CMS_DIR . '/application/models/mailer.php');

class Comments {

	private $mailer;

	public function __construct($group = '') {
		$this->mailer = new Mailer();
		$this->table = DB_PREFIX . 'comments';
		$this->group = $group;
	}

	public function __destruct() {
		
	}

	public function loadComments($parent_id = 0, $limitStart = 0, $limit = 10) {
		$q = "SELECT c.* ";
		$q.= "FROM `" . $this->table . "` c WHERE c.parent_id='" . (int) $parent_id . "' AND c.lang_id='" . _ID . "' AND c.group='" . $this->group . "' AND c.active=1 ";
		$q.= "ORDER BY c.date_add DESC LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$d1 = explode(' ', $v['date_add']);
			$d2 = explode('-', $d1[0]);
			$v['date_add'] = substr($d1[1], 0, 5) . ' | ' . $d2[2] . '.' . $d2[1] . '.' . $d2[0];
			$items[] = $v;
		}
		return $items;
	}

	public function getPages($parent_id = 0, $limit = 10) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` WHERE `lang_id`='" . _ID . "' AND `parent_id`='" . (int) $parent_id . "' AND `group`='" . $this->group . "' AND `active`=1 ";
		$v = Cms::$db->max($q);
		$v[2] = $v[0];
		if ($v[0] < 1)
			$v[0] = 1;
		$v[1] = ceil($v[0] / $limit);
		return $v;
	}

	public function loadNote($id) {
		$q = "SELECT SUM(`note`) as sum, COUNT(`id`) as count FROM `" . $this->table . "` ";
		$q.= "WHERE `parent_id`='" . (int) $id . "' AND `group`='shop' ";
		$v = Cms::$db->getRow($q);
		if ($v['count'] > 0)
			return round($v['sum'] / $v['count']);
		return false;
	}

	public function add($post, $parent_id, $table = '', $ping = 0) {
		if (empty($post['desc'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['comments_check_content']);
			return false;
		} elseif ($this->commentExist($post['desc'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['comments_exist']);
			return false;
		} elseif (LOGGED != 1 AND ( !isset($_SESSION['captcha']) OR $post['captcha'] != base64_decode($_SESSION['captcha']))) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['captcha_check']);
			return false;
		} elseif ($post['code'] != 'MT028lesijFL67dkhjcXT082gdkpy') {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['code']);
			return false;
		}

		$desc = addslashes($post['desc']);
		if (!LOGGED)
			$post['author'] = '~' . $post['author'];
		$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : 0;
		$post['note'] = isset($post['note']) ? $post['note'] : 5;
		if (isset($post['uid'])) {
			$q = "SELECT `customer_id` FROM `" . DB_PREFIX . "shop_orders` WHERE MD5(CONCAT(`id`,`customer_id`,`date_add`))='" . addslashes($post['uid']) . "'  ";
			if ($customer = Cms::$db->getRow($q)) {
				$customer_id = $customer['customer_id'];
			}
		}

		$q = "INSERT INTO " . $this->table . " SET `parent_id`='" . (int) $parent_id . "', `customer_id`='" . $customer_id . "', `lang_id`='" . _ID . "', ";
		$q.= "`group`='" . $this->group . "', `desc`='" . $desc . "', `author`='" . $post['author'] . "', `date_add`=NOW(), ";
		$q.= "`note`='" . $post['note'] . "', `active`=1 ";
		if ($id = Cms::$db->insert($q)) {
			unset($_POST);
			if ($ping == 1 AND ! empty($table)) {
				$this->addPingCron($parent_id, $this->group, $table);
			}
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['comments_thank']);
			$data['id'] = $id;
			$data['customer_id'] = $customer_id;
			return $data;
		}
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['comments_no_add']);
		return false;
	}

	public function commentExist($desc) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `desc`='" . addslashes($desc) . "' ";
		$row = Cms::$db->getRow($q);
		if ($row['id'] > 0)
			return true;
		return false;
	}

	public function loadAdmin($limitStart = 0, $limit = 50, $filtr) {
		$q = "SELECT c.*, ";
		$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "shop_products_desc` WHERE `lang_id`=c.lang_id AND `parent_id`=p.id LIMIT 1) as name_url, ";
		$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "shop_categories_desc` WHERE `lang_id`=c.lang_id AND `parent_id`=p.category_id LIMIT 1) as category_url, ";
		$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "shop_categories_desc` WHERE `lang_id`=c.lang_id AND `parent_id`=p.category_id LIMIT 1) as parent_url ";
		$q.= "FROM `" . $this->table . "` c LEFT JOIN `" . DB_PREFIX . "shop_products` p ON c.parent_id=p.id ";
		$q.= $filtr;
		$q.= "ORDER BY c.date_add DESC, c.id DESC LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			if ($v['lang_id'] == 2)
				$URL = '/pl';
			else
				$URL = '';
			if ($v['parent_url'])
				$v['url'] = SERVER_URL . CMS_URL . $URL . '/product/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			else
				$v['url'] = SERVER_URL . CMS_URL . $URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($limit = 50, $filtr) {
		$q = "SELECT COUNT(c.id) FROM `" . $this->table . "` c ";
		$q.= $filtr;
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function setFiltr($filtr) {
		$q = "WHERE 1=1 ";
		if ($filtr['active'] == 1)
			$q.= "AND c.active=1 ";
		if ($filtr['active'] == 2)
			$q.= "AND c.active!=1 ";
		if (!empty($filtr['group']))
			$q.= "AND c.group='" . addslashes($filtr['group']) . "' ";
		if ($filtr['lang'] > 0)
			$q.= "AND c.lang_id='" . addslashes($filtr['lang']) . "' ";
		return $q;
	}

	public function getGroups() {
		$q = "SELECT `group` FROM `" . $this->table . "` ";
		$q.= "GROUP BY `group` ORDER BY `group` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function edit($post) {
		$desc = addslashes($post['desc']);
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['note'] = isset($post['note']) ? $post['note'] : 0;
		if ($post['note'] == 1) {
			if ($post['lang_id'] == 1)
				$note = 'Part of the entry has been removed due to terms of use.';
			else
				$note = 'Część wpisu została usunięta ze względu na regulamin serwisu.';
			$desc.= ' <br /><i>' . $note . '</i>';
		}

		$q = "UPDATE " . $this->table . " SET `desc`='" . $desc . "', `active`='" . $post['active'] . "' ";
		$q.= "WHERE `id`='" . (int) $post['id'] . "'";
		if (Cms::$db->update($q)) {
			Cms::getFlashBag()->add('info', 'Zapisano zmiany dla komentarza.');
			return true;
		}
		Cms::getFlashBag()->add('error', 'Zmiana komentarza nie powiodła się!');
		return false;
	}

	function delete($id) {
		if ($id) {
			$q = "SELECT * FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			$comments = Cms::$db->getRow($q);
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);

			Cms::getFlashBag()->add('error', 'Wybrany komentarz usunięto.');
			return true;
		}
		Cms::getFlashBag()->add('error', 'Usuwanie komentarza nie powiodło się!');
		return false;
	}

	function getUserId($id) {
		if ($id) {
			$q = "SELECT `customer_id` FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			$row = Cms::$db->getRow($q);
			return $row['customer_id'];
		}
		return false;
	}

	public function loadCommentsByCustomer($id = 0) {
		$q = "SELECT c.*, ";
		$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "product_desc` WHERE `lang_id`=c.lang_id AND `parent_id`=p.id LIMIT 1) as name_url, ";
		$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "product_category_desc` WHERE `lang_id`=c.lang_id AND `parent_id`=p.category_id LIMIT 1) as category_url ";
		$q.= "FROM `" . $this->table . "` c LEFT JOIN `" . DB_PREFIX . "product` p ON c.parent_id=p.id ";
		$q.= "WHERE c.customer_id='" . (int) $id . "' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$d1 = explode(' ', $v['date_add']);
			$d2 = explode('-', $d1[0]);
			$v['date_add'] = substr($d1[1], 0, 5) . ' | ' . $d2[2] . '.' . $d2[1] . '.' . $d2[0];
			if ($v['group'] == 'shop') {
				if ($v['lang_id'] == 2)
					$URL = '/pl';
				else
					$URL = '';
				$v['url'] = URL . '/product/' . $v['category_url'] . '/' . $v['name_url'] . '.html';
			}
			$items[] = $v;
		}
		return $items;
	}

}
