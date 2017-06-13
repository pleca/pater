<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['news'] != 1)
	die;

require_once(CMS_DIR . '/application/models/pages.php');
$oPages = new Pages();

$url = isset($params[1]) ? $params[1] : '';

if ($entity = $oPages->loadType("'" . $url . "'")) {
	
    $data = array(
        'entity' => $entity[0],
        'pageTitle' => $entity[0]['title'] . ' - ' . Cms::$seo['title'],
        'pageKeywords' => Cms::$seo['meta_keywords'],
        'pageDescription' => $entity[0]['desc_short'],
        'pageTitle' => $entity[0]['title'] . ' - ' . Cms::$seo['title'],	
    );	
	
	echo Cms::$twig->render('templates/pages/info.twig', $data); 
} else {
	Cms::getFlashBag()->add('info', 'Brak strony');
}




     


