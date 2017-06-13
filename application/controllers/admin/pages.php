<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['pages'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['pages'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Page.php');
global $page;

$page = new Page();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'addPublish') {

	if (!$page->add($_POST)) {
		showAdd();
	}

	showList($params);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'addContinue') {
	
	if (!$id = $page->add($_POST)) {
		showAdd();
	}

	showEdit($id);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'savePublish') {
	
	if (!$page->edit($_POST)) {
		showEdit();
	}

	showList();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveContinue') {	
	$page->edit($_POST);
	showEdit($_POST['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$page->deleteById($_GET['id']);
	showList();
} else {
	showList();
}

function showAdd($params = []) {
	global $page;

	if (Cms::$modules['gallery'] == 1) {
		require_once(CMS_DIR . '/application/models/gallery.php');
		$oGallery = new Gallery();
		$option_gallery = $oGallery->loadViews();
		$params['gallery'] = true;
		$params['option_gallery'] = $option_gallery;
	}

	$data = array(
		'tinyMce' => true,
		'pageTitle' => $GLOBALS['LANG']['pages_add']
	);

	echo Cms::$twig->render('admin/pages/add.twig', array_merge($data, $params));	
}

function showEdit($id) {
	global $page;
	
	$entity = $page->getById($id);

	$data = array(
		'entity' => $entity,
		'pageTitle' => $GLOBALS['LANG']['pages_edit'],
		'tinyMce' => true,
	);
	
	if (Cms::$modules['gallery'] == 1) {
		require_once(CMS_DIR . '/application/models/gallery.php');
		$oGallery = new Gallery();
		$option_gallery = $oGallery->loadViews();
		$data['gallery'] = true; 
		$data['option_gallery'] = $option_gallery;
	}
	
	echo Cms::$twig->render('admin/pages/edit.twig', $data);		
}

function showList() {
	global $page;

	$entities = $page->getAll();
	
	$data = array(
		'entities' => $entities,
		'pageTitle' => $GLOBALS['LANG']['pages_title']
	);

	echo Cms::$twig->render('admin/pages/list.twig', $data);	
}
