<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['shop'] != 1)
	die;

require_once(CMS_DIR . '/application/models/shopProducts.php');
$oProducts = new Products();

$keyword = isset($params[1]) ? $params[1] : '';
if (isset($params[1]))
	$keyword = str_replace('-', ' ', $keyword);
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $keyword;
$keyword = strip_tags(urldecode($keyword));

if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
	$_GET['page'] = 1;
}

$limit = 20;
$params = array(
	'limit' => $limit,
	'start' => ($_GET['page'] - 1) * $limit,
	'resultType'	=> 'search',
	'keyword' => $keyword,
	'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
);

$entities = $oProducts->getBy($params);

$aProducts2 = $oProducts->getBy(array_merge($params, ['resultType' => 'count']));
$pages = count($aProducts2);
$pages =  ceil($pages / $limit);


if ($keyword) {
    $keyword = ': ' . $keyword;
}

$data = array(
	'entities' => $entities,
    'pages' => $pages,
    'page' => $_GET['page'],
    'qs' => '&sort=' . $params['sort'] . '&keyword=' . $params['keyword'],
    'title' => $GLOBALS['LANG']['s_title'] . $keyword,
    'pageTitle' => $GLOBALS['LANG']['s_title'] . ' - ' . Cms::$seo['title'],
    'pageKeywords' => Cms::$seo['meta_keywords'],
    'pageDescription' => Cms::$seo['meta_description']	
);

echo Cms::$twig->render('templates/shop/list.twig', array_merge($data, $params));














//$filtr = [];
//$filtr['limit'] = $limit;
//$filtr['start'] = ($_GET['page'] - 1) * $filtr['limit'];
//$filtr['type'] = 'list';	// list - lista produktow, count - ilosc wszystkich wynikow
//$filtr['keyword'] = $keyword;
//$filtr['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';	// sotowanie: name_asc, name_desc, price_asc, price_desc, date_asc, date_desc
//$entities = $oProducts->getAll($filtr);
//
//$filtr['type'] = 'count';	// list - lista produktow, count - ilosc wszystkich wynikow
//$aProducts2 = $oProducts->getAll($filtr);
//$pages = count($aProducts2);
//unset($aProducts2);
//if ($pages < 1) {
//	$pages = 1;
//}
//$pages =  ceil($pages / $limit);
//
//if ($keyword) {
//    $keyword = ': ' . $keyword;
//}
//        
//$data = array(
//    'entities' => $entities,
//    'pages' => $pages,
//    'page' => $_GET['page'],
//    'qs' => '&sort=' . $filtr['sort'] . '&keyword=' . $filtr['keyword'],
//    'title' => $GLOBALS['LANG']['s_title'] . $keyword,
//    'pageTitle' => $GLOBALS['LANG']['s_title'] . ' - ' . Cms::$seo['title'],
//    'pageKeywords' => Cms::$seo['meta_keywords'],
//    'pageDescription' => Cms::$seo['meta_description']			
//);
//
//echo Cms::$twig->render('templates/shop/list.twig', array_merge($data, $params));
