<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

$customer = $oCustomer->loadById($_SESSION['customer']['id']);
$params['aItem'] = $customer;
$params['selected'] = $params[1];


if (isset($_POST['action']) AND $_POST['action'] == 'address') {
	if (!$oCustomer->address($_POST)) {
		$params['aItem'] = $_POST;
	} else {
		$customer = $oCustomer->loadById($_SESSION['customer']['id']);
		$params['aItem'] = $customer;
		$params['customer'] = $customer;
	}
}

$countries = $oCustomer->loadCountry();

$data = array(
	'countries' => $countries,
	'pageTitle' => $GLOBALS['LANG']['c_address'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/address.twig', array_merge($data, $params)); 

