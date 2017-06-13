<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/mailer.php');
require_once(MODEL_DIR . '/EmailTemplate.php');

class Customer {

	public $mailer;
	public $table;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->table = DB_PREFIX . 'customer';
	}

	public function __destruct() {
		
	}

	public function logged() {
		if (isset($_SESSION[CUSTOMER_CODE . '_id'])) {
			$customer_id = addslashes($_SESSION[CUSTOMER_CODE . '_id']);
			$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` WHERE SHA1(CONCAT(id,login,pass,first_name,last_name,email))='" . $customer_id . "' AND `active`='1' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}
    
    public static function isLogged() {
		if (isset($_SESSION[CUSTOMER_CODE . '_id'])) {
			$customer_id = addslashes($_SESSION[CUSTOMER_CODE . '_id']);
            $table = DB_PREFIX . 'customer';
			$q = "SELECT COUNT(`id`) FROM `" . $table . "` WHERE SHA1(CONCAT(id,login,pass,first_name,last_name,email))='" . $customer_id . "' AND `active`='1' ";
            if (Cms::$db->getRow($q)) {
                return true;
            }
		}
		return false;
    }

	protected function loginExistWithoutPassword($login) {
		$q = "SELECT * FROM " . $this->table . " WHERE (LOWER(`login`)='" . $login . "' OR LOWER(`email`)='" . $login . "') AND (pass is null or pass = '')";
		$res = Cms::$db->getRow($q);
		
		if (!$res) {
			return false;
		}
		
		return $res;
	}
	
	public function login($post) {
		if (isset($post['login']))
			$login = addslashes(strtolower($post['login']));
		if (isset($post['pass']))
			$pass = $post['pass'];

		if (empty($login) or empty($pass)) {
			$msg = $GLOBALS['LANG']['c_sign1'];
			Cms::getFlashBag()->add('error', $msg);
			return false;
		} else {			
			
			$q = "SELECT * FROM " . $this->table . " WHERE LOWER(`login`)='" . $login . "' OR LOWER(`email`)='" . $login . "' ";
			if (!Cms::$db->getRow($q)) {
				$msg = $GLOBALS['LANG']['c_sign2'];
				Cms::getFlashBag()->add('error', $msg);
				return false;
			} else {
				
				if ($res = $this->loginExistWithoutPassword($login)) {
//					$machedParam = $login == $res['login'] ? 'login' : 'email';					
					redirect(URL . '/customer/expired-pass.html?login=' . $login);
				} else {

					$pass_hash = setHash($pass, SALT2);
					$q = "SELECT * FROM " . $this->table . " WHERE (LOWER(`login`)='" . $login . "' OR LOWER(`email`)='" . $login . "') AND `pass`='" . addslashes($pass_hash) . "' ";
					$res = Cms::$db->getRow($q);

					if (!$res) {
						$msg = $GLOBALS['LANG']['c_sign3'];
						Cms::getFlashBag()->add('error', $msg);
						return false;
					} else {
						if ($res['active'] != 1) {
							$msg = $GLOBALS['LANG']['c_sign4'];
							Cms::getFlashBag()->add('error', $msg);
							return false;
						} else {
							$_SESSION[CUSTOMER_CODE . '_id'] = sha1($res['id'] . $res['login'] . $res['pass'] . $res['first_name'] . $res['last_name'] . $res['email']);
							unset($res['pass']);
							$_SESSION[CUSTOMER_CODE] = $res;
							$msg = $GLOBALS['LANG']['c_sign5'];
							Cms::getFlashBag()->add('info', $msg);
							return true;
						}
					}
				}
				
			}
		}
	}

	public function logout() {
		unset($_SESSION[CUSTOMER_CODE . '_id']);
		unset($_SESSION[CUSTOMER_CODE]);
		$msg = $GLOBALS['LANG']['c_confirm3'];
		Cms::getFlashBag()->add('info', $msg);
		return true;
	}

    public function getById($id = 0) {
		if ($id > 0) {
			$q = "SELECT c.*, n.active as newsletter FROM `" . $this->table . "` c LEFT JOIN `" . DB_PREFIX . "newsletter_users` n ON c.email=n.email ";
			$q.= "WHERE c.id='" . $id . "' ";
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
				$v['sid'] = sha1($v['id']);

				return $v;
			}
		}
		return false;        
    }
    
	public function loadById($id = 0) {
		if ($id > 0) {
			$q = "SELECT c.*, n.active as newsletter FROM `" . $this->table . "` c LEFT JOIN `" . DB_PREFIX . "newsletter_users` n ON c.email=n.email ";
			$q.= "WHERE c.id='" . $id . "' ";
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
				$v['sid'] = sha1($v['id']);

				$_SESSION[CUSTOMER_CODE] = $v;
				return $v;
			}
		}
		return false;
	}

	public function add($post) {
		$post = maddslashes($post);
		$post['login2'] = strtolower($post['login2']);
        
        $errors = [];
        
        if ($post) {
            foreach ($post as $field => $value) {
                switch ($field) {
                    case 'first_name':
                        if (strlen($value) < 2) {
                            $errors[] = $GLOBALS['LANG']['c_check1'];
                        }
                        break;
                    case 'last_name':
                        if (strlen($value) < 2) {
                            $errors[] = $GLOBALS['LANG']['c_check2'];
                        }
                        break;
                    case 'email':
                        if (!checkEmail($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check3'];
                        }
                        
                        if (!$this->emailExists($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check4'];
                        }
                        break;
                    case 'email2':
                        if ($post['email'] != $value) {
                            $errors[] = $GLOBALS['LANG']['c_check5'];
                        }
                        break;
                    case 'login2':
                        if (!checkLogin($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check6'];
                        }
                        
                        if (!$this->userExists($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check7'];
                        }
                        break;
                    case 'pass2':
                        if (!checkPassword($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check8'];
                        }
                        break;
                    case 'pass3':
                        if ($post['pass2'] != $value) {
                            $errors[] = $GLOBALS['LANG']['c_check9'];
                        }
                        break;
                    case 'accept':
                        if ($value != 1) {
                            $errors[$field] = $GLOBALS['LANG']['c_check10'];
                        }
                        break;
                }
            }
            
            if ($errors) {
				foreach ($errors as $error) {
					Cms::getFlashBag()->add('error', $error);
				}
				
                return false;
            } else {
                $pass = setHash($post['pass2'], SALT2);
                
                $active = 1;
                
                if (Cms::$conf['client_account_activation'] == 2) {
                    $active = 0;
                }

                $q = "INSERT INTO `" . $this->table . "` SET `login`='" . $post['login2'] . "', `pass`='" . $pass . "', ";
                $q.= "`first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', `email`='" . $post['email'] . "', ";
                $q.= "`type`=1, `active`='" . $active ."', `date_add`=NOW() ";                
                
                if ($id = Cms::$db->insert($q)) {
                    if ($post['lang'] == 'pl')
                        $l = 2;
                    else
                        $l = 1;
                    $q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
                    $q.= "`email`='" . $post['email'] . "', `active`='1' ";
                    Cms::$db->insert($q);
                    
                    $this->sendEmailCustomerAdd(array_merge($post, ['customer_id' => $id]));
                    
					Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm1']);
                    return true;
                }
                
                $errors[] = $GLOBALS['LANG']['c_confirm2'];
				foreach ($errors as $error) {
					Cms::getFlashBag()->add('error', $error);
				}

                return false;
            }
        }
	}

	public function add2($post) {
		$post = maddslashes($post);
		if (!$post['login2'])
			$post['login2'] = $post['email'];
		$post['login2'] = strtolower($post['login2']);
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
		if ($post['type'] == 2 AND strlen($post['company_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check11']);
			return false;
		} elseif ($post['type'] == 2 AND strlen($post['nip']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check12']);
			return false;
		} elseif (strlen($post['first_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check1']);
			return false;
		} elseif (strlen($post['last_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check2']);
			return false;
		} elseif (!checkEmail($post['email'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check3']);
			return false;
		} elseif (!$this->emailExists($post['email'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check4']);
			return false;
		} elseif (strlen($post['phone']) < 3) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check16']);
			return false;
		} elseif (strlen($post['address1']) < 1) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check13']);
			return false;
		} elseif (strlen($post['post_code']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check14']);
			return false;
		} elseif ($post['country'] == 1 AND ! checkPostcode($post['post_code'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check14']);
			return false;
		} elseif (strlen($post['city']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check15']);
			return false;
		} elseif ($post['login2'] AND ! checkLogin($post['login2'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check6']);
			return false;
		} elseif ($post['login2'] AND ! $this->userExists($post['login2'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check7']);
			return false;
		} elseif ($post['pass2'] AND ! checkPassword($post['pass2'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check8']);
			return false;
		} elseif ($post['pass2'] AND $post['pass2'] != $post['pass3']) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check9']);
			return false;
		} else {
			$pass = setHash($post['pass2'], SALT2);
			$q = "INSERT INTO `" . $this->table . "` SET `login`='" . $post['login2'] . "', `pass`='" . $pass . "', ";
			$q.= "`first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', `email`='" . $post['email'] . "', ";
			$q.= "`type`='" . $post['type'] . "', `company_name`='" . $post['company_name'] . "', `nip`='" . $post['nip'] . "', ";
			$q.= "`address1`='" . $post['address1'] . "', `address2`='" . $post['address2'] . "', `address3`='" . $post['address3'] . "', `post_code`='" . $post['post_code'] . "', ";
			$q.= "`city`='" . $post['city'] . "', `country`='" . $post['country'] . "', `phone`='" . $post['phone'] . "', ";
			$q.= "`lang`='" . $post['lang'] . "', `active`=1, `source`=1, `date_add`=NOW() ";
			if ($id = Cms::$db->insert($q)) {
				if ($post['lang'] == 'pl')
					$l = 2;
				else
					$l = 1;
				$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
				$q.= "`email`='" . $post['email'] . "', `lang_id`='" . $l . "', `active`='1' ";
				Cms::$db->insert($q);

				$_SESSION[CUSTOMER_CODE] = $post;
				$_SESSION[CUSTOMER_CODE]['id'] = $id;
				$_SESSION[CUSTOMER_CODE]['access'] = 'without_login';
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm6']);
				return true;
			}
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm2']);
			return false;
		}
	}

	public function edit($post, $files) {
		$post = maddslashes($post);
		if (strlen($post['first_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check1']);
			return false;
		} elseif (strlen($post['last_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check2']);
			return false;
		} else {
			$q = "UPDATE " . $this->table . " SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "' ";
			$q.= "WHERE sha1(`id`)='" . $post['sid'] . "' ";
			if (Cms::$db->update($q)) {
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm4']);
				$msg = 1;
			}

			if (isset($msg))
				return true;
			
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm5']);
			return false;
		}
	}

	public function address($post) {
		$post = maddslashes($post);
		if ($post['country'] == 1)
			$post['post_code'] = strtoupper($post['post_code1'] . ' ' . $post['post_code2']);
		else
			$post['post_code'] = strtoupper($post['post_code']);
		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}
		if ($post['type'] == 2 AND strlen($post['company_name']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check11']);
			return false;
		} elseif ($post['type'] == 2 AND strlen($post['nip']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check12']);
			return false;
		} elseif (strlen($post['address1']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check13']);
			return false;
		} elseif (strlen($post['post_code']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check14']);
			return false;
		} elseif (strlen($post['city']) < 2) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check15']);
			return false;
		} elseif (strlen($post['phone']) < 3) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check16']);
			return false;
		} else {
            
			$q = "UPDATE " . $this->table . " SET `type`='" . $post['type'] . "', `company_name`='" . $post['company_name'] . "', `nip`='" . $post['nip'] . "', ";
			$q.= "`address1`='" . $post['address1'] . "', `address2`='" . $post['address2'] . "', `address3`='" . $post['address3'] . "', `post_code`='" . $post['post_code'] . "', ";
			$q.= "`city`='" . $post['city'] . "', `country`='" . $post['country'] . "', `phone`='" . $post['phone'] . "' ";
			$q.= "WHERE sha1(`id`)='" . $post['sid'] . "'";
			if (Cms::$db->update($q)) {
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm4']);
				return true;
			}
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm5']);
			return false;
		}
	}

	public function userExists($login) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `login`='" . $login . "' ";
		if (Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}
       
    public function emailExistsAlready($email) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `email`='" . $email . "' ";

		if (Cms::$db->getRow($q)) {
			return true;
		}
        
		return false;        
    }
    
	public function emailExists($email, $id = 0) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `email`='" . $email . "' ";
		if ($id > 0)
			$q.= "AND `id`!='" . (int) $id . "' ";
		if (Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}

	public function delete($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			Cms::getFlashBag()->add('error', 'Wybrany klient został usunięty.');
			
			return false;
		}
		
		Cms::getFlashBag()->add('error', 'Usuwanie klienta nie powiodło się!');
		return false;
	}

	public function sendEmailCustomerAdd(array $post) {	        
        $templateSuffix = Cms::$conf['client_account_activation'] == 2 ? 'inactive' : 'active';        
        $templateName = 'customer_add' . '_' . $templateSuffix;

        $data = array(
            'template_name' => $templateName,
            'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>'
        );
        
        $data = array_merge($data, $post);
        
        $this->sendEmailCustomer($data);
        $this->sendEmailAdmin($data);
	}
    
    public function sendEmailCustomer(array $data) {
		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate($data['template_name']);		

        //title
		$searchTitle = array('#COMPANY_NAME#', '#DOMAIN#');
		$replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME']);
		$title = str_replace($searchTitle, $replaceTitle, $template['title']);         

		$search = array('#LOGIN#', '#PASSWORD#', '#FIRST_NAME#', '#LAST_NAME#', '#EMAIL#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
		$replace = array($data['login2'], $data['pass2'], $data['first_name'], $data['last_name'], $data['email'], Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $data['server_url']);
		$content = str_replace($search, $replace, $template['content']);

		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);		
		$this->mailer->sendHTML($data['email']);
		$this->mailer->ClearAllRecipients();        
    }
    
    public function sendEmailAdmin(array $data) {
        
        $emailAdmin = isset($data['email_admin']) ? $data['email_admin'] : Cms::$conf['email_admin'];
		$emailAdmin = explode(",", $emailAdmin);
		
		if (is_array($emailAdmin)) {
            $data['template_name'] = $data['template_name'] . '_admin';
            $emailTemplate = new EmailTemplate();
            $template = $emailTemplate->getTemplate($data['template_name']);	
            
            $customerLink = SERVER_URL . '/admin/customer.html?action=edit&id=' . $data['customer_id'];
            
            $searchTitle = array('#COMPANY_NAME#', '#DOMAIN#');
            $replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME']);
            $title = str_replace($searchTitle, $replaceTitle, $template['title']); 
        
            $search = array('#LOGIN#', '#PASSWORD#', '#FIRST_NAME#', '#LAST_NAME#', '#EMAIL#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#', '#CUSTOMER_LINK#');
            $replace = array($data['login2'], $data['pass2'], $data['first_name'], $data['last_name'], $data['email'], Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $data['server_url'], $customerLink);
            $content = str_replace($search, $replace, $template['content']);
            
            $this->mailer->setSubject($title);
            $this->mailer->setBody($content);	            
            
			foreach ($emailAdmin as $v) {
				$this->mailer->sendHTML($v);
				$this->mailer->ClearAllRecipients();
			}
		}        
    }    

	public function activeUid($sid) {
		$sid = addslashes($sid);
		$q = "UPDATE " . $this->table . " SET `active`='1' WHERE SHA1(CONCAT(`id`,`login`,`email`))='" . $sid . "' ";
		if (Cms::$db->update($q)) {
			$q = "SELECT `email` FROM `" . $this->table . "` WHERE SHA1(CONCAT(`id`,`login`,`email`))='" . $sid . "'  ";
			$row = Cms::$db->getRow($q);
			$q = "UPDATE " . $this->tableNewsletterUsers . " SET `active`='1' WHERE `email`='" . $row['email'] . "' ";
			Cms::$db->update($q);
			return true;
		} else {
			return false;
		}
	}

	public function deleteUid($sid) {
		$sid = addslashes($sid);
		$q = "DELETE FROM " . $this->table . " WHERE SHA1(CONCAT(`id`,`login`,`email`))='" . $sid . "'";
		if (Cms::$db->delete($q)) {
			return true;
		} else {
			return false;
		}
	}

    public function sendEmailNewPasswordCustomer(array $data) {
        $emailTemplate = new EmailTemplate();
        $template = $emailTemplate->getTemplate('customer_new_password');	

        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';        
        $title = str_replace($data['search_title'], $data['replace_title'], $template['title']);
        
        $search = array('#LOGIN#', '#FIRST_NAME#', '#LAST_NAME#', '#PASSWORD#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
        $replace = array($data['customer']['login'], $data['customer']['first_name'], $data['customer']['last_name'], $data['new_password'], Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
        $content = str_replace($search, $replace, $template['content']);
        $replyTo = explode(",", Cms::$conf['email_admin'])[0];			

        $this->mailer->setSubject($title);
        $this->mailer->setBody($content);
        $result = $this->mailer->sendHTML($data['email'], $replyTo);        
        
        return $result;
    }
    
	public function reminder($post) {
		if (!checkEmail($post['email'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check3']);
			return false;
		} elseif (!$this->emailExistsAlready($post['email'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_email_not_exists']);
			return false;
		}

		$q = "SELECT `login`, `first_name`, `last_name` FROM `" . $this->table . "` WHERE `email`='" . addslashes($post['email']) . "' ";
		$customer = Cms::$db->getRow($q);

		$new_pass = substr(sha1(date("Y-m-d_h-i")), 0, 10);
		$pass = setHash($new_pass, SALT2);
		$q = "UPDATE " . $this->table . " SET `pass`='" . $pass . "' WHERE `login`='" . $customer['login'] . "' ";
		if (Cms::$db->update($q)) {
            
            $data = array(
                'customer' => $customer,
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME']],                
                'new_password' => $new_pass,
                'email' => $post['email']
            );
            
            $this->sendEmailNewPasswordCustomer($data);
            
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm7']);
			return true;
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm8']);
			return false;
		}
	}

	public function password($post) {
		if (strlen($post['pass_old']) < 5) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check17']);
			return false;
		} elseif (!checkPassword($post['pass'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check8']);
			return false;
		} elseif ($post['pass'] != $post['pass2']) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check9']);
			return false;
		}

		$pass = setHash($post['pass'], SALT2);
		$pass_old = setHash($post['pass_old'], SALT2);
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `pass`='" . $pass_old . "' ";
		if (!Cms::$db->getRow($q)) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check17']);
			return false;
		}

		$q = "UPDATE " . $this->table . " SET `pass`='" . $pass . "' WHERE `id`='" . (int) $_SESSION[CUSTOMER_CODE]['id'] . "' AND `pass`='" . $pass_old . "' ";
		if (Cms::$db->update($q)) {
			$customer['login'] = $_SESSION[CUSTOMER_CODE]['login'];
			$customer['pass'] = $post['pass'];
			$this->login($customer);
			return true;
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm5']);
			return false;
		}
	}
	
	public function passwordExpiredChange($post) {
		if (!checkPassword($post['pass'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check8']);
			return false;
		} elseif ($post['pass'] != $post['pass2']) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_check9']);
			return false;
		}

		$pass = setHash($post['pass'], SALT2);

		$q = "UPDATE " . $this->table . " SET `pass`='" . $pass . "' WHERE (LOWER(`login`)='" . $post['login'] . "' OR LOWER(`email`)='" . $post['login'] . "') AND (pass is null or pass = '')";

		if (Cms::$db->update($q)) {
			$customer['login'] = $post['login'];
			$customer['pass'] = $post['pass'];
			$this->login($customer);
			return true;
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['c_confirm5']);
			return false;
		}
	}	

	public function loadCountry() {
		$q = "SELECT c.* FROM `" . DB_PREFIX . "transport_country` c WHERE c.status_id IN (1,2) ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

	public function updateAddress(array $post, $customer_id) {
		$customer = $this->loadById($customer_id);

		$q = 'UPDATE ' . $this->table . ' SET';
		$set = false;
        
		if (!$customer['phone'] && $post['shipping_phone']) {
			$q .= ' phone="' . $post['shipping_phone'] . '"';
			$set = true;
        }

		//in this case change all address data
		if (!$customer['address1'] && $post['shipping_address1']) {
            if ($set) {
                $q .= ',';
            }
			$q .= ' address1="' . $post['shipping_address1'] . '"';
			$q .= ', address2="' . $post['shipping_address2'] . '"';
			$q .= ', address3="' . $post['shipping_address3'] . '"';
			$q .= ', post_code="' . $post['shipping_post_code'] . '"';
			$q .= ', city="' . $post['shipping_city'] . '"';
			$q .= ', country="' . $post['shipping_country'] . '"';
			$set = true;
		}

		if ($set) {
			$q .= ' WHERE `id`="' . (int) $customer_id . '"';

			Cms::$db->update($q);
		}
	}    
    
//    isset($_SESSION[CUSTOMER_CODE . '_id'])
    
    //Customer::logged()

}
