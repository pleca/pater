<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$log.= '<strong>Clear Cache</strong><br />';

// promocje i wyprzedaz
$q = "UPDATE `" . DB_PREFIX . "shop_products` SET `promotion`='0', `clearance`='0' WHERE `promotion`='1' AND `date_promotion`<=NOW()";
if ($oDb->update($q))
	$log.= 'Wyłączono promocje czasowe<br />';

// nowosci
$q = "UPDATE `" . DB_PREFIX . "shop_products` SET `new`='0' WHERE `new`='1' AND `date_new`<=NOW()";
if ($oDb->update($q))
	$log.= 'Wyłączono nowości czasowe<br />';

// anulujemy zamowienia zwykle nie oplacone, 2 tyg
$interval = '2 WEEK';
$q = "SELECT `id`, `customer_id`, `date_add`, `status_id`, `groupon`, `source` FROM `" . DB_PREFIX . "shop_orders` ";
$q.= "WHERE `status_id`=1 AND `date_add`<DATE_SUB(NOW(), INTERVAL " . $interval . ") ";
$items = $oDb->getAll($q);
foreach ($items as $v) {
	$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`=4 WHERE `id`='" . $v['id'] . "' ";
	$oDb->update($q);
	if ($v['groupon'] != '') {
		if (strlen($v['groupon']) > 10) {
			$t = explode(";", $v['groupon']);
			foreach ($t as $w) {
				$q = "UPDATE `" . DB_PREFIX . "groupon_codes` SET `use`=0, `date_use`='' WHERE `order_id`='" . $v['id'] . "' AND `value`='" . addslashes($w) . "' ";
				$oDb->update($q);
			}
		} else {
			$q = "UPDATE `" . DB_PREFIX . "groupon_codes` SET `use`=0, `date_use`='' WHERE `order_id`='" . $v['id'] . "' AND `value`='" . addslashes($v['groupon']) . "' ";
			$oDb->update($q);
		}
	}
	$log.= 'Anulowano zamówienie <strong>' . $v['id'] . '</strong><br />';
}

// anulujemy zamowienia promocyjne nie oplacone, 1 tyg
$interval = '1 WEEK';
$q = "SELECT `id`, `customer_id`, `date_add`, `status_id` FROM `" . DB_PREFIX . "shop_orders` ";
$q.= "WHERE `status_id`=1 AND `date_add`<DATE_SUB(NOW(), INTERVAL " . $interval . ") ";
$items = $oDb->getAll($q);
foreach ($items as $v) {
	$q = "SELECT COUNT(`order_id`) as count FROM `" . DB_PREFIX . "shop_orders_products` WHERE `order_id`='" . $v['id'] . "' AND `promotion`=1 ";
	$item = $oDb->getRow($q);
	if ($item['count'] > 0) {
		$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`=4 WHERE `id`='" . $v['id'] . "' ";
		$oDb->update($q);
		$log.= 'Anulowano zamówienie <strong>' . $v['id'] . '</strong><br />';
	}
}

// usuwanie szablonow
$compile_dir = CMS_DIR . '/application/views/_compile/*';
$files = array();
foreach (glob($compile_dir, GLOB_BRACE) as $file) {
	$files[] = $file;
}
foreach ($files as $v) {
	unlink($v);
}
$log.= 'Usunięto pliki szablonów<br />';
