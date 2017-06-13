<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

$controller = isset($params[2]) ? $params[2] : null;

if (file_exists(CONTROL_DIR . '/admin/ajax/' . $controller . '.php')) {
	require_once(CONTROL_DIR . '/admin/ajax/' . $controller . '.php');
}
