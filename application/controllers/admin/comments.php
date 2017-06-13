<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['comments'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['comments'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/comments.php');
global $oComments;

$oComments = new Comments();

if (isset($_POST['action']) AND $_POST['action'] == 'save') {
	$oComments->edit($_POST);
	showList();
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$user_id = $oComments->getUserId($_GET['id']);
	$oComments->delete($_GET['id']);
	showList();
} else {
	showList();
}

function showList() {
	global $oComments, $aLangs;

	$limit = 50;
	$filtr['active'] = isset($_REQUEST['filtr_active']) ? $_REQUEST['filtr_active'] : '';
	$filtr['group'] = isset($_REQUEST['filtr_group']) ? $_REQUEST['filtr_group'] : '';
	$filtr['lang'] = isset($_REQUEST['filtr_lang']) ? $_REQUEST['filtr_lang'] : '';
	$agent = isset($_REQUEST['agent']) ? $_REQUEST['agent'] : '';
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;
	$filtrQ = $oComments->setFiltr($filtr);
	$groups = $oComments->getGroups();
	$aItems = $oComments->loadAdmin($limitStart, $limit, $filtrQ);
	$pages = $oComments->getPagesAdmin($limit, $filtrQ);
	Cms::$tpl->assign('aItems', $aItems);
	Cms::$tpl->assign('aLangs', $aLangs);
	Cms::$tpl->assign('pages', $pages);
	Cms::$tpl->assign('groups', $groups);
	Cms::$tpl->assign('page', $_GET['page']);
	Cms::$tpl->assign('interval', $limit * ($_GET['page'] - 1));
	Cms::$tpl->assign('qs', '&amp;filtr_active=' . $filtr['active'] . '&amp;filtr_group=' . $filtr['group'] . '&amp;filtr_lang=' . $filtr['lang']);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['comments_title']);
	Cms::$tpl->showPage('other/comments.tpl');
}
