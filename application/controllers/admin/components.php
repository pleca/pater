<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$module = isset($params[2]) ? $params[2] : '';
$book = isset($params[3]) ? $params[3] : '';

if ($module == 'contact') {
	if (Cms::$modules['contact'] != 1)
		die('This module is disabled!');
	if ($_SESSION[USER_CODE]['privilege']['contact'] != 1)
		die('No permission at this level!');

	require_once(CMS_DIR . '/application/models/contact.php');
	$oContact = new Contact();

	if (isset($_POST['action']) AND $_POST['action'] == 'add') {
		$oContact->add($_POST);
	} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
		$oContact->edit($_POST);
	} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
		$oContact->delete($_GET['id']);
	}

	$aContact = $oContact->loadAddress();
	Cms::$tpl->assign('module', $module);
	Cms::$tpl->assign('aContact', $aContact);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['components_title'] . ' -> ' . $GLOBALS['LANG']['components_contact']);
	Cms::$tpl->showPage('components/contact.tpl');
} elseif ($module == 'phrase') {
	if (Cms::$modules['phrase'] != 1)
		die('This module is disabled!');
	if ($_SESSION[USER_CODE]['privilege']['phrase'] != 1)
		die('No permission at this level!');

	require_once(CMS_DIR . '/application/models/phrase.php');
	$oPhrase = new Phrase();

	if (isset($_POST['action']) AND $_POST['action'] == 'add') {
		$oPhrase->add($_POST);
	} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {
		$oPhrase->edit($_POST);
	} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
		$oPhrase->delete($_GET['id']);
	}

	$aPhrase = $oPhrase->loadAdmin();
	$aUzycia = $oPhrase->loadUzycia();
	Cms::$tpl->assign('module', $module);
	Cms::$tpl->assign('aPhrase', $aPhrase);
	Cms::$tpl->assign('aUzycia', $aUzycia);
	Cms::$tpl->assign('datepicker', true);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['components_title'] . ' -> ' . $GLOBALS['LANG']['components_phrase']);
	Cms::$tpl->showPage('components/phrase.tpl');
} else {
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['components_title']);
	Cms::$tpl->showPage('components/start.tpl');
}
