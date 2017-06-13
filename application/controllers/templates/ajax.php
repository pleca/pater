<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

$controller = isset($params[1]) ? $params[1] : null;

if (file_exists(CONTROL_DIR . '/templates/ajax/' . $controller . '.php')) {
	require_once(CONTROL_DIR . '/templates/ajax/' . $controller . '.php');
}
