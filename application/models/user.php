<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class User {

	private $table;
	private $tableUsersActions;    

	public function __construct() {
		$this->table = DB_PREFIX . 'users';
		$this->tableUsersActions = DB_PREFIX . 'users_actions';
	}

	public function __destruct() {
		
	}

	public function logged() {   // sprawdzamy czy użytkownik jest zalogowany
		if (isset($_SESSION[USER_CODE . '_id'])) {
			$user_id = addslashes($_SESSION[USER_CODE . '_id']);
			$q = "SELECT `id` FROM `" . $this->table . "` WHERE SHA1(CONCAT(id,login,pass,email))='" . $user_id . "' AND `active`='1' ";
			$tmp = Cms::$db->getRow($q);
			if ($tmp['id'] > 0)
				return true;
		}
		return false;
	}

	// funkcja loguje uzytkownika do serwisu
	public function login($post) {
		if (isset($post['login']))
			$login = addslashes(strtolower($post['login']));
		if (isset($post['pass']))
			$pass_hash = setHash($post['pass'], SALT2);

		if (empty($login) or empty($post['pass'])) {
			$msg = 'Login i hasło nie mogą być puste.';
			Cms::getFlashBag()->add('error', $msg);
			return false;
		} else {
			$q = "SELECT * FROM " . $this->table . " WHERE LOWER(`login`)='" . $login . "' ";
			if (!Cms::$db->getOne($q)) {
				$msg = 'Brak użytkownika o podanym loginie.';
				Cms::getFlashBag()->add('error', $msg);
				return false;
			} else {
				$q = "SELECT * FROM " . $this->table . " WHERE LOWER(`login`)='" . $login . "' AND `pass`='" . addslashes($pass_hash) . "' ";
				$res = Cms::$db->getRow($q);
				if (!$res) {
					$msg = 'Podane hasło jest niepoprawne.';
					Cms::getFlashBag()->add('error', $msg);
					return false;
				} else {
					if ($res['active'] != 1) {
						$msg = 'Konto użytkownika jest nieaktywne.';
						Cms::getFlashBag()->add('error', $msg);						
						return false;
					} else {
						$res['shid'] = sha1($res['id']);
						$res['privilege'] = $this->setPrivilege($res['privilege']);
						$res['available_actions'] = $this->setAvailableActions($res['available_actions']);
                        
						$_SESSION[USER_CODE . '_id'] = sha1($res['id'] . $res['login'] . $res['pass'] . $res['email']);
						unset($res['pass']);
						$_SESSION[USER_CODE] = $res;
                        
						$msg = 'Logowanie poprawne. Witaj w panelu administracyjnym.';
						Cms::getFlashBag()->add('info', $msg);						
						return true;
					}
				}
			}
		}
	}

    public static function property($propertyName) {
        return isset($_SESSION[USER_CODE][$propertyName]) ? $_SESSION[USER_CODE][$propertyName] : false;
    }
    
	// funkcja sprawdzamy uprawnienia uzytkownika logujacego sie
	public function setPrivilege($privilege) {
		$privilege = explode('|', $privilege);
		$q = "SELECT * FROM `" . Cms::$tableModules . "` ORDER BY `id` ASC ";
		$array = Cms::$db->getAll($q);
		$priv = array();
		foreach ($array as $v) {
			$priv[$v['name']] = 0;
			if ($v['active'] == 1) {
				$priv[$v['name']] = in_array($v['id'], $privilege) ? 1 : 0;
			}
		}
		return $priv;
	}
    
	public function setAvailableActions($availableActions) {
		$availableActions = explode('|', $availableActions);
		$q = "SELECT * FROM `" . $this->tableUsersActions . "` ORDER BY `id` ASC ";
        
		$array = Cms::$db->getAll($q);
		$actions = array();
		foreach ($array as $v) {
			$actions[$v['name']] = 0;
			if ($v['active'] == 1) {
				$actions[$v['name']] = in_array($v['id'], $availableActions) ? 1 : 0;
			}
		}        
		return $actions;
	}

	public function logout() {
		unset($_SESSION[USER_CODE . '_id']);
		unset($_SESSION[USER_CODE]);
		Cms::getFlashBag()->add('info', 'Użytkownik wylogowany.');
		return true;
	}

	public function loadAdmin() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `login` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['shid'] = sha1($v['id']);
			$items[] = $v;
		}
		return $items;
	}

	public function loadAdminById($sid = '') {
		if ($sid) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE SHA1(`id`)='" . $sid . "' ";
			$item = Cms::$db->getRow($q);
			$item = mstripslashes($item);
			$item['shid'] = sha1($item['id']);
            
//			if ($_SESSION[USER_CODE]['level'] == 1) {
				$privilege = explode('|', $item['privilege']);
				$q = "SELECT * FROM `" . Cms::$tableModules . "`WHERE `type`=1 ORDER BY `id` ASC ";
				$array = Cms::$db->getAll($q);
				$priv = array();
				foreach ($array as $v) {
					$v = mstripslashes($v);
					if ($v['active'] == 1) {
						$v['status'] = in_array($v['id'], $privilege) ? 1 : 0;
						unset($v['active']);
						$priv[] = $v;
					}
				}
				$item['privilege'] = $priv;
                
                //available actions
				$availableActions = explode('|', $item['available_actions']);
				$q = "SELECT * FROM `" . $this->tableUsersActions . "` ORDER BY `id` ASC ";
				$array = Cms::$db->getAll($q);
				$actions = array();
				foreach ($array as $v) {
					$v = mstripslashes($v);
					if ($v['active'] == 1) {
						$v['status'] = in_array($v['id'], $availableActions) ? 1 : 0;
						unset($v['active']);
						$actions[] = $v;
					}
				}
				$item['available_actions'] = $actions;                
//			}
            
			return $item;
		}
		return false;
	}

	public function add($post) {
		$post['login'] = strtolower($post['login']);
		$post = maddslashes($post);
		$pass = setHash($post['pass'], SALT2);

		if ($this->checkDataAdd($post)) {
			$q = "INSERT INTO " . $this->table . " SET `login`='" . $post['login'] . "', `pass`='" . $pass . "', `name`='" . $post['name'] . "', `surname`='" . $post['surname'] . "', ";
			$q.= "`email`='" . $post['email'] . "', `level`='" . $post['level'] . "', `active`='" . $post['active'] . "', `privilege`='" . $post['active'] . "', date_add=NOW(), date_pass=NOW() ";
			if (Cms::$db->insert($q)) {
				return true;
			}
			return false;
		}
	}

	public function edit($post) {
		$post = maddslashes($post);
		if ($this->checkDataEdit($post)) {
			$q = "UPDATE " . $this->table . " SET `name`='" . $post['name'] . "', ";
			$q.= "`surname`='" . $post['surname'] . "', `email`='" . $post['email'] . "', `level`='" . $post['level'] . "', `active`='" . $post['active'] . "' ";
			$q.= "WHERE SHA1(`id`)='" . $post['sid'] . "' ";
			if ($_SESSION[USER_CODE]['level'] != 1)
				$q.= "AND `level`!=1 ";
			if (Cms::$db->update($q)) {
                $_SESSION[USER_CODE]['level'] = $post['level'];
				return true;
			}

			return false;
		}
	}

	public function editPass($post) {
		$post = maddslashes($post);
		$pass = setHash($post['pass'], SALT2);
		if ($this->checkDataPass($post)) {
			$q = "UPDATE " . $this->table . " SET `pass`='" . $pass . "', date_pass=NOW() WHERE SHA1(`id`)='" . $post['sid'] . "' ";
			if ($_SESSION[USER_CODE]['level'] != 1)
				$q.= "AND `level`!='1' ";
			if (Cms::$db->update($q)) {
				if ($_SESSION[USER_CODE]['sid'] == $post['sid']) {
					$user['login'] = $_SESSION[USER_CODE]['login'];
					$user['pass'] = $post['pass'];
					$this->login($user);
				}

				return true;
			}

			return false;
		}
	}

	public function editPriv($post) {
//		if ($_SESSION[USER_CODE]['level'] in [1,2]) {
		if (in_array($_SESSION[USER_CODE]['level'], [1,2])) {
			$privilege = implode('|', $post['privilege']);
			$q = "UPDATE " . $this->table . " SET `privilege`='" . $privilege . "' WHERE SHA1(`id`)='" . $post['sid'] . "' ";
            
			if (Cms::$db->update($q)) {
				if ($_SESSION[USER_CODE]['shid'] == $post['sid']) {
					$_SESSION[USER_CODE]['privilege'] = $this->setPrivilege($privilege);
					Cms::$twig->addGlobal('user', $_SESSION[USER_CODE]);
				}
				Cms::getFlashBag()->add('info', 'Zapisano zmiany dla wybranego użytkownika.');
				return true;
			}
			Cms::getFlashBag()->add('error', 'Zmiana nie powiodła się.');
			return false;
		}
		Cms::getFlashBag()->add('error', 'Nie masz uprawnień do tej zmiany!');
		return false;
	}
    
	public function editActions($post) {
		if ($_SESSION[USER_CODE]['level'] == 1) {
            $post['available_actions'] = isset($post['available_actions']) ? $post['available_actions'] : [];
			$availableActions = implode('|', $post['available_actions']);
			$q = "UPDATE " . $this->table . " SET `available_actions`='" . $availableActions . "' WHERE SHA1(`id`)='" . $post['sid'] . "' ";
            
			if (Cms::$db->update($q)) {
				if ($_SESSION[USER_CODE]['shid'] == $post['sid']) {
					$_SESSION[USER_CODE]['available_actions'] = $this->setAvailableActions($availableActions);
					Cms::$twig->addGlobal('user', $_SESSION[USER_CODE]);
				}
				return true;
			}
			return false;
		}

		return false;
	}

	public function checkDataAdd($post) {
		if (!checkLogin($post['login'])) {
			Cms::getFlashBag()->add('error', 'Login powinień składać się z liter, cyfr lub podkreślenia "_", powinien mieć długość co najmniej 3 znaków.');
			return false;
		} elseif (!$this->userExists($post['login'])) {
			Cms::getFlashBag()->add('error', 'Użytkownik o podanym loginie już jest zarejestrowany!');
			return false;
		} elseif (!checkPassword($post['pass'])) {
			Cms::getFlashBag()->add('error', 'Hasło powinno mieć co najmniej 8 znaków, zawierać cyfrę i wielką literą.');
			return false;
		} elseif (!checkEmail($post['email'])) {
			Cms::getFlashBag()->add('error', 'Podany adres e-mail jest nieprawidłowy!');
			return false;
		} elseif (!$this->emailExists($post['email'])) {
			Cms::getFlashBag()->add('error', 'Użytkownik o podanym adresie e-mail już jest zarejestrowany!');
			return false;
		} else {
			return true;
		}
	}

	public function checkDataEdit($post) {
		if (!checkEmail($post['email'])) {
			Cms::getFlashBag()->add('error', 'Podany adres e-mail jest nieprawidłowy!');
			return false;
		} elseif (!$this->emailExists($post['email'], $post['sid'])) {
			Cms::getFlashBag()->add('error', 'Użytkownik o podanym adresie e-mail już jest zarejestrowany!');
			return false;
		} else {
			return true;
		}
	}

	public function checkDataPass($post) {
		if (!checkPassword($post['pass'])) {
			Cms::getFlashBag()->add('error', 'Hasło powinno mieć co najmniej 8 znaków, zawierać cyfrę i wielką literą.');
			return false;
		} elseif ($post['pass'] != $post['pass2']) {
			Cms::getFlashBag()->add('error', 'Wpisane hasła powinny być identyczne.');
			return false;
		} else {
			return true;
		}
	}

	public function userExists($login) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `login`='" . $login . "' ";
		if (Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}

	public function emailExists($email, $sid = '') {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `email`='" . $email . "' ";
		if ($sid)
			$q.= "AND SHA1(`id`)!='" . $sid . "' ";
		if (Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}

	public function delete($sid) {
		if ($sid) {
			$q = "DELETE FROM " . $this->table . " WHERE SHA1(`id`)='" . $sid . "' AND `level`!=1 ";
			if (Cms::$db->delete($q)) {
				return false;
			}
		}

		return false;
	}
	
	public function load_status()
   {
      $q = "SELECT * FROM `".DB_PREFIX."status` ORDER BY `id` ASC "; 
      $array = Cms::$db -> getAll($q);
      $items = array();
      foreach($array as $v)
      {
         $items[$v['id']] = $v;
      }
      return $items;
   }
   
   public function count() {
       $q = "SELECT COUNT(*) as rows FROM `" . $this->table . "`";
       $result = Cms::$db->getRow($q);
       
       return $result['rows'];
   }
   

}
