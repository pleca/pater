<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Category.php');
global $category;

$category = new Category();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	if (!$category->add($_POST)) {
		showAdd();
	}
	showList();	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	$category->edit($_POST);
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$category->deleteById($_GET['id']);
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'up') {
	$category->moveUp($_GET['id']);
	showList();
} elseif (isset($_GET['action']) AND $_GET['action'] == 'down') {
	$category->moveDown($_GET['id']);
	showList();
} else {
	showList();
}


function showAdd() {
	global $category;

	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);

	$data = array(
		'categories' => $categories,	
		'pageTitle' => $GLOBALS['LANG']['shop_cat_title']
	);

	echo Cms::$twig->render('admin/categories/add.twig', $data);	
}

function showEdit($id) {
	global $category;
	
	$entity = $category->getById($id);
	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);

	$data = array(
		'entity' => $entity,
		'categories' => $categories,
		'pageTitle' => $GLOBALS['LANG']['shop_cat_title'],
		'tinyMce' => true,
	);
	
	echo Cms::$twig->render('admin/categories/edit.twig', $data);		
}

function showList() {
	global $category;

	$parentId = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0;

	$entities = $category->getAll(['parent_id' => $parentId]);
	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);

	$data = array(
		'entities' => $entities,
		'categories' => $categories,
		'parent_id' => $parentId,
		'pageTitle' => $GLOBALS['LANG']['shop_cat_title']
	);	
	
	echo Cms::$twig->render('admin/categories/list.twig', $data);	
}
