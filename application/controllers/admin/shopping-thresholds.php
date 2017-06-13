<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('shopping_thresholds', 'module'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/ShoppingThresholds.php');

$shoppingThresholds = new ShoppingThresholds();

if (isset($_POST['action']) AND $_POST['action'] == 'add') {
    $item = [];
    $item['value'] = $_POST['value'];
    $item['discount'] = $_POST['discount'];
    if ($shoppingThresholds->set($item)) {
		$params['info'] = $GLOBALS['LANG']['info_add'];
    } else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
    }
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$item = $shoppingThresholds->getById($_GET['id'])['0'];
	$params['item'] = $item;
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
    $item = [];
    $item['value'] = $_POST['value'];
    $item['discount'] = $_POST['discount'];
    if ($shoppingThresholds->updateById($_POST['id'], $item)) {
		$params['info'] = $GLOBALS['LANG']['info_config'];
    } else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
    }
    
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	if ($shoppingThresholds->deleteById($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
	
}

$entities = $shoppingThresholds->getAll();

$data = array(
	'entities' => $entities,
	'pageTitle' => $GLOBALS['LANG']['shopping_thresholds']
);

echo Cms::$twig->render('admin/shopping_thresholds/list.twig', array_merge($data, $params));
