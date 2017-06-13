<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$aItem = array("type" => '', "company_name" => '', "nip" => '', "first_name" => '', "last_name" => '', "email" => '', "phone" => '', "country" => '', "address1" => '', "address2" => '',
	"address3" => '', "post_code" => '', "post_code1" => '', "post_code2" => '', "city" => '', "login2" => '', "pass2" => '', "pass3" => '');

if (isset($_POST['action']) AND $_POST['action'] == 'add2') {
	if (!$oCustomer->add2($_POST)) {
		$aItem = $_POST;
	} else {
		if (Cms::$modules['shop'] == 1) {
			$oBasket->saveBasket();
		}

		$aItem = $_SESSION['customer'];
		$params['redirect'] = true;
	}
} elseif (isset($_POST['action']) AND $_POST['action'] == 'login') {
	if (!$oCustomer->login($_POST)) {
		$aItem2 = $_POST;
	} else {
		if (Cms::$modules['shop'] == 1)
			$oBasket->saveBasket();

		$params['logged'] = true;
		$params['customer'] = $_SESSION['customer'];
		$params['redirect'] = true;
	}
}

$country = $oCustomer->loadCountry();

$data = array(
	'aItem' => $aItem,
	'country' => $country,
	'pageTitle' => $GLOBALS['LANG']['c_login'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/login2.twig', array_merge($data, $params)); 