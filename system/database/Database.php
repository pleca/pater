<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

class Database {

	protected $db;
	protected $logSlowerThan = 1; // loguje zapytania wolniejsze niz 1s

	public function __construct() {
		try {
			$this->db = new PDO('mysql:host=' . DB_SERVER . ';port=' . DB_PORT . ';dbname=' . DB_NAME . '', DB_USER, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$GLOBALS['counter_db'] ++;
		} catch (PDOException $e) {
			if (ERROR == 1) {
				Cms::$log->LogError('Wystapil blad laczenia z baza danych!');
			}
			die;
		}
	}

	public function __destruct() {
		
	}

	public function exec($query, $binds = null) {
		$execTimeStart = $execTimeEnd = $execTimeTotal = microtime();
		try {
			$sql = $this->db->prepare($query);
			if (isset($binds)) {
				if (is_array($binds)) {
					foreach ($binds as $n => $bind) {
						$sql->bindValue($n + 1, $bind);
					}
				} else {
					$sql->bindValue(1, $binds);
				}
			}
			$GLOBALS['counter_q'] ++;
			$sql->execute();

			$execTimeEnd = microtime();
			$aStart = explode(" ", $execTimeStart);
			$start = $aStart[1] + $aStart[0];
			$aEnd = explode(" ", $execTimeEnd);
			$end = $aEnd[1] + $aEnd[0];
			$execTimeTotal = $end - $start;
			$execTimeTotal = round($execTimeTotal, 2);

			if ($execTimeTotal > $this->logSlowerThan) {

				Cms::$log->LogInfo("SEC: " . $execTimeTotal . " | " . round(memory_get_peak_usage() / 1024 / 1024, 2) . "MB");
				Cms::$log->LogInfo($query);

				if (stripos(trim($query), 'select') === 0) {
					$sql_explain = $this->db->prepare('EXPLAIN ' . trim($query));
					if (isset($binds)) {
						if (is_array($binds)) {
							foreach ($binds as $n => $bind) {
								$sql_explain->bindValue($n + 1, $bind);
							}
						} else {
							$sql_explain->bindValue(1, $binds);
						}
					}
					$GLOBALS['counter_q'] ++;
					$sql_explain->execute();
					$aExplain = $sql_explain->fetch(PDO::FETCH_ASSOC);
					Cms::$log->LogInfo(print_r($aExplain, true));
				}
			}
		} catch (PDOException $e) {
			$msg = $e->getMessage();
			$msg .= " ";
			$msg .= $e->getFile();
			$msg .= ":";
			$msg .= $e->getLine();
			$msg .= "\n" . $query . "\n";
			Cms::$log->LogFatal($msg);
			if (ERROR == 1) {
				Cms::$log->LogError('Wystapil blad biblioteki PDO:' . $e->getMessage());
			}
			die;
		}
		return $sql;
	}

	public function getConfig($query, $binds = null) {
		$sql = $this->exec($query, $binds);
		$result = array();
		while ($row = $sql->fetch()) {
			$result[$row['name']] = stripslashes($row['value']);
		}
		return $result;
	}

	public function getAll($query, $binds = null) {
		$sql = $this->exec($query, $binds);
		return $sql->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getRow($query, $binds = null) {
		$sql = $this->exec($query, $binds);
		return $sql->fetch(PDO::FETCH_ASSOC);
	}

	public function getOne($query, $binds = null) {
		$sql = $this->exec($query, $binds);
		return $sql->fetchColumn(PDO::FETCH_ASSOC);
	}

	public function update($query, $binds = null) {
		return $this->db->exec($query);
	}

	public function insert($query, $binds = null) {
		if ($this->db->exec($query))
			return $this->db->lastInsertId();
		return false;
	}

	public function delete($query, $binds = null) {
		if ($this->db->exec($query))
			return true;
		return false;
	}

	public function max($query, $binds = null) {
		$sql = $this->exec($query, $binds);
		return $sql->fetch(PDO::FETCH_NUM);
	}

	public function beginTransaction() {

		if (!$this->db->inTransaction())
			return $this->db->beginTransaction();
	}

	public function commit() {

		if ($this->db->inTransaction())
			return $this->db->commit();
	}

	public function rollBack() {

		if ($this->db->inTransaction())
			return $this->db->rollBack();
	}

}
