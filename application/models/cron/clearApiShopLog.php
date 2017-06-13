<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$log.= '<strong>Clear Api Shop Log</strong><br />';

//starsze niz 3 miesiace
$q = "DELETE FROM `" . DB_PREFIX . "api_shop_log` WHERE `date_add`< DATE_SUB(NOW(), INTERVAL 3 MONTH)";
Cms::$db->delete($q);
		
$log.= 'Usunięto wpisów <br />';

return $log;
