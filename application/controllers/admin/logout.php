<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/user.php');
$user = new User();

$user->logout($_POST);
header('Location: ' . CMS_URL . '/admin.html');
die;


