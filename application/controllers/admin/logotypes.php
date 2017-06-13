<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Logotype.php');

$entity = new Logotype;

if (isset($_POST['action']) AND $_POST['action'] == 'add') {	
    if ($entity->add($_POST)) {
        Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
    } else {
        Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
    }
    
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$params['item'] = $entity->loadByIdAdmin($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	
	if ($entity->editAdmin($_POST)) {
		$params['info'] = $GLOBALS['LANG']['info_edit'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$entity->deleteById($_GET['id']);

} elseif (isset($_GET['action']) AND $_GET['action'] == 'image_delete') {

	if ($entity->deleteImageByName($_GET['file'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
}


$filter = array(
    'orderBy'   => 'order'
);

$entities = $entity->getAll($filter);

$data = array(
	'entities' => $entities,
	'pageTitle' => $GLOBALS['LANG']['ml_logotypes']
);

echo Cms::$twig->render('admin/logotypes/list.twig', array_merge($data, $params));


