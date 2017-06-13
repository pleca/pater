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

$params = array(
	'limit' => $limit,
	'start' => ($_GET['page'] - 1) * $limit,
	'resultType'	=> 'list',
	'new'	=> 1,
	'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
);	

$entities = $oProducts->getBy($params);	

$aProducts2 = $oProducts->getBy(array_merge($params, ['resultType' => 'count']));
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
    'qs' => '&sort=' . $params['sort'],
    'title' => $GLOBALS['LANG']['shop_new'],
    'pageTitle' => $GLOBALS['LANG']['shop_new'] . ' - ' . Cms::$seo['title'],
    'pageKeywords' => Cms::$seo['meta_keywords'],
    'pageDescription' => Cms::$seo['meta_description']			
);

echo Cms::$twig->render('templates/shop/list.twig', array_merge($data, $params));


