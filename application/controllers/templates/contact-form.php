<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['contact'] != 1)
	die;

require_once(MODEL_DIR . '/Contact.php');
$contact = new Contact();

$page = new Page();
$pageContactForm = $page->getByTitle('contact_form');

$params['pageContactForm'] = $pageContactForm['content'];

if (isset($_POST['action']) AND $_POST['action'] == 'send') {
    if ($contact->isValid($_POST)) {
        $contact->sendEmailContact($_POST);
        $params['was_sent'] = true;
    }
}

$contacts = $contact->loadAddress();

if (LOGGED != 1)
	require_once(CMS_DIR . '/application/models/captcha.php');  // captcha obrakowy

$params['contacts'] = $contacts;
$params['pageTitle'] = $GLOBALS['LANG']['contact_title'];
$params['pageKeywords'] = Cms::$seo['meta_keywords'];
$params['pageDescription'] = Cms::$seo['meta_description'];

echo Cms::$twig->render('templates/other/contact.twig', $params);


