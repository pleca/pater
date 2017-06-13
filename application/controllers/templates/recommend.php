<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if (Cms::$modules['shop'] != 1) {
	die;
}

require_once(CMS_DIR . '/application/models/shopProducts.php');
$oProducts = new Products();

if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
	$_GET['page'] = 1;
}

$limit = 20;
$filtr = [];
$filtr['limit'] = $limit;
$filtr['start'] = ($_GET['page'] - 1) * $filtr['limit'];
$filtr['type'] = 'list';	// list - lista produktow, count - ilosc wszystkich wynikow
$filtr['recommended'] = 1;
$filtr['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';	// sotowanie: name_asc, name_desc, price_asc, price_desc, date_asc, date_desc
$entities = $oProducts->getAll($filtr);

$filtr['type'] = 'count';	// list - lista produktow, count - ilosc wszystkich wynikow
$aProducts2 = $oProducts->getAll($filtr);
$pages = count($aProducts2);
unset($aProducts2);

if ($pages < 1) {
	$pages = 1;
}

$pages =  ceil($pages / $limit);

$data = array(
    'entities' => $entities,
    'pages' => $pages,
    'page' => $_GET['page'],
    'qs' => '&sort=' . $filtr['sort'],
    'title' => $GLOBALS['LANG']['shop_recommend_title'],
    'pageTitle' => $GLOBALS['LANG']['shop_recommend_title'] . ' - ' . Cms::$seo['title'],
    'pageKeywords' => Cms::$seo['meta_keywords'],
    'pageDescription' => Cms::$seo['meta_description']			
);

echo Cms::$twig->render('templates/shop/list.twig', array_merge($data, $params));
