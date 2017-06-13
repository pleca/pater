<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$log.= '<strong>Generowanie sitemap</strong><br />';

$q = "SELECT `id`, `name`, `code`, `default` FROM `" . DB_PREFIX . "languages` ORDER BY `default` DESC, `id` ASC ";
$array = $oDb->getAll($q);
$items = array();
foreach ($array as $v) {
	$aLangs[$v['id']] = $v;
}
$q = "SELECT `name`, `active` FROM `" . DB_PREFIX . "modules` ORDER BY `id` ASC ";
$array = $oDb->getAll($q);
$items = array();
foreach ($array as $v) {
	$aModules[$v['name']] = $v['active'];
}

foreach ($aLangs as $v) {
	$xmlTxt = '<?xml version="1.0" encoding="UTF-8"?>
<urlset
xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
      http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
';
	if ($v['default'] != 1)
		$lang = '/' . $v['code'];
	else
		$lang = '';
	$xmlTxt.= loadMain($lang, '1.0');
	if ($aModules['menu'] == 1)
		$xmlTxt.= loadMenu($lang, $v['id'], '0.9');
	if ($aModules['pages'] == 1)
		$xmlTxt.= loadXml($lang, $v['id'], 'pages', '', '0.8');   // table, url, priority
	if ($aModules['news'] == 1)
		$xmlTxt.= loadXml($lang, $v['id'], 'news', 'aktualnosci', '0.7');
	if ($aModules['gallery'] == 1)
		$xmlTxt.= loadXml($lang, $v['id'], 'gallery', 'galeria', '0.6');
	if (Cms::modules()['shop'] == 1)
		$xmlTxt.= loadProducts($lang, $v['id'], '0.9');
	$xmlTxt.= '
</urlset>';

	$file = fopen(CMS_DIR . '/public/sitemap/' . $v['code'] . '.xml', 'w');
	fwrite($file, $xmlTxt);
	$log.= 'sitemap - ' . $v['code'] . '<br />';
	fclose($file);
}

function loadMain($lang, $priority = '0.5') {
	$lastmod = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
	$changefreq = 'daily';
	$items = '
<url>
<loc>' . SERVER_URL . CMS_URL . $lang . '/</loc>
<priority>' . $priority . '</priority>
<lastmod>' . $lastmod . '</lastmod>
<changefreq>' . $changefreq . '</changefreq>
</url>';
	return $items;
}

function loadMenu($lang, $lang_id, $priority = '0.5') {
	global $oDb;

	$lastmod = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
	$changefreq = 'daily';
	$q = "SELECT d.url, a.type FROM `" . DB_PREFIX . "menu` a LEFT JOIN `" . DB_PREFIX . "menu_desc` d ON a.id=d.parent_id ";
	$q.= "WHERE a.type='module' AND d.lang_id='" . $lang_id . "' GROUP BY d.url ORDER BY d.name ASC";
	$array = $oDb->getAll($q);
	$items = '';
	foreach ($array as $v) {
		$url = SERVER_URL . CMS_URL . $lang . '/' . $v['url'] . '.html';
		$items.= '
<url>
<loc>' . $url . '</loc>
<priority>' . $priority . '</priority>
<lastmod>' . $lastmod . '</lastmod>
<changefreq>' . $changefreq . '</changefreq>
</url>';
	}
	return $items;
}

function loadXml($lang, $lang_id, $table, $link, $priority = '0.5') {
	global $oDb;

	$lastmod = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
	$changefreq = 'daily';
	$q = "SELECT d.title_url FROM `" . DB_PREFIX . $table . "` a LEFT JOIN `" . DB_PREFIX . $table . "_desc` d ON a.id=d.parent_id ";
	$q.= "WHERE d.lang_id='" . $lang_id . "' ORDER BY d.title ASC";
	$array = $oDb->getAll($q);
	$items = '';
	foreach ($array as $v) {
		if ($table == 'pages')
			$url = SERVER_URL . CMS_URL . $lang . '/' . $v['title_url'] . '.html';
		else
			$url = SERVER_URL . CMS_URL . $lang . '/' . $link . '/' . $v['title_url'] . '.html';
		$items.= '
<url>
<loc>' . $url . '</loc>
<priority>' . $priority . '</priority>
<lastmod>' . $lastmod . '</lastmod>
<changefreq>' . $changefreq . '</changefreq>
</url>';
	}
	return $items;
}

function loadProducts($lang, $lang_id, $priority = '0.5') {
	global $oDb;

	$lastmod = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
	$changefreq = 'daily';
	$q = "SELECT p.*, d.title_url, d.desc_short, c1.name_url as category_url, ";
	$q.= "(SELECT `name_url` FROM `" . DB_PREFIX . "shop_categories_desc` WHERE parent_id=c.parent_id LIMIT 1) as parent_url ";
	$q.= "FROM `" . DB_PREFIX . "shop_products` p LEFT JOIN `" . DB_PREFIX . "shop_products_desc` d ON p.id=d.parent_id ";
	$q.= "LEFT JOIN `" . DB_PREFIX . "shop_categories` c ON p.category_id=c.id ";
	$q.= "LEFT JOIN `" . DB_PREFIX . "shop_categories_desc` c1 ON c1.parent_id=c.id ";
	$q.= "WHERE d.lang_id='" . $lang_id . "' AND c1.lang_id='" . $lang_id . "' AND p.active='1' ";
	$q.= "GROUP BY p.id ORDER BY d.title ASC ";
	$array = $oDb->getAll($q);
	$items = '';
	foreach ($array as $v) {
		if ($v['parent_url'])
			$url = SERVER_URL . CMS_URL . $lang . '/' . $v['parent_url'] . '/' . $v['category_url'] . '/' . $v['title_url'] . '.html';
		else
			$url = SERVER_URL . CMS_URL . $lang . '/' . $v['category_url'] . '/' . $v['title_url'] . '.html';
		$items.= '
<url>
<loc>' . $url . '</loc>
<priority>' . $priority . '</priority>
<lastmod>' . $lastmod . '</lastmod>
<changefreq>' . $changefreq . '</changefreq>
</url>';
	}
	return $items;
}
