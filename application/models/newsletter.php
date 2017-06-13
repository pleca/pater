<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/mailer.php');

class Newsletter {

	private $mailer;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->tableTemplates = DB_PREFIX . 'newsletter_templates';
		$this->tableUsers = DB_PREFIX . 'newsletter_users';
	}

	public function __destruct() {
		
	}

	public function addEmail($post) {
		$post = maddslashes($post);
		$post['id'] = 0;
		if ($this->checkData($post)) {
			$q = "INSERT INTO " . $this->tableUsers . " SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
			$q.= "`email`='" . $post['email'] . "', `active`='1' ";
			if ($id = Cms::$db->insert($q)) {
				return true;
			}
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['newsletter_error1']);
			return false;
		}
	}

	public function unsubscribeEmail($post) {
		$post = maddslashes($post);
		if ($post['email']) {
			$q = "UPDATE " . $this->tableUsers . " SET `active`='0' WHERE `email`='" . $post['email'] . "' ";
			if (Cms::$db->update($q)) {
				return true;
			}
		}
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['newsletter_error2']);
		return false;
	}

	public function loadAdmin($limitStart = 0, $limit = 25) {
		$q = "SELECT * FROM `" . $this->tableTemplates . "` ORDER BY `date_add` DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['desc_short'] = clearHtml($v['desc'], 150, '...');
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->tableTemplates . "` ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadAdminById($id = 0) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->tableTemplates . "` WHERE `id`='" . $id . "' ";
			$v = Cms::$db->getRow($q);
			return $v;
		}
		return false;
	}

	public function add($post) {
		$title = clearName($post['title']);
		$desc = addslashes($post['desc']);
		$q = "INSERT INTO " . $this->tableTemplates . " SET `title`='" . $title . "', `desc`='" . $desc . "', `date_add`=NOW() ";
		if ($id = Cms::$db->insert($q)) {
			Cms::getFlashBag()->add('info', 'Dodano nowy artykuł.');
			return $id;
		}
		Cms::getFlashBag()->add('error', 'Dodawanie nie powiodło się!');
		return false;
	}

	public function edit($post) {
		$title = clearName($post['title']);
		$desc = addslashes($post['desc']);
		$q = "UPDATE " . $this->tableTemplates . " SET `title`='" . $title . "', `desc`='" . $desc . "', `date_mod`=NOW() ";
		$q.= "WHERE `id`='" . (int) $post['id'] . "'";
		if (Cms::$db->update($q)) {
			Cms::getFlashBag()->add('info', 'Zapisano zmiany dla artykułu.');
			return true;
		}
		Cms::getFlashBag()->add('error', 'Zmiana artykułu nie powiodła się!');
		return false;
	}

	function delete($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->tableTemplates . " WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			Cms::getFlashBag()->add('error', 'Wybrany element usunięto.');
			return true;
		}
		Cms::getFlashBag()->add('error', 'Usuwanie elementu nie powiodło się!');
		return false;
	}

	public function loadUserAdminById($id = 0) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->tableUsers . "` WHERE `id`='" . $id . "' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}

	public function addUser($post) {
		$post = maddslashes($post);
		$post['id'] = 0;
		if ($this->checkData($post)) {
			$q = "INSERT INTO " . $this->tableUsers . " SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
			$q.= "`email`='" . $post['email'] . "', `lang_id`='" . $post['lang_id'] . "', `active`='" . $post['active'] . "' ";
			if ($id = Cms::$db->insert($q)) {
				return true;
			}

			return false;
		}
	}

	public function editUser($post) {
		$post = maddslashes($post);
		if ($this->checkData($post)) {
			$q = "UPDATE " . $this->tableUsers . " SET `first_name`='" . $post['first_name'] . "', `last_name`='" . $post['last_name'] . "', ";
			$q.= "`email`='" . $post['email'] . "', `lang_id`='" . $post['lang_id'] . "', `active`='" . $post['active'] . "' ";
			$q.= "WHERE `id`='" . (int) $post['id'] . "'";
			if (Cms::$db->update($q)) {
				return true;
			}

			return false;
		}
	}

	public function checkData($data, $edit = 0) {
		if (!checkEmail($data['email'])) {
			Cms::getFlashBag()->add('error', 'Podany adres e-mail jest nieprawidłowy.');
			return false;
		} elseif (!$this->emailExists($data['email'], $data['id'])) {
			Cms::getFlashBag()->add('error', 'Podany adres e-mail jest juz zapisany w naszym systemie.');
			return false;
		} else {
			return true;
		}
	}

	public function emailExists($email, $id) {
		$q = "SELECT `id` FROM `" . $this->tableUsers . "` WHERE `email`='" . $email . "' AND `id`!='" . (int) $id . "' ";
		if (Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}

	public function deleteUser($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->tableUsers . " WHERE `id`='" . (int) $id . "' ";
			return Cms::$db->delete($q);
		}
		
		return false;
	}

	public function loadUsersAdmin($filtr = '', $limitStart = 0, $limit = 25) {
		$q = "SELECT * FROM `" . $this->tableUsers . "` ";
		if (isset($filtr['action2'])) {
			$q.= "WHERE 1 ";
			if ($filtr['first_name'])
				$q.= "AND `first_name` LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND `last_name` LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND `email` LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['active'] == 1)
				$q.= "AND `active`=1 ";
			elseif ($filtr['active'] == 2)
				$q.= "AND `active`!=1 ";
			if ($filtr['lang_id'])
				$q.= "AND `lang_id`='" . (int) $filtr['lang_id'] . "' ";
		}
		$q.= "ORDER BY `email` DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function getUsersAdmin($filtr = '', $limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->tableUsers . "` ";
		if (isset($filtr['action2'])) {
			$q.= "WHERE 1 ";
			if ($filtr['first_name'])
				$q.= "AND `first_name` LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND `last_name` LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND `email` LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['active'] == 1)
				$q.= "AND `active`=1 ";
			elseif ($filtr['active'] == 2)
				$q.= "AND `active`!=1 ";
			if ($filtr['lang_id'])
				$q.= "AND `lang_id`='" . (int) $filtr['lang_id'] . "' ";
		}
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function getCsv($filtr = '') {
		$q = "SELECT * FROM `" . $this->tableUsers . "` ";
		$q.= "WHERE 1 ";
		if ($filtr['first_name'])
			$q.= "AND `first_name` LIKE '" . $filtr['first_name'] . "%' ";
		if ($filtr['last_name'])
			$q.= "AND `last_name` LIKE '" . $filtr['last_name'] . "%' ";
		if ($filtr['email'])
			$q.= "AND `email` LIKE '" . $filtr['email'] . "%' ";
		if ($filtr['active'] == 1)
			$q.= "AND `active`=1 ";
		elseif ($filtr['active'] == 2)
			$q.= "AND `active`!=1 ";
		if ($filtr['lang_id'])
			$q.= "AND `lang_id`='" . (int) $filtr['lang_id'] . "' ";
		$q.= "ORDER BY `email` ASC ";
		$array = Cms::$db->getAll($q);
		header("Content-type: text/csv; charset=utf-8");
		header('Content-disposition: attachment;filename=newsletter_' . date("Y-m-d") . '.csv');
		$content = '';
		foreach ($array as $v) {
			$content.= $v['first_name'] . ';' . $v['last_name'] . ';' . $v['email'] . "\n";
		}
		echo $content;
		die;
		return $items;
	}

	public function loadTemplatesSelect() {
		$q = "SELECT `id`, `title` FROM `" . $this->tableTemplates . "` ORDER BY `title` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$items[] = $v;
		}
		return $items;
	}

	public function getUsers() {
		$q = "SELECT COUNT(`id`) as `all`, SUM(`active`) as `active` FROM " . $this->tableUsers . " ";
		if ($items = Cms::$db->getRow($q)) {
			return $items;
		}
		return false;
	}

	public function loadUsersSend() {
		$q = "SELECT * FROM `" . $this->tableUsers . "` WHERE `active`=1 ORDER BY `id` DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function send($post) {
		if (isset($post['to']) AND $post['to'] == 1 AND ! empty($post['email']) AND $post['template_id'] > 0) {
			$template = $this->loadAdminById($post['template_id']);
			$search = array('#IMIE#', '#NAZWISKO#', '#EMAIL#');
			$replace = array('Imię', 'Nazwisko', $post['email']);
			$content = str_replace($search, $replace, stripslashes($template['desc']));
			$subject = $template['title'];
			$this->mailer->setSubject($subject);
			$this->mailer->setBody($content);
			if (!$this->mailer->sendHTML($post['email'])) {
				return true;
			}
			$this->mailer->ClearAllRecipients();
			Cms::getFlashBag()->add('info', 'Wyslano biuletyn na adres ' . $post['email']);
			return true;
		} elseif (isset($post['to']) AND $post['to'] == 2 AND $post['template_id'] > 0) {
			$count = 0;
			$template = $this->loadAdminById($post['template_id']);
			$subject = $template['title'];
			$users = $this->loadUsersSend();
			foreach ($users as $v) {
				$search = array('#IMIE#', '#NAZWISKO#', '#EMAIL#');
				$replace = array($v['first_name'], $v['last_name'], $v['email']);
				$content = str_replace($search, $replace, stripslashes($template['desc']));
				$this->mailer->setSubject($subject);
				$this->mailer->setBody($content);
				if (!$this->mailer->sendHTML($v['email'])) {
					return true;
				}
				$this->mailer->ClearAllRecipients();
				$count++;
			}
			Cms::getFlashBag()->add('info', 'Wyslano biuletyn do ' . $count . ' osób.');
			return true;
		}
		Cms::getFlashBag()->add('error', 'Wysyłanie nie powiodło się, prawdopodobnie nie wybrano wymaganych opcji!');
		return false;
	}

}
