<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['gallery'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['gallery'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/galleryAdmin.php');
global $oGalleryAdmin;

$oGalleryAdmin = new GalleryAdmin();

if (isset($_GET['action']) AND $_GET['action'] == 'addForm') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'addPublish') {
	$oGalleryAdmin->addAdmin($_POST, $aLangs);
	showList();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'addContinue') {
	$id = $oGalleryAdmin->addAdmin($_POST, $aLangs);
	showEdit($id);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'savePublish') {
	$oGalleryAdmin->editAdmin($_POST, $aLangs);
	showList();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveContinue') {
	$oGalleryAdmin->editAdmin($_POST, $aLangs);
	showEdit($_POST['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'photo') {
	showPhoto($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$oGalleryAdmin->deleteAdmin($_GET['id']);
	showList();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'addPhoto') {
	$oGalleryAdmin->addPhotoAdmin($_POST);
	showPhoto($_POST['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'upPhoto') {
	$oGalleryAdmin->moveUpPhotoAdmin($_GET['photo_id']);
	showPhoto($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'downPhoto') {
	$oGalleryAdmin->moveDownPhotoAdmin($_GET['photo_id']);
	showPhoto($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveDesc') {
	$oGalleryAdmin->editDescAdmin($_POST);
	showPhoto($_POST['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'deletePhoto') {
	$oGalleryAdmin->deletePhotoAdmin($_GET['photo_id']);
	showPhoto($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'deleteFile') {
	$oGalleryAdmin->deleteFileAdmin($_GET['file'], $_GET['id']);
	showPhoto($_GET['id']);
} else {
	showList();
}

function showAdd() {
	global $oGalleryAdmin, $aLangs;

	$aDesc = $oGalleryAdmin->loadDescAdmin(0, $aLangs);
	Cms::$tpl->assign('aItem', $_POST);
	Cms::$tpl->assign('aDesc', $aDesc);

	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['gallery_add']);
	Cms::$tpl->assign('tinyMce', true);
	Cms::$tpl->showPage('gallery/add.tpl');
}

function showEdit($id) {
	global $oGalleryAdmin, $aLangs;

	$aItem = $oGalleryAdmin->loadByIdAdmin($id);
	$aDesc = $oGalleryAdmin->loadDescAdmin($aItem['id'], $aLangs);

	Cms::$tpl->assign('aItem', $aItem);
	Cms::$tpl->assign('aDesc', $aDesc);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['gallery_edit']);
	Cms::$tpl->assign('tinyMce', true);
	Cms::$tpl->showPage('gallery/edit.tpl');
}

function showPhoto($id) {
	global $oGalleryAdmin;

	$aItem = $oGalleryAdmin->loadByIdAdmin($id);
	$aPhotos = $oGalleryAdmin->loadPhotos($aItem['id']);
	$aFiles = $oGalleryAdmin->listFilesDir($aItem['id']);

	Cms::$tpl->assign('aItem', $aItem);
	Cms::$tpl->assign('aPhotos', $aPhotos);
	Cms::$tpl->assign('aFiles', $aFiles);
	Cms::$tpl->assign('GALLERY_URL', CMS_URL);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['gallery_photo']);
	Cms::$tpl->showPage('gallery/photo.tpl');
}

function showList() {
	global $oGalleryAdmin;

	$limit = 25;
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;
	$aItems = $oGalleryAdmin->loadAdmin($limitStart, $limit);
	$pages = $oGalleryAdmin->getPagesAdmin($limit);

	Cms::$tpl->assign('aItems', $aItems);
	Cms::$tpl->assign('pages', $pages);
	Cms::$tpl->assign('page', $_GET['page']);
	Cms::$tpl->assign('interval', $limit * ($_GET['page'] - 1));
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['gallery_title']);
	Cms::$tpl->showPage('gallery/list.tpl');
}
