<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['pages'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Feature.php');
require_once(MODEL_DIR . '/FeatureValue.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/Variation.php');

global $featureValue, $feature, $product, $variation;

$featureValue = new FeatureValue();

$feature = new Feature();
$product = new Products();
$variation = new Variation();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	if (!$featureValue->add($_POST)) {
		showAdd();
	} else {
		showList();	
	}	

} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	
	if (!$featureValue->edit($_POST)) {		
		showEdit($_POST['id']);
	} else {
		showList();
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$featureValue->deleteById($_GET['id']);
	showList();
} else {
	showList();
}

function showAdd() {
	global $featureValue, $feature;

	$features = $feature->getAll(['locale' => CMS::$defaultLocale]);

	$data = array(
		'features' => $features,
		'tinyMce' => true,
		'pageTitle' => $GLOBALS['LANG']['feature_value_add']
	);

	echo Cms::$twig->render('admin/feature_values/add.twig', $data);	
}

function showEdit($id) {
	global $featureValue, $feature;
	
	$entity = $featureValue->getById($id);	
	$features = $feature->getAll(['locale' => CMS::$defaultLocale]);

	$data = array(
		'entity' => $entity,
		'features' => $features,
		'pageTitle' => $GLOBALS['LANG']['feature_value_edit'],
		'tinyMce' => true,
	);

	
	echo Cms::$twig->render('admin/feature_values/edit.twig', $data);		
}

function showList() {
	global $featureValue, $feature, $product, $variation;

	$entities = $featureValue->getAll();
	
	$features = $feature->getAll(['locale' => CMS::$defaultLocale]);
	$features = getArrayByKey($features, 'id');

	$product = new Products();		
	$productFeaturesIds = $product->getFeaturesIds();
	
	$variations = $variation->getAll();	
	$disabledFeatureValuesIds = $variation->getFeatureValuesIds();
	
	$data = array(
		'entities' => $entities,
		'features' => $features,
		'disabledFeatureValuesIds' => $disabledFeatureValuesIds,
		'pageTitle' => $GLOBALS['LANG']['ml_product_feature_values']
	);

	echo Cms::$twig->render('admin/feature_values/list.twig', $data);	
}


