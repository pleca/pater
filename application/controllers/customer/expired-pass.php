<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if (isset($_GET['login'])) {
	$_POST['login'] = $_GET['login'];
}

if (isset($_POST['action']) AND $_POST['action'] == 'change_password') {
	if ($oCustomer->passwordExpiredChange($_POST)) {
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm4']);
		redirect(URL . '/customer.html');
	}
} else {
	Cms::getFlashBag()->add('info', $GLOBALS['LANG']['password_expired']);
}

$data = array(
	'pageTitle' => $GLOBALS['LANG']['c_login'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/expired-pass.twig', array_merge($data, $params)); 
