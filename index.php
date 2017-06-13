<?php

/* 2015-10-14 | 4me.CMS 15.3 */

$pageStart = microtime();
$GLOBALS['counter_db'] = 0;
$GLOBALS['counter_q'] = 0;

@session_start();
header("Cache-control: private");
define('VER', '15.3');
define('DATE', '2015-10-14');
require('application/config/config.php'); // ustawienia indywidualne
require('application/config/config_common.php'); // ustawienia wspolne

if (ERROR == 1) {
	error_reporting(E_ALL);
	ini_set('display_errors', 'on');
} else {
	error_reporting(0);
	ini_set('display_errors', 'off');
}

if (TEST == 1) {
	if (!isset($_COOKIE['cms_authorization']) OR $_COOKIE['cms_authorization'] != 'L9WYzmQDxfjeo6Zusk0y7n1c4MvhUNF2') {
		if (isset($_POST['action']) AND $_POST['action'] == 'authorization' AND isset($_POST['password']) AND $_POST['password'] == 'hit24') {
			setcookie('cms_authorization', 'L9WYzmQDxfjeo6Zusk0y7n1c4MvhUNF2', time() + 30 * 24 * 60 * 60, '/');
		} else {
			echo 'Password:<form method="post" action=""><input type="password" name="password" value="" /><input type="hidden" name="action" value="authorization" />';
			echo '<input type="submit" value="OK" /><form>';
			die;
		}
	}
}

require(SYS_DIR . '/core/Initialize.php');

$pageStart = explode(" ", $pageStart);
$pageStart = $pageStart[1] + $pageStart[0];
$pageStop = explode(" ", microtime());
$pageStop = $pageStop[1] + $pageStop[0];
$count = $pageStop - $pageStart;

echo "\n" . '<!--<div class="clear center">Number of calls: ' . $GLOBALS['counter_db'] . ' | Number of queries: ' . $GLOBALS['counter_q'] . ' | Time of the script: ';
echo substr($count, 0, 5) . ' sec | Memory peak usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB</div>-->';
