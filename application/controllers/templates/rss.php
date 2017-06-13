<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if (Cms::$modules['shop'] != 1) {
	die;
}

$product  = new Product(['decorate' => true]);

$params = array(
	'locale' => Cms::$session->get('locale'),
	'limit'	=> 5000
);

$fields = ['name', 'date_add', 'content_short'];
$products = $product->getAll($params, $fields);

$data = array(
	'products' => $products
);

echo Cms::$twig->render('templates/other/rss.twig', $data);	
die;



/*
$limit = 5000;
$filtr = [];
$filtr['limit'] = $limit;
$filtr['start'] = ($_GET['page'] - 1) * $filtr['limit'];
$filtr['type'] = 'list';	// list - lista produktow, count - ilosc wszystkich wynikow
$filtr['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';	// sotowanie: name_asc, name_desc, price_asc, price_desc, date_asc, date_desc
$products = $oProducts->getAll($filtr);
echo '<pre>';
dump($products);
echo '</pre>';
die;
$data = array(
	'products' => $products
);
*/

//
//if (Cms::$modules['shop'] == 1) {
//	require_once(CMS_DIR . '/application/models/shopProducts.php');
//	$oProducts = new Products();
//	$aProducts = $oProducts->loadProducts('rss', '', 0, 5000);
//	Cms::$tpl->assign('aProducts', $aProducts);
//}
//
//if (Cms::$modules['pages'] == 1) {
//	require_once(CMS_DIR . '/application/models/pages.php');
//	$oPages = new Pages();
//	$aPages = $oPages->loadRss();
//	Cms::$tpl->assign('aPages', $aPages);
//}
//
//if (Cms::$modules['news'] == 1) {
//	require_once(CMS_DIR . '/application/models/news.php');
//	$oNews = new News();
//	$aNews = $oNews->loadRss();
//	Cms::$tpl->assign('aNews', $aNews);
//}
//
//if (Cms::$modules['articles'] == 1) {
//	require_once(CMS_DIR . '/application/models/articles.php');
//	$oArticles = new Articles();
//	$aArticles = $oArticles->loadRss();
//	Cms::$tpl->assign('aArticles', $aArticles);
//}
//
//if (Cms::$modules['gallery'] == 1) {
//	require_once(CMS_DIR . '/application/models/gallery.php');
//	$oGallery = new Gallery();
//	$aGallery = $oGallery->loadRss();
//	Cms::$tpl->assign('aGallery', $aGallery);
//}
//
//Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['rss_title'] . ' - ' . Cms::$seo['title']);
//Cms::$tpl->assign('pageKeywords', Cms::$seo['meta_keywords']);
//Cms::$tpl->assign('pageDescription', Cms::$seo['meta_description']);
//Cms::$tpl->display('other/rss.tpl');
//die;
//

