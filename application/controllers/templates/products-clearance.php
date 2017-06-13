<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['shop'] != 1)
	die;
require_once(CMS_DIR . '/application/models/shopProducts.php');
$oProducts = new Products();

$limit = CMS::$conf['shop_limit_page'];
;
if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
	$_GET['page'] = 1;
$limitStart = ($_GET['page'] - 1) * $limit;
$entities = $oProducts->loadProducts('clearance', '', $limitStart, $limit);
$pages = $oProducts->getPages('clearance', '', $limit);

$data = array(
	'entities' => $entities,
	'pages' => $pages,
	'page' => $_GET['page'],
	'title' => $GLOBALS['LANG']['shop_clearance_title'],
	'pageTitle' => $GLOBALS['LANG']['shop_clearance_title'],
	'pageKeywords' => Cms::$seo['meta_keywords'],
	'pageDescription' => Cms::$seo['meta_description'],	
);

echo Cms::$twig->render('templates/shop/list.twig', $data);	


