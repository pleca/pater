<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['customer'] != 1)
	die;

$url = isset($params[1]) ? $params[1] : '';
$module = CMS_DIR . '/application/controllers/customer/' . $url . '.php';

require_once(CMS_DIR . '/application/models/customer.php');
$oCustomer = new Customer();

if (!empty($url) AND file_exists($module)) {
	require_once($module);
} elseif ($oCustomer->logged()) {
	require_once(CMS_DIR . '/application/controllers/customer/profile.php');
} else {
	require_once(CMS_DIR . '/application/controllers/customer/login.php');
}
