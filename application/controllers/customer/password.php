<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

$params['selected'] = $params[1];

if (isset($_POST['action']) AND $_POST['action'] == 'password') {
	if ($oCustomer->password($_POST)) {
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['c_confirm4']);
	}
}

$data = array(
	'pageTitle' => $GLOBALS['LANG']['c_password4'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/password.twig', array_merge($data, $params));

