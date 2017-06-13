<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die;

require_once(ENTITY_DIR . '/ProductReview.php');
require_once(SYS_DIR . '/core/Cms.php');
use Application\Entity\ProductReview;

global $oProducts, $oProducers, $featureValue;

$oProducts = new Products();
$category = new Category();
$oProducers = new Producers();
$featureValue = new FeatureValue();

$url1 = isset($params[1]) ? $params[1] : '';
$url2 = isset($params[2]) ? $params[2] : '';
$url3 = isset($params[3]) ? $params[3] : '';

if ($mainCategory = $category->findBySlug($url1)) {

	if ($entity = $oProducts->findBy(['category_id' => $mainCategory['id'], 'slug' => $url2])) {   // jesli istnieje produkt: kategoria glowna, url    
		showProduct($entity, $mainCategory);	

	} elseif ($subCategory = $category->findBySlug($url2, $mainCategory)) {

		if ($entity = $oProducts->findBy(['category_id' => $subCategory['id'], 'slug' => $url3])) {   // jesli istnieje produkt: podkategoria, url									
			showProduct($entity, $subCategory);			            
        } else {
            error_404();
        }		
	} else {
		error_404();
	}

} else {
	error_404();
}

function showProduct($entity, $category = '') 
{
	global $oProducts, $oProducers;
    
    $productReview = null;
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'addReview':
                $productReview = new ProductReview();
                $productReview->setAuthor($_SESSION[CUSTOMER_CODE]['first_name']. ' ' . $_SESSION[CUSTOMER_CODE]['last_name']);
                $productReview->setCustomerId($_SESSION[CUSTOMER_CODE]['id']);
                $productReview->setProductId($entity['id']);
                $productReview->setReviewValue($_POST['reviewValue']);
                $productReview->setCommentTitle($_POST['commentTitle']);
                $productReview->setComment($_POST['comment']);
                
                if ($productReview->validate()) {
                    CMS::$entityManager->persist($productReview);
                    CMS::$entityManager->flush();	
                    Cms::getFlashBag()->add('info', $GLOBALS['LANG']['review_added']);		                    
                    $productReview = null;
                }

                break;

            default:
                break;
        }
    }

	$params = array(
		'product_id' => $entity['id']
	);    

	$entity['variations'] = $oProducts->getVariationsBy($params);
	$entity['default_variation'] = $oProducts->getDefaultVariation($entity['variations']);  
	$entity['producer'] = $oProducers->getById($entity['producer_id']);
	$alsoBought = $oProducts->loadAlsoBought($entity['id']);

	$pageTitle = $entity['seo_title'] ? $entity['seo_title'] : $entity['name'];
	$pageDescription = $entity['content_short'] ? $entity['content_short'] : Cms::$seo['meta_description'];

    $productReviewRepository = CMS::$entityManager->getRepository('Application\Entity\ProductReview');
    $productReviews = $productReviewRepository->findBy(['productId' => $entity['id'], 'active' => 1]);
    
    $sumReviewRatingValues = 0;
    if ($productReviews) {        
        foreach ($productReviews as $review) {
            $sumReviewRatingValues += $review->getReviewValue();
        }
    }
    
	$data = array(
		'entity' => $entity,
		'alsoBought' => $alsoBought,
		'category' => $category['name'],
		'galleryScript' => true,
		'pageTitle' => $pageTitle,
		'pageKeywords' => Cms::$seo['meta_keywords'],
		'pageDescription' => $pageDescription,
        'productReview' => $productReview,
        'productReviews' => $productReviews,
        'sumReviewRatingValues' => $sumReviewRatingValues
	);

	echo Cms::$twig->render('templates/product/show.twig', array_merge($data, $params));	
}

function addFormAction($productReview = null) 
{		

    $data = array(
        'entity' => $productReview,
//        'pageTitle' => 'Crons',
    );

    echo Cms::$twig->render('admin/crons/add.twig', $data);
}