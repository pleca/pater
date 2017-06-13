<?php

//namespace System\Core;

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

class BaseModel {

	protected function getFields(array $params, array $tableFields) {
		$insertFields = array();
		foreach ($tableFields as $field) {
			if (isset($params[$field]))
				$insertFields[$field] = $params[$field];
		}
		return $insertFields;
	}

	protected function getTableFields($table) {
		$this->mas($table);

		$q = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table' ";

		$fields = Cms::$db->getAll($q);
		$aColumns = array();
		foreach ($fields as $field) {
			$aColumns[] = $field['COLUMN_NAME'];
		}
		return $aColumns;
	}

	protected function createInsertFields($params) {
		$string = "(";
		$first = true;
		foreach ($params as $name => $value) {
			if (!isset($value)) {
				continue;
			}
			if ($first) {
				$string .= "`" . $name . "`";
			} else {
				$string .= ",`" . $name . "`";
			}
			$first = false;
		}
		$string .= ")";
		if ($first) {
			return '';
		} else {
			return $string;
		}
	}

	protected function createInsertValues($params) {
		$string = "VALUES(";
		$first = true;
		foreach ($params as $name => $value) {
			if (!isset($value)) {
				continue;
			}
			if ($first) {
				$string .= "\"" . $value . "\"";
			} else {
				$string .= ",\"" . $value . "\"";
			}
			$first = false;
		}
		$string .= ")";
		if ($first) {
			return '';
		} else {
			return $string;
		}
	}

	protected function createUpdate($params) {
		$string = "";
		$first = true;
		foreach ($params as $name => $value) {
			if ($first) {
				if ($value === "NULL") {
					$string .= "SET `" . $name . "` = " . $value;
				} else {
					$string .= "SET `" . $name . "` = \"" . $value . "\"";
				}
			} else {
				if ($value === "NULL") {
					$string .= ",`" . $name . "` = " . $value;
				} else {
					$string .= ",`" . $name . "` = \"" . $value . "\"";
				}
			}
			$first = false;
		}
		return $string;
	}

	protected function mas(&$array) {
		if (!is_array($array)) {
			$array = addslashes($array);
		} else {
			foreach ($array as $key => &$val) {

				if (is_array($val)) {
					$this->mas($val);
				} else {
					$val = addslashes($val);
				}
			}
		}
	}

	protected function mss(&$array) {
		if (!is_array($array)) {
			$array = stripslashes($array);
		} else {
			foreach ($array as $key => &$val) {

				if (is_array($val)) {
					$this->mss($val);
				} else {
					$val = stripslashes($val);
				}
			}
		}
	}

	public function select($table = '', $where = '1', $orderBy = '', $dir = 'ASC', $fields = '', $limitStart = '', $limit = '') {
		if (!$table) {
			return false;
		}

		if ($fields) {
			$q = "SELECT " . implode(',', $fields) . " FROM `" . $table . "` ";
		} else {
			$q = "SELECT * FROM `" . $table . "` ";
		}
				
		$q .= "WHERE " . $where . " ";

        if ($orderBy) {
            $q .= "ORDER BY " . $orderBy . " " . $dir;
        }

		if ($limit) {
			$q .= " Limit " . $limitStart . ", " . $limit;
		}
//dump($q);
		$item = Cms::$db->getAll($q);

		if ($item) {            
			$this->mss($item);
			return $item;
		}
		return false;
	}

	public function insert($table = '', $item = '') {
		if (!$table) {
			return false;
		}
		$tableFields = $this->getTableFields($table);
		$fields = $this->getFields($item, $tableFields);
		$this->mas($fields);

		$q = "INSERT INTO `" . $table . "` " .
				$this->createInsertFields($fields) .
				$this->createInsertValues($fields);

		return Cms::$db->insert($q);
	}

	public function update($table = '', $where = '', $item = '') {
		if (!$table) {
			return false;
		}
		if (!$where) {
			return false;
		}
		$tableFields = $this->getTableFields($table);
		$fields = $this->getFields($item, $tableFields);
		$this->mas($fields);

		$q = "UPDATE `" . $table . "` " .
				$this->createUpdate($fields) .
				" WHERE " . $where . " ";

		return Cms::$db->update($q);
	}

	public function delete($table = '', $where = '') {
		if (!$table) {
			return false;
		}
		if (!$where) {
			return false;
		}

		$q = "DELETE FROM `" . $table . "` "
				. "WHERE " . $where . " ";

		return Cms::$db->delete($q);
	}

	public function where($params = '', $type = 'AND ') {
		if (!is_array($params)) {
			return false;
		}
		$string = "";

		foreach ($params as $k => $v) {
            
			if ($string) {
				$string .= $type;
			}
						
//			$pos = strpos($k, 'time');
			$pos = strpos_array($k, ['date', 'time']);

			if ($pos !== false) {
			
				if (is_array($v)) {
					if (isset($v['from']) && isset($v['to'])) {
						$string .= "`" . $k . "`>='" . addslashes($v['from']) . " 00:00:00' ";
						$string .= "AND `" . $k . "`<='" . addslashes($v['to']) . " 23:59:59' ";

					} elseif (isset($v['from'])) {
						$string .= "`" . $k . "`>='" . addslashes($v['from']) . " 00:00:00' ";
					} elseif (isset($v['to'])) {
						$string .= "`" . $k . "`<='" . addslashes($v['to']) . " 23:59:59' ";
					}
                }
                
			} elseif (is_array($v)) {
				$string .= "`" . $k ."` IN (" .implode(',', $v) .") ";
			} else {
				$string .= "`" . $k . "`='" . addslashes($v) . "' ";
			}		
            
            
//                    $string .= "`" . $k . "`='" . addslashes($v) . "' ";
		}
        
		return $string;
	}
	
	protected function groupByTranslation(array $entities = [], $column = 'name') {
		$grouped = [];

		foreach ($entities as $entity) {
			if ($entity['locale']) {
				$grouped[$entity['locale']][$entity[$column]] = $entity;
			} else {
				if ($langs = Cms::$langs) {
					foreach ($langs as $lang) {
						$grouped[$lang['code']][$entity[$column]] = $entity;
					}
				}
			}
		}		
		
		return $grouped;
	}	
	
	protected function convertToTranslationData(&$data) {
		if (!$data) {
			return false;
		}
		
		foreach ($data as $key => $value) {
			if (!in_array($key, Cms::$locales)) {
				unset($data[$key]);
			}			
		}		
	}
	
	protected function existsTranslation($entityId, $locale, $entities) {
		
		if (isset($entities[$locale][$entityId])) {
			return true;
		}
		
		return false;
	}
	
	public function getBy(array $params = null, $fields = '') {
		
		$where = $params ? $this->where($params) : '1';
		return $this->select($this->table, $where, '', 'ASC', $fields);
	}
	
	public function updateBy(array $params = null, $item = '') {
				
		if (!$params || !$item) {
			return false;
		}
		
		$where = $params ? $this->where($params) : '1';
		return $this->update($this->table, $where, $item);
	}	
	
	public function getPages($limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}	

}
