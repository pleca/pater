<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('unit_transport', 'module'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/UnitTransportUnit.php');

$entity = new UnitTransportUnit();

if (isset($_POST['action']) AND $_POST['action'] == 'add') {
    $item = [];
    $item['length'] = $_POST['length'];
    $item['width'] = $_POST['width'];
    $item['height'] = $_POST['height'];
    $item['price'] = $_POST['price'];
    if ($entity->set($item)) {
		$params['info'] = $GLOBALS['LANG']['info_add'];
    } else {
		$params['error'] = $GLOBALS['LANG']['error_add'];
    }
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$item = $entity->getById($_GET['id'])['0'];
	$params['item'] = $item;
	$params['url_back'] = CMS_URL . '/admin/unit-transport-unit.php';
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
    $item = [];
    $item['length'] = $_POST['length'];
    $item['width'] = $_POST['width'];
    $item['height'] = $_POST['height'];
	$item['price'] = $_POST['price'];
    if ($entity->updateById($_POST['id'], $item)) {
		$params['info'] = $GLOBALS['LANG']['info_config'];
    } else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
    }
    
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	if ($entity->deleteById($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
}

$data = array(
	'entities' => $entity->getAll(),
	'pageTitle' => $GLOBALS['LANG']['transport_units']
);

echo Cms::$twig->render('admin/unit_transport_unit/list.twig', array_merge($data, $params));
