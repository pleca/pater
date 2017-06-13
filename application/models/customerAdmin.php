<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/customer.php');

class CustomerAdmin extends Customer {

	public $mailer;
	public $table;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->table = DB_PREFIX . 'customer';
	}

	public function __destruct() {
		
	}

	public function loadAdmin($filtr = '', $limitStart = 0, $limit = 100) {
		$q = "SELECT * FROM `" . $this->table . "` ";
		if (isset($filtr['action'])) {
			$q.= "WHERE 1 ";
			if ($filtr['first_name'])
				$q.= "AND `first_name` LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND `last_name` LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND `email` LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['login'])
				$q.= "AND `login` LIKE '" . $filtr['login'] . "%' ";
		}
		$q.= "ORDER BY `login` ASC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($filtr = '', $limit = 100) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` ";
		if (isset($filtr['action'])) {
			$q.= "WHERE 1 ";
			if ($filtr['first_name'])
				$q.= "AND `first_name` LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND `last_name` LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND `email` LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['login'])
				$q.= "AND `login` LIKE '" . $filtr['login'] . "%' ";
		}
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadByIdAdmin($id = 0) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->table . "` ";
			$q.= "WHERE `id`='" . $id . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				if ($v['country'] == 1) {
					$t = explode(' ', $v['post_code']);
					$v['post_code1'] = $t[0];
					if (isset($t[1]))
						$v['post_code2'] = $t[1];
					else
						$v['post_code2'] = '';
				}                
				return $v;
			}
		}
		return false;
	}

	public function addAdmin($post) {
		$post = maddslashes($post);
		$post['login'] = strtolower($post['login']);
		$post['post_code'] = strtoupper($post['post_code']);
		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}
		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}
		$post['id'] = 0;
		
		$only_netto_prices = isset($post['only_netto_prices']) ? $post['only_netto_prices'] : 0;
		
		$result = $this->checkDataAdmin($post, 0, 1);
		
		if ($result === true) {  // dane, edit, pasword
			$pass = setHash($post['pass'], SALT2);
			$q = "INSERT INTO " . $this->table . " SET `login`='" . $post['login'] . "', `pass`='" . $pass . "', `type`='" . $post['type'] . "', ";
			$q.= "`company_name`='" . $post['company_name'] . "', `nip`='" . $post['nip'] . "', `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
			$q.= "`address1`='" . $post['address1'] . "', `address2`='" . $post['address2'] . "', `address3`='" . $post['address3'] . "', `post_code`='" . $post['post_code'] . "', ";
			$q.= "`city`='" . $post['city'] . "', `country`='" . $post['country'] . "', `email`='" . $post['email'] . "', ";
            
            if (CMS::$modules['price_groups']) {
                $q.= "`price_group`='" . $post['price_group'] . "', ";
            }
            
			$q.= "`only_netto_prices`='" . $only_netto_prices . "', ";	
			
			$q.= "`phone`='" . $post['phone'] . "', `discount`='" . $post['discount'] . "', `active`='1', `date_add`=NOW() ";
			if ($id = Cms::$db->insert($q)) {
				if ($post['lang'] == 'pl')
					$l = 2;
				else
					$l = 1;
				$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
				$q.= "`email`='" . $post['email'] . "', `lang_id`='" . $l . "', `active`='1' ";
				Cms::$db->insert($q);

				return $id;
			}

			return false;
			
		} else {
			return $result;
		}
		
	}

	public function editAdmin($post) {
		$post = maddslashes($post);

		if ($post['country'] == 1)
			$post['post_code'] = strtoupper($post['post_code1'] . ' ' . $post['post_code2']);
		else
			$post['post_code'] = strtoupper($post['post_code']);
		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}
		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}

		$new_pass = isset($post['new_pass']) ? $post['new_pass'] : 0;
		$only_netto_prices = isset($post['only_netto_prices']) ? $post['only_netto_prices'] : 0;
		
		$result = $this->checkDataAdmin($post, 1, $new_pass);
		if ($result === true) {
			$pass = setHash($post['pass'], SALT2);
			$q = "UPDATE " . $this->table . " SET `type`='" . $post['type'] . "', `company_name`='" . $post['company_name'] . "', ";
			$q.= "`nip`='" . $post['nip'] . "', `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
			$q.= "`address1`='" . $post['address1'] . "', `address2`='" . $post['address2'] . "', `address3`='" . $post['address3'] . "', `post_code`='" . $post['post_code'] . "', ";
			$q.= "`city`='" . $post['city'] . "', `country`='" . $post['country'] . "', `phone`='" . $post['phone'] . "', `sales_representative`='" . $post['sales_representative'] . "', ";
            
            if (CMS::$modules['price_groups']) {
                $q.= "`price_group`='" . $post['price_group'] . "', ";
            }

			if ($new_pass == 1)
				$q.= "`pass`='" . $pass . "', ";
			
			$q.= "`only_netto_prices`='" . $only_netto_prices . "', ";			
			
			$q.= "`discount`='" . $post['discount'] . "', `active`='" . $post['active'] . "' WHERE `id`='" . (int) $post['id'] . "' ";
			if (Cms::$db->update($q)) {				
				return true;
			}
			
			return false;
		} else {
			return $result;
		}
	}

	public function checkDataAdmin($data, $edit = 0, $pass = 0) {
		if (strlen($data['first_name']) < 2) {
			return $GLOBALS['LANG']['customers_check_f_name'];
		} elseif (strlen($data['last_name']) < 2) {
			return $GLOBALS['LANG']['customers_check_l_name'];
		} elseif ($edit != 1 AND ! checkEmail($data['email'])) {
			return $GLOBALS['LANG']['customers_check_email'];
		} elseif ($edit != 1 AND ! $this->emailExists($data['email'], $data['id'])) {
			return $GLOBALS['LANG']['customers_check_email_exist'];
		} elseif ($edit != 1 AND ! checkLogin($data['login'])) {
			return $GLOBALS['LANG']['customers_check_login'];
		} elseif ($edit != 1 AND ! $this->userExists($data['login'])) {
			return $GLOBALS['LANG']['customers_check_exist'];
		} elseif (($edit != 1 AND strlen($data['pass']) < 5) OR ( $edit == 1 AND $pass == 1 AND strlen($data['pass']) < 5)) {
			return $GLOBALS['LANG']['customers_check_password'];
		} elseif ($data['type'] == 2 AND strlen($data['company_name']) < 2) {
			return $GLOBALS['LANG']['customers_check_firm'];
		} elseif ($data['type'] == 2 AND strlen($data['nip']) < 5) {
			return $GLOBALS['LANG']['customers_check_nip'];            
		} elseif ($edit != 1 && strlen($data['address1']) < 1) {
			return $GLOBALS['LANG']['customers_check_address1'];
		} elseif ($edit != 1 && strlen($data['post_code']) < 5) {
			return $GLOBALS['LANG']['customers_check_postcode'];
		} elseif ($edit != 1 && strlen($data['city']) < 2) {
			return $GLOBALS['LANG']['customers_check_city'];
		} elseif ($edit != 1 && strlen($data['phone']) < 3) {
			return $GLOBALS['LANG']['customers_check_phone'];
		} else {
			return true;
		}
	}
	
	public function checkDataAdmin2($data, $edit = 0, $pass = 0) {
		if (strlen($data['first_name']) < 2) {
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_f_name']);
			return false;
		} elseif (strlen($data['last_name']) < 2) {echo 'b'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_l_name']);
			return false;
		} elseif ($edit != 1 AND ! checkEmail($data['email'])) { echo 'c'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_email']);
			return false;
		} elseif ($edit != 1 AND ! $this->emailExists($data['email'], $data['id'])) { echo 'd'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_email_exist']);
			return false;
		} elseif ($edit != 1 AND ! checkLogin($data['login'])) {echo 'e'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_login']);
			return false;
		} elseif ($edit != 1 AND ! $this->userExists($data['login'])) { echo 'f'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_exist']);
			return false;
		} elseif (($edit != 1 AND strlen($data['pass']) < 5) OR ( $edit == 1 AND $pass == 1 AND strlen($data['pass']) < 5)) { echo 'g'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_password']);
			return false;
		} elseif ($data['type'] == 2 AND strlen($data['company_name']) < 2) {echo 'h'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_firm']);
			return false;
		} elseif ($data['type'] == 2 AND strlen($data['nip']) < 5) {echo 'i'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_nip']);
			return false;
		} elseif (strlen($data['address1']) < 1) {echo 'j'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_address1']);
			return false;
		} elseif (strlen($data['post_code']) < 5) {echo 'k'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_postcode']);
			return false;
		} elseif (strlen($data['city']) < 2) {echo 'l'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_city']);
			return false;
		} elseif (strlen($data['phone']) < 3) {echo 'm'; die;
			Cms::$tpl->setError($GLOBALS['LANG']['customers_check_phone']);
			return false;
		} else {
			return true;
		}
	}	

	public function deleteAdmin($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			return Cms::$db->delete($q);
		}
		
		return false;
	}

}
