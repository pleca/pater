<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/shopProducersAdmin.php');
$oProducersAdmin = new ProducersAdmin();

if (isset($_POST['action']) AND $_POST['action'] == 'add') {	
	if ($oProducersAdmin->addAdmin($_POST)) {
		$parmas['info'] = $GLOBALS['LANG']['info_add'];
	} else {
		$parmas['error'] = $GLOBALS['LANG']['error_add'];
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$params['item'] = $oProducersAdmin->loadByIdAdmin($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	
	if ($oProducersAdmin->editAdmin($_POST)) {
		$params['info'] = $GLOBALS['LANG']['info_edit'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	
	if ($oProducersAdmin->deleteAdmin($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'image_delete') {
	if ($oProducersAdmin->deleteImageByName($_GET['file'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
}

$entities = $oProducersAdmin->loadAdmin();

$data = array(
	'entities' => $entities,
	'pageTitle' => $GLOBALS['LANG']['shop_producer_title']
);

echo Cms::$twig->render('admin/shop/producers.twig', array_merge($data, $params));


