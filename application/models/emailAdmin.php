<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class EmailAdmin {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'email_templates';
	}

	public function __destruct() {
		
	}

	public function loadAdmin() {
		$q = "SELECT * FROM `" . $this->table . "` WHERE `lang_id`='" . _ID . "' ORDER BY `name` ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['desc'] = substr(strip_tags($v['desc']), 0, 300);
			$items[] = $v;
		}
		return $items;
	}

	public function loadDescAdmin($name = 0, $langs = '') {
		$items = array();

		foreach ($langs as $l) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE `name`='" . $name . "' AND `lang_id`='" . (int) $l['id'] . "' ";
			$v = Cms::$db->getRow($q);		
			$v = mstripslashes($v);
			$v['lang_id'] = $l['id'];
			$v['lang_code'] = $l['code'];
			$v['lang_name'] = $l['name'];
			$items[$l['id']] = $v;
		}
		return $items;
	}

	public function editAdmin($post, $langs) {
		foreach ($langs as $v) {
			$title = clearName($post['title'][$v['id']]);
			$desc = addslashes($post['desc'][$v['id']]);

			$q = "UPDATE " . $this->table . " SET `title`='" . $title . "', `desc`='" . $desc . "' ";
			$q.= "WHERE `name`='" . $post['name'] . "' AND `lang_id`='" . (int) $v['id'] . "' ";
			Cms::$db->update($q);
		}
		Cms::getFlashBag()->add('info', 'Zapisano zmiany dla szablonu.');
		return true;
	}

}
