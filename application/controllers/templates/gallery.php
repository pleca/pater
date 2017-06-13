<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['gallery'] != 1)
	die;

require_once(CMS_DIR . '/application/models/gallery.php');
$oGallery = new Gallery();

$url = isset($params[1]) ? $params[1] : '';
if ($entity = $oGallery->load($url)) {

	$photos = $oGallery->loadPhotos($entity['id']);
	
    $data = array(
        'entity' => $entity,
        'photos' => $photos,
        'galleryScript' => true,
        'pageTitle' => $entity['title'],
        'pageKeywords' => Cms::$seo['meta_keywords'],
        'pageDescription' => $entity['desc_short'],
    );	
	
	echo Cms::$twig->render('templates/gallery/show.twig', $data); 	
} else {
	$limit = 5;
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;
	$entities = $oGallery->loadArticles($limitStart, $limit);
	$pages = $oGallery->getPages($limit);

    $data = array(
        'entities' => $entities,
        'pages' => $pages,
        'page' => $_GET['page'],
        'pageTitle' => $GLOBALS['LANG']['gallery_title'] . ' - ' . Cms::$seo['title'],
        'pageKeywords' => Cms::$seo['meta_keywords'],
        'pageDescription' => Cms::$seo['meta_description'],
    );	
	
	echo Cms::$twig->render('templates/gallery/list.twig', $data); 		
}


