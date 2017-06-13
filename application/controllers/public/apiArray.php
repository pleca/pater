<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

set_time_limit(580);

$url = isset($params[1]) ? $params[1] : '';

if($url AND file_exists(CONTROL_DIR . '/public/apiArray/'.$url.'.php')) {
	require_once(CONTROL_DIR . '/public/apiArray/'.$url.'.php');
}

die;
