<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['customer'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['customer'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/customerAdmin.php');
require_once(MODEL_DIR . '/transportCountry.php');
require_once(MODEL_DIR . '/SalesRepresentative.php');
global $oCustomerAdmin;

$oCustomerAdmin = new CustomerAdmin();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	$result = $oCustomerAdmin->addAdmin($_POST);

	if ($result === true or is_numeric($result)) {
		$params['info'] = $GLOBALS['LANG']['customers_new1'];
		showEdit($result, $params);		
	} else {
		$params['error'] = $GLOBALS['LANG']['customers_new2'] . ' ' . $result;
		showAdd($params);			
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	$result = $oCustomerAdmin->editAdmin($_POST);
	
	if ($result === true) {
		$params['info'] = $GLOBALS['LANG']['customers_edit1'];
	} else {
		$params['error'] = $GLOBALS['LANG']['customers_edit2'] . ' ' . $result;
	}
	
	showEdit($_POST['id'], $params);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	if ($oCustomerAdmin->deleteAdmin($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['customers_del1'];
	} else {
		$params['error'] = $GLOBALS['LANG']['customers_del2'];
	}
	showList($params);
} else {
	showList();
}

function showAdd($params = []) {
	global $oCustomerAdmin;

	$transportCountry = new TransportCountryModel();
	$transportEnabledCountries = $transportCountry->getEnabledCountries();	
//	$country = $oCustomerAdmin->loadCountry();

	$entity = array("first_name" => '', "last_name" => '', "email" => '', "login" => '', "pass" => '', "type" => '', "company_name" => '', "nip" => '',
		"country" => '', "address1" => '', "address2" => '', "address3" => '', "post_code" => '', "post_code1" => '', "post_code2" => '', "city" => '', "phone" => '', "active" => '');
	$entity = isset($_POST['action']) ? $_POST : $entity;

	$data = array(
		'entity'	=> $entity,
//		'country'	=> $country,
		'transportEnabledCountries' => $transportEnabledCountries,
		'pageTitle' => $GLOBALS['LANG']['customers_add'],
	);

	echo Cms::$twig->render('admin/customers/add.twig', array_merge($data, $params));		
}

function showEdit($id, $params = []) {
	global $oCustomerAdmin;

	$entity = $oCustomerAdmin->loadByIdAdmin($id);

//	$country = $oCustomerAdmin->loadCountry();
	$transportCountry = new TransportCountryModel();
	$transportEnabledCountries = $transportCountry->getEnabledCountries();

	$salesRepresentative = new SalesRepresentative;
	$salesRepresentatives = $salesRepresentative->getAll();

	$data = array(
		'entity'	=> $entity,
//		'country'	=> $country,
		'transportEnabledCountries' => $transportEnabledCountries,
		'salesRepresentatives' => $salesRepresentatives,
		'pageTitle' => $GLOBALS['LANG']['customers_edit'],
	);

	echo Cms::$twig->render('admin/customers/edit.twig', array_merge($data, $params));		
}

function showList($params = []) {
	global $oCustomerAdmin;

	$_GET['first_name'] = isset($_GET['first_name']) ? $_GET['first_name'] : '';
	$_GET['last_name'] = isset($_GET['last_name']) ? $_GET['last_name'] : '';
	$_GET['email'] = isset($_GET['email']) ? $_GET['email'] : '';
	$_GET['login'] = isset($_GET['login']) ? $_GET['login'] : '';

	$limit = 100;
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;
	if (isset($_GET['action']) AND $_GET['action'] == 'search') {
		$params['qs'] = '&amp;first_name=' . $_GET['first_name'] . '&amp;last_name=' . $_GET['last_name'] . '&amp;email=' . $_GET['email'] . '&amp;login=' . $_GET['login'] . '&amp;action=search';
	}
	$entities = $oCustomerAdmin->loadAdmin($_GET, $limitStart, $limit);
	$pages = $oCustomerAdmin->getPagesAdmin($_GET, $limit);

	$data = array(
		'entities'	=> $entities,
		'pages'	=> $pages,
		'interval'	=> $limit * ($_GET['page'] - 1),
		'pageTitle' => $GLOBALS['LANG']['customers_title'],
	);

	echo Cms::$twig->render('admin/customers/list.twig', array_merge($data, $params));	
}
