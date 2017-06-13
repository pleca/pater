<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['main'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['main'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/glowna.php');
$oGlowna = new Glowna();

if (isset($_POST['action']) AND $_POST['action'] == 'saveDesc') {
	$oGlowna->saveDesc($_POST);
	Cms::$tpl->setInfo('Zapisano zmiany');
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveImg') {
	$oGlowna->saveImg($_POST, $_FILES);
	Cms::$tpl->setInfo('Zapisano zmiany');
} elseif (isset($_POST['action']) AND $_POST['action'] == 'saveSzcze') {
	$oGlowna->saveSzcze($_POST);
	Cms::$tpl->setInfo('Zapisano zmiany');
}

$aItem = $oGlowna->loadAdminById();
Cms::$tpl->assign('aItem', $aItem);
Cms::$tpl->assign('pageTitle', 'Zawartość strony głównej');
Cms::$tpl->showPage('pages/glowna.tpl');


