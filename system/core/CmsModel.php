<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

class CmsModel {

	public function loadConfig() {
		$q = "SELECT `name`, `value` FROM `" . Cms::$tableConfig . "` ";
		return Cms::$db->getConfig($q);
	}   

	public function loadModules() {
		$q = "SELECT `name`, `active` FROM `" . Cms::$tableModules . "` ORDER BY `id` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[$v['name']] = $v['active'];
		}
		return $items;
	}

	public function loadLanguages() {
		$q = "SELECT `id`, `name`, `code`, `default` FROM `" . Cms::$tableLanguages . "` WHERE `active`='1' ORDER BY `default` DESC, `id` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[$v['id']] = $v;
		}
		return $items;
	}	
	
	public function loadLogotypes() {
		$logotype = new Logotype();
		$filter = array(
			'orderBy'   => 'order'
		);

		$entities = $logotype->getAll($filter);
		
		if ($entities) {
			foreach ($entities as &$entity) {
				if(isset($entity['file'])) $entity['photo'] = SERVER_URL . '/files/logotypes/' . $entity['file'];
			}
		}
		
		return $entities;
	}

	public function loadAdmin($module) {
		$q = "SELECT * FROM `" . Cms::$tableConfig . "` WHERE `module`='" . (int) $module . "' ORDER BY `name` ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

	public function save($post) {
		$post = maddslashes($post);
		foreach ($post as $name => $value) {
			$q = "UPDATE " . Cms::$tableConfig . " SET `value`='" . $value . "' WHERE `name`='" . $name . "'";
			Cms::$db->update($q);
		}
		return true;
	}

	public function update($name, $value) {
		$q = "UPDATE " . Cms::$tableConfig . " SET `value`='" . $value . "' WHERE `name`='" . $name . "'";
		Cms::$db->update($q);
		return true;
	}

	public function loadOption($name) {
		$q = "SELECT `value` FROM `" . Cms::$tableConfig . "` WHERE `name`='" . $name . "' ";
		$array = Cms::$db->getRow($q);
		return $array['value'];
	}

}
