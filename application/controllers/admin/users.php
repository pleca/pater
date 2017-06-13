<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['users'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['users'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/user.php');
global $user;

$user = new User();
$parameters = [];     
if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	if ($user->add($_POST)) {
		$parameters['info'] = $GLOBALS['LANG']['info_add'];
		showList($parameters);
	} else {
		$parameters['error'] = $GLOBALS['LANG']['error_add'];
		showAdd($parameters);
	}
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['sid']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	if ($user->edit($_POST)) {
		$parameters['info'] = $GLOBALS['LANG']['info_config'];
		showList($parameters);
	} else {
		$parameters['error'] = $GLOBALS['LANG']['error_change'];
		showEdit($_POST['sid'], $parameters);
	}
} elseif (isset($_POST['action']) AND $_POST['action'] == 'savePriv') {
	if ($user->editPriv($_POST)) {
		$parameters['info'] = $GLOBALS['LANG']['info_config'];
	} else {
		$parameters['error'] = $GLOBALS['LANG']['error_change'];
	}
	
	showEdit($_POST['sid'], $parameters);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveActions') {
	if ($user->editActions($_POST)) {
		$parameters['info'] = $GLOBALS['LANG']['info_config'];
	} else {
		$parameters['error'] = $GLOBALS['LANG']['error_change'];
	}
	showEdit($_POST['sid'], $parameters);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'savePass') {
	if ($user->editPass($_POST)) {
		$parameters['info'] = $GLOBALS['LANG']['info_config'];
	} else {
		$parameters['error'] = $GLOBALS['LANG']['error_change'];
	}
	
	showEdit($_POST['sid'], $parameters);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	if ($user->delete($_GET['sid'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
	showList($parameters);
} else {
	showList($parameters);
}

function showAdd($parameters = []) {
	global $user;

	$entity = array("login" => '', "password" => '', "active" => '', "name" => '', "surname" => '', "email" => '');
	$entity = isset($_POST['action']) ? $_POST : $entity;
	
	$params = array(
		'entity' => $entity,
		'pageTitle'=> $GLOBALS['LANG']['users_add']
	);

	echo Cms::$twig->render('admin/users/add.twig', array_merge($params, $parameters));	
}

function showEdit($sid, $parameters = []) {
	global $user;

	$entity = $user->loadAdminById($sid);

//	$aItem = $user->loadAdminById($sid);
//	Cms::$tpl->assign('aItem', $aItem);
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['users_edit']);
//	Cms::$tpl->showPage('users/edit.tpl');
//	dump($entity);

	$params = array(
		'entity' => $entity,
		'pageTitle'=> $GLOBALS['LANG']['users_edit']
	);	
	
	echo Cms::$twig->render('admin/users/edit.twig', array_merge($params, $parameters));
}

function showList($parameters = []) {
	global $user;

	$entities = $user->loadAdmin();

	$params = array(
		'entities' => $entities,
		'pageTitle'=> $GLOBALS['LANG']['users_title']
	);
	
	echo Cms::$twig->render('admin/users/list.twig', array_merge($params, $parameters));
}
