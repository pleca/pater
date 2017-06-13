<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['shop'] == 1) {
	require_once(CMS_DIR . '/application/models/shopProducts.php');
	$oProducts = new Products();
	$aProducts = $oProducts->loadProducts();
	$params['aProducts'] = $aProducts;
}

if (Cms::$modules['news'] == 1) {
	require_once(CMS_DIR . '/application/models/news.php');
	$oNews = new News();
	$aNews = $oNews->loadSiteMap();
	$params['aNews'] = $aNews;
}

if (Cms::$modules['articles'] == 1) {
	require_once(CMS_DIR . '/application/models/articles.php');
	$oArticles = new Articles();
	$aArticles = $oArticles->loadSiteMap();
	$params['aArticles'] = $aArticles;
}

if (Cms::$modules['gallery'] == 1) {
	require_once(CMS_DIR . '/application/models/gallery.php');
	$oGallery = new Gallery();
	$aGallery = $oGallery->loadSiteMap();
	$params['aGallery'] = $aGallery;
}

if (Cms::$modules['menu'] == 1) {
	$aMenu = $oMenu->loadSiteMap();
	$params['aMenu'] = $aMenu;
}

$data = array(
    'pageTitle' => $GLOBALS['LANG']['sitemap_title'] . ' - ' . Cms::$seo['title'],
    'pageKeywords' => Cms::$seo['meta_keywords'],
    'pageDescription' => Cms::$seo['meta_description']			
);

echo Cms::$twig->render('templates/other/site-map.twig', array_merge($data, $params));


