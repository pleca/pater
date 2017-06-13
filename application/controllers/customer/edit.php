<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/transportCountry.php');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

if (isset($_POST['action']) AND $_POST['action'] == 'edit') {
  
	if (!$oCustomer->edit($_POST, $_FILES)) {
		$params['entity'] = $_POST;
	}
	$customer = $oCustomer->loadById($_SESSION[CUSTOMER_CODE]['id']);
	$params['customer'] = $customer;
} elseif (isset($_POST['action']) AND $_POST['action'] == 'address') {

	if (!$oCustomer->address($_POST)) {
		$params['entity'] = $_POST;
	} else {

		$customer = $oCustomer->loadById($_SESSION[CUSTOMER_CODE]['id']);
		$params['entity'] = $customer;
		$params['customer'] = $customer;
        
	}
}

$customer = $oCustomer->loadById($_SESSION[CUSTOMER_CODE]['id']);
$countries = $oCustomer->loadCountry();
$params['selected'] = $params[1];

$transportCountry = new TransportCountryModel();
$transportEnabledCountries = $transportCountry->getEnabledCountries();

$data = array(
	'entity' => $customer,
//	'countries' => $countries,
	'transportEnabledCountries' => $transportEnabledCountries,
	'pageTitle' => $GLOBALS['LANG']['c_edit'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/edit.twig', array_merge($data, $params));
