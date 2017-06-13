<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/Pages.php');

class PagesAdmin extends Pages {

	public $mailer;
	public $module;
	public $table;
	public $tableDesc;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->module = 'pages';
		$this->table = DB_PREFIX . 'pages';
		$this->tableDesc = DB_PREFIX . 'pages_desc';
		$this->tableMenu = DB_PREFIX . 'menu';
	}

	public function __destruct() {
		
	}

	public function loadAdmin($limitStart = 0, $limit = 25) {
		$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.lang_id='" . _ID . "' ";
		$q.= "ORDER BY a.date_add DESC, a.id DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadByIdAdmin($id = 0, $lang = '') {
		if ($id > 0) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			if ($lang)
				$q.= "WHERE d.lang_id='" . $lang['id'] . "' AND a.id='" . (int) $id . "' ";
			else
				$q.= "WHERE d.lang_id='" . _ID . "' AND a.id='" . (int) $id . "' ";
			$v = Cms::$db->getRow($q);
			$v = mstripslashes($v);
			if ($lang) {
				if ($lang['default'] != 1)
					$langUrl = '/' . $lang['code'];
				else
					$langUrl = '';
				$v['url'] = SERVER_URL . CMS_URL . $langUrl . '/' . $v['title_url'] . '.html';
			}
			return $v;
		}
		return false;
	}

	public function loadDescAdmin($id = 0, $langs = '') {
		$items = array();

		foreach ($langs as $l) {
			$q = "SELECT * FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $id . "' AND `lang_id`='" . (int) $l['id'] . "' ";
			$v = Cms::$db->getRow($q);
			if ($v = Cms::$db->getRow($q)) {

				$v = mstripslashes($v);
			} else {

				$v['title'] = '';
				$v['desc_short'] = '';
				$v['desc'] = '';
				$v['tag1'] = '';
				$v['tag2'] = '';
				$v['tag3'] = '';
			}

			$v['lang_id'] = $l['id'];
			$v['lang_code'] = $l['code'];
			$v['lang_name'] = $l['name'];
			$items[$l['id']] = $v;
		}
		return $items;
	}

	public function addAdmin($post, $langs, $ping = 0) {
		$i = 0;
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$q = "INSERT INTO " . $this->table . " SET `active`='" . $post['active'] . "', `gallery_id`='" . $post['gallery_id'] . "', `menu`='1', `date_add`=NOW() ";
		$id = Cms::$db->insert($q);

		foreach ($langs as $v) {
			$title = clearName($post['title'][$v['id']]);
			$title_url = makeUrl($title);
			if ($i != 1) {
				$title_img = $title_url;
				$i = 1;
			}
			$desc = addslashes($post['desc'][$v['id']]);
			$desc_short = addslashes($post['desc_short'][$v['id']]);
			if (empty($desc_short))
				$desc_short = substr(strip_tags($desc), 0, 250);
			$title_url = $this->titleExists($title_url, $id, $v['id']);

			$q = "INSERT INTO " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
			$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "', ";
			$q.= "`parent_id`='" . $id . "', `lang_id`='" . $v['id'] . "' ";
			Cms::$db->insert($q);
		}
		return $id;
	}

	public function editAdmin($post, $langs, $ping = 0) {
		$i = 0;
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$q = "UPDATE " . $this->table . " SET `active`='" . $post['active'] . "', `gallery_id`='" . $post['gallery_id'] . "', `date_mod`=NOW() ";
		$q.= "WHERE `id`='" . (int) $post['id'] . "'";
		Cms::$db->update($q);

		$id = $post['id'];
		foreach ($langs as $v) {
			$title = clearName($post['title'][$v['id']]);
			$title_url = makeUrl($title);
			if ($i != 1) {
				$title_img = $title_url;
				$i = 1;
			}
			$desc = addslashes($post['desc'][$v['id']]);
			$desc_short = addslashes($post['desc_short'][$v['id']]);
			if (empty($desc_short))
				$desc_short = substr(strip_tags($desc), 0, 250);
			$title_url = $this->titleExists($title_url, $id, $v['id']);

			if ($this->idExists($id, $v['id'])) {
				$q = "UPDATE " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
				$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "' ";
				$q.= "WHERE `parent_id`='" . (int) $id . "' AND `lang_id`='" . (int) $v['id'] . "' ";
				Cms::$db->update($q);
			} else {
				$q = "INSERT INTO " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
				$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "', ";
				$q.= "`parent_id`='" . $id . "', `lang_id`='" . $v['id'] . "' ";
				Cms::$db->insert($q);
			}
		}

		$q = "UPDATE `" . $this->tableMenu . "`, `" . $this->tableMenu . "_desc` SET `url`='" . $title_url . "' ";
		$q.= "WHERE `" . $this->tableMenu . "`.`type`='page' AND `" . $this->tableMenu . "_desc`.`url`='" . $post['title_url_old'] . "'";
		Cms::$db->update($q);

		return true;
	}

	public function titleExists($title_url, $parent_id = 0, $lang_id = '') {
		if (empty($title_url))
			$title_url = '-';
		$temp_url = $title_url;
		$i = 0;
		do {
			$q = "SELECT `title_url` FROM `" . $this->tableDesc . "` WHERE `title_url`='" . $temp_url . "' AND `lang_id`='" . (int) $lang_id . "' ";
			if ($parent_id > 0)
				$q.= "AND `parent_id`!='" . (int) $parent_id . "' ";
			if ($row = Cms::$db->getRow($q)) {
				$i++;
				$temp_url = $title_url . '_' . $i;
			} else {
				return $temp_url;
			}
		} while ($title_url != $temp_url);
		return $temp_url;
	}

	public function idExists($parent_id = 0, $lang_id = '') {
		$q = "SELECT `parent_id` FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $parent_id . "' AND `lang_id`='" . (int) $lang_id . "' ";
		if (Cms::$db->getRow($q)) {
			return true;
		}
		return false;
	}

	function deleteAdmin($id) {
		if ($id) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			return true;
		}

		return false;
	}

}
