<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

class Opinion {

	public function __construct($group = '') {
		$this->table = DB_PREFIX . 'opinion';
		$this->group = $group;
	}

	public function __destruct() {
		
	}

	public function loadOpinion($parent_id = 0) {
		$q = "SELECT SUM(`yes`) as yes, SUM(`no`) as no FROM `" . $this->table . "` ";
		$q.= "WHERE `parent_id`='" . (int) $parent_id . "' AND `group`='" . $this->group . "' ";
		if ($item = Cms::$db->getRow($q)) {
			$item['sum'] = $item['yes'] + $item['no'];
			if ($item['sum'] > 0) {
				if ($item['yes'] > 0)
					$item['vote_yes'] = round($item['yes'] / $item['sum'] * 100);
				else
					$item['vote_yes'] = 0;
				$item['vote_no'] = 100 - $item['vote_yes'];
			}
			else {
				$item['vote_yes'] = 0;
				$item['vote_no'] = 0;
			}
			return $item;
		}
		return false;
	}

	public function checkVote($parent_id = 0) {
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `parent_id`='" . (int) $parent_id . "' AND `group`='" . $this->group . "' ";
		$q.= "AND `session_id`='" . session_id() . "' ";
		if (Cms::$db->getRow($q)) {
			return true;
		}
		return false;
	}

	public function add($post, $parent_id) {
		$q = "INSERT INTO " . $this->table . " SET `parent_id`='" . (int) $parent_id . "', `group`='" . $this->group . "', ";
		if (isset($post['yes']))
			$q.= "`yes`=`yes`+1, ";
		elseif (isset($post['no']))
			$q.= "`no`=`no`+1, ";
		$q.= "`session_id`='" . session_id() . "' ";
		if ($id = Cms::$db->insert($q)) {
			return true;
		}
		return false;
	}

}
