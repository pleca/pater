<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['menu'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['menu'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/Menu.php');
require_once(MODEL_DIR . '/Module.php');
require_once(MODEL_DIR . '/Page.php');
global $menu;

$menu = new Menu();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	
	if (!$menu->add($_POST)) {
		redirect(URL . '/admin/menu.html');	
	}
	
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
	
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
	
	$menu->edit($_POST);
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	
	$menu->deleteById($_GET['id']);
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'up') {	
	$menu->moveUp($_GET['id']);
	showList();
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'down') {	
	$menu->moveDown($_GET['id']);
	showList();

} else {
	showList();
}

function showEdit($id) {
	global $menu;
	
	$group = isset($_REQUEST['group']) ? $_REQUEST['group'] : Menu::$menuGroups[0];
	$entity = $menu->getById($id);

	$module = new Module();
	$modules = $module->getMenuModules();
	
	$page = new Page();
	$pages = $page->getMenuPages();	
	
	$data = array(
		'entity' => $entity,
		'groups' => Menu::$menuGroups,
		'group' => $group,	
		'modules'  => $modules,
		'pages'  => $pages,		
		'pageTitle' => $GLOBALS['LANG']['menu_title'],
		'tinyMce' => true,
	);
		
	echo Cms::$twig->render('admin/menu/edit.twig', $data);		
}

function showList() {
	global $menu;
	
	$parentId = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0;
	$group = isset($_REQUEST['group']) ? $_REQUEST['group'] : Menu::$menuGroups[0];

	$params = array(
		'group' => $group, 
		'parent_id' => $parentId
	);

	$entities = $menu->getAll($params);	

	$data = array(
		'menu' => $menu,
		'entities' => $entities,
		'parentId' => $parentId,
		'groups' => Menu::$menuGroups,
		'group' => $group,
		'pageTitle' => $GLOBALS['LANG']['menu_title']
	);

	echo Cms::$twig->render('admin/menu/list.twig', $data);	
}

function showAdd() {

	$group = isset($_REQUEST['group']) ? $_REQUEST['group'] : Menu::$menuGroups[0];

	$module = new Module();
	$modules = $module->getMenuModules();

	$page = new Page();
	$pages = $page->getMenuPages();

	$data = array(
		'tinyMce' => true,
		'groups' => Menu::$menuGroups,
		'group' => $group,
		'modules'  => $modules,
		'pages'  => $pages,
		'pageTitle' => $GLOBALS['LANG']['menu_title']
	);

	echo Cms::$twig->render('admin/menu/add.twig', $data);	
}


