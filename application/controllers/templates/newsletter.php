<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['newsletter'] != 1)
	die;

require_once(CMS_DIR . '/application/models/newsletter.php');
$oNewsletter = new Newsletter();

$url = isset($params[1]) ? $params[1] : '';

if (isset($_POST['action']) AND $_POST['action'] == 'unsubscribe') {
	if ($oNewsletter->unsubscribeEmail($_POST)) {
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['newsletter_thank2']);
	} else {
		showUnsubscribe();
	}
} elseif ($url == 'unsubscribe') {
	showUnsubscribe();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	if ($oNewsletter->addEmail($_POST)) {
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['newsletter_thank1']);
		$lastUrl = $_SERVER['HTTP_REFERER'];
		redirect($lastUrl);
	} else {
		showAdd();
	}
} else {
	showAdd();
}

function showAdd() {	
	
	$data = array(
		'entity'	=>	$_POST,
		'pageTitle' => $GLOBALS['LANG']['newsletter_title'] . ' - ' . Cms::$seo['title'],
		'pageKeywords' => Cms::$seo['meta_keywords'],
		'pageDescription' => Cms::$seo['meta_description'],

	);

	echo Cms::$twig->render('templates/newsletter/add.twig', $data);		
}

function showUnsubscribe() {	
	$data = array(
		'pageTitle' => $GLOBALS['LANG']['newsletter_unsubscribe'] . ' - ' . Cms::$seo['title'],
		'pageKeywords' => Cms::$seo['meta_keywords'],
		'pageDescription' => Cms::$seo['meta_description'],

	);

	echo Cms::$twig->render('templates/newsletter/unsubscribe.twig', $data);		
}
