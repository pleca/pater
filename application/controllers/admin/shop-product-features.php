<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['pages'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Feature.php');
require_once(MODEL_DIR . '/shopProducts.php');

global $feature, $product;

$feature = new Feature();
$product = new Products();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	if (!$feature->add($_POST)) {
		showAdd();
	}

	showList();	

} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	
	if (!$feature->edit($_POST)) {
		showEdit();
	}

	showList();
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$feature->deleteById($_GET['id']);
	showList();
} else {
	showList();
}

function showAdd() {
	global $feature;

	$data = array(
		'tinyMce' => true,
		'pageTitle' => $GLOBALS['LANG']['features_add']
	);

	echo Cms::$twig->render('admin/features/add.twig', $data);	
}

function showEdit($id) {
	global $feature;
	
	$entity = $feature->getById($id);

	$data = array(
		'entity' => $entity,
		'pageTitle' => $GLOBALS['LANG']['features_edit'],
		'tinyMce' => true,
	);

	
	echo Cms::$twig->render('admin/features/edit.twig', $data);		
}

function showList() {
	global $feature, $product;

	$entities = $feature->getAll();	
	$productFeaturesIds = $product->getFeaturesIds();

	$data = array(
		'entities' => $entities,
		'productFeaturesIds' => $productFeaturesIds,
		'pageTitle' => $GLOBALS['LANG']['features_title']
	);

	echo Cms::$twig->render('admin/features/list.twig', $data);	
}


