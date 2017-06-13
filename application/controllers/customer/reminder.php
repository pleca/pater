<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (isset($_POST['action']) AND $_POST['action'] == 'reminder') {
	if ($oCustomer->reminder($_POST)) {
		$params['redirect'] = true;
	}
}

$data = array(
	'aItem' => $_POST,
	'pageTitle' => $GLOBALS['LANG']['c_reminder'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/reminder.twig', array_merge($data, $params));

