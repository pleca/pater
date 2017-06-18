<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['shop'] != 1)
	die('This module is disabled!');

if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(CMS_DIR . '/application/models/shopProductsAdmin.php');
require_once(CMS_DIR . '/application/models/shopProducersAdmin.php');
require_once(MODEL_DIR . '/Category.php');
require_once(MODEL_DIR . '/Feature.php');
require_once(MODEL_DIR . '/FeatureValue.php');
require_once(MODEL_DIR . '/AllegroTemplate.php');
require_once(MODEL_DIR . '/AllegroCategory.php');
require_once(MODEL_DIR . '/AllegroSetting.php');
require_once(MODEL_DIR . '/VariationRelated.php');

	require_once(MODEL_DIR . '/Product.php');

global $oProductsAdmin, $oProducersAdmin;
global $category, $feature, $featureValue;
global $allegroTemplate, $allegroCategory, $allegroSetting;

$oProductsAdmin = new ProductsAdmin();
$category = new Category();
$feature = new Feature();
$featureValue = new FeatureValue();
$oProducersAdmin = new ProducersAdmin();
$allegroTemplate = new AllegroTemplate();
$allegroCategory = new AllegroCategory();
$allegroSetting = new AllegroSetting();
$variationRelated = new VariationRelated();

if (isset($_GET['action']) AND $_GET['action'] == 'add') {
	showAdd();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
	$result = $oProductsAdmin->addAdmin($_POST);
	
	if ($result === true or is_numeric($result)) {
		$params['info'] = $GLOBALS['LANG']['info_add'];
		showEdit($result, $params);
	} else {
		$params['error'] = $GLOBALS['LANG']['error_add'] . ' ' . $result;
		showAdd($params);
	}
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	showEdit($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'edit') { 

	$result = $oProductsAdmin->editAdmin($_POST);
	
	if ($result === true) {
		$params['info'] = $GLOBALS['LANG']['info_edit'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'] . ' ' . $result;
	}	

	showEdit($_POST['id'], $params);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'duplicate') {
	showDuplicate($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'expanded') {
	showExpanded($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'expanded') {
	
	if ($oProductsAdmin->expandedAdmin($_POST)) {
		$params['info'] = $GLOBALS['LANG']['info_edit'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}
	
	showExpanded($_POST['id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'image') {
	showImage($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'image') {

	if ($oProductsAdmin->imageAdmin($_POST, $_FILES)) {
		$params['info'] = $GLOBALS['LANG']['info_edit'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}
	
	showImage($_POST['id'], $params);
} 

elseif(isset($_GET['action']) AND $_GET['action'] == 'variation') {
	showVariations($_GET['id']);
} elseif(isset($_GET['action']) AND $_GET['action'] == 'variation_add') {
	showVariationAdd($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'variation_add') {
	
	if ($id = $oProductsAdmin->addVariation($_POST)) {
		showVariations($_POST['id'], $params);
	} else {
		showVariationAdd($_POST['id'], $params);
	}

} elseif(isset($_GET['action']) AND $_GET['action'] == 'variation_edit') {
	showVariationEdit($_GET['variation_id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'variation_edit') {

	if ($id = $oProductsAdmin->editVariation($_POST)) {
		showVariations($_POST['id'], $params);
	} else {
		showVariationEdit($_POST['variation_id'], $params);
	}

} elseif(isset($_GET['action']) AND $_GET['action'] == 'variation_duplicate') {
	showVariationDuplicate($_GET['variation_id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_delete') {    
    
    if ($_SESSION[USER_CODE]['available_actions']['variation_delete'] != 1) {
        die('No permission to this action!');
    }
    
	if ($oProductsAdmin->deleteVariationAdmin($_GET['variation_id'], $_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
		
    showVariations($_GET['id'], $params);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_photo') {
    showVariationImage($_GET['variation_id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_photo_up') {
	
	if ($oProductsAdmin->moveUpPhotoAdmin($_GET['photo_id'])) {
		$params['info'] = $GLOBALS['LANG']['info_up'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}

    showVariationImage($_GET['variation_id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_photo_down') {
	
	if ($oProductsAdmin->moveDownPhotoAdmin($_GET['photo_id'])) {
		$params['info'] = $GLOBALS['LANG']['info_down'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}

    showVariationImage($_GET['variation_id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_photo_delete') {
	
	if ($oProductsAdmin->deletePhotoAdmin($_GET['photo_id'], $_GET['product_id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}

    showVariationImage($_GET['variation_id'], $params);    
	
}elseif (isset($_GET['action']) AND $_GET['action'] == 'upPhoto') {
	
	if ($oProductsAdmin->moveUpPhotoAdmin($_GET['photo_id'])) {
		$params['info'] = $GLOBALS['LANG']['info_up'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}
	
	showImage($_GET['id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'downPhoto') {
	
	if ($oProductsAdmin->moveDownPhotoAdmin($_GET['photo_id'])) {
		$params['info'] = $GLOBALS['LANG']['info_down'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_change'];
	}

	showImage($_GET['id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'deletePhoto') {
	
	if ($oProductsAdmin->deletePhotoAdmin($_GET['photo_id'], $_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}

	showImage($_GET['id'], $params);
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {    
    
    if ($_SESSION[USER_CODE]['available_actions']['product_delete'] != 1) {
        die('No permission to this action!');
    }
    
	if ($oProductsAdmin->deleteAdmin($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
	
    $_GET['id'] = '';
	showList($params);

} elseif (isset($_GET['action']) AND $_GET['action'] == 'generate_allegro_template') { 
	generateAllegroTemplate();
	
} elseif (isset($_POST['action']) AND $_POST['action'] == 'product_note_save') {
	$id = $oProductsAdmin->productNoteSave($_POST);
	showEdit($id);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'variation_photo') {
	
	if ($oProductsAdmin->variationImageAdmin($_POST, $_FILES)) {
		$params['info'] = $GLOBALS['LANG']['info_add'];		
	} else {
		$params['error'] = $GLOBALS['LANG']['error_add'];
	}
	
    showVariationImage($_POST['variation_id'], $params);  
	
} elseif (isset($_GET['action']) AND $_GET['action'] == 'variation_related') {		
	showVariationRelated();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'variation_related_edit') {
	
	if ($variationRelated->edit()) {
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//			redirect(URL . '/admin/allegro-templates.html');
	} else {
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_edit']);
	}

	showVariationRelated();
	
} else {
    //1.
	showList();
}

function showAdd($params = []) {
	global $oProductsAdmin, $category, $oProducersAdmin;

	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);
	$producersSelect = $oProducersAdmin->loadProducersSelect();

//	$entity = array("name" => '', "category_id" => 0, "producer_id" => 0, "status_id" => 1, "type" => 1, "desc" => '');
	$entity = array("category_id" => 0, "producer_id" => 0, "status_id" => 1, "type" => 1, "desc" => '');
	$entity = isset($_POST['action']) ? $_POST : $entity;

	$data = array(
		'entity' => $entity,
		'categories' => $categories,
		'producersSelect' => $producersSelect,
		'tinyMce' => true,
		'pageTitle' => $GLOBALS['LANG']['shop_pro_add']
	);

	echo Cms::$twig->render('admin/shop/add.twig', array_merge($data, $params));	
}

function showEdit($id, $params = []) {
	global $oProductsAdmin, $category, $oProducersAdmin;

	$entity = $oProductsAdmin->loadByIdAdmin($id);

	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);
	$producersSelect = $oProducersAdmin->loadProducersSelect();

	$data = array(
		'entity' => $entity,
		'categories' => $categories,
		'producersSelect' => $producersSelect,
		'tinyMce' => true,
		'pageTitle' => $GLOBALS['LANG']['shop_pro_edit'] . ': ' . $entity['name']
	);

	echo Cms::$twig->render('admin/shop/edit.twig', array_merge($data, $params));	
}

function showExpanded($id, $params = []) {
	global $oProductsAdmin, $feature;

	$entity = $oProductsAdmin->loadByIdAdmin($id);
	$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
	$features = $feature->getAll(['locale' => $locale]);

	$data = array(
		'entity' => $entity,
		'features' => $features,
		'pageTitle' => 'Dane rozszerzone: ' . $entity['name']
	);

	echo Cms::$twig->render('admin/shop/expanded.twig', array_merge($data, $params));	
}

function showImage($id, $params = []) {
	global $oProductsAdmin;

	$entity = $oProductsAdmin->loadByIdAdmin($id);
	$photos = $oProductsAdmin->getImage($entity['id']);

	$data = array(
		'entity' => $entity,
		'photos' => $photos,
		'pageTitle' => 'Zdjęcia: ' . $entity['name']
	);

	echo Cms::$twig->render('admin/shop/image.twig', array_merge($data, $params));	
}

function showDuplicate($id, $params = []) {
}

//2.
function showList($params = []) {
	global $oProductsAdmin, $category, $oProducersAdmin;

	$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);
	
	$producersSelect = $oProducersAdmin->loadProducersSelect();
    $fullCategoriesList = [];
	
    foreach ($categories as $category) {        
        if (isset($category['name']) && isset($category['subcategories'])) {
			$fullCategoriesList[$category['id']] = $category['name'];
			
            foreach ($category['subcategories'] as $subcategory) {
                $fullCategoriesList[$subcategory['id']] = $category['name'] . ' -> ' . $subcategory['name'];                
            }            
        } else {
            $fullCategoriesList[$category['id']] = isset($category['name']) ? $category['name'] : '';
        }                
    }
    

	$data = array(
		'categories' => $categories,
		'fullCategoriesList' => $fullCategoriesList,
		'producersSelect' => $producersSelect,
		'pageTitle' => $GLOBALS['LANG']['shop_pro_title']
	);

	echo Cms::$twig->render('admin/shop/list.twig', array_merge($data, $params));
}


function showVariationAdd($id, $params = []) {
	global $oProductsAdmin, $featureValue;

	$product = $oProductsAdmin->loadByIdAdmin($id);
//	$taxes = $oProductsAdmin->loadTax();
//dump($taxes);	

	$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
	$taxes = $taxRepository->findBy([],['position' => 'ASC']);

//	$featureValues = $featureValue->getAll(['locale' => CMS::$defaultLocale]);
	$featureValues = $featureValue->getAll()[CMS::$defaultLocale];
//	dump($product);
//	dump($features);
//	dump($featureValues);
	
	require_once(MODEL_DIR . '/UnitTransportGroup.php');
	$unitTransportGroup = new UnitTransportGroup();
	$unitTransportGroups = $unitTransportGroup->getAll();
	
	$entity = array("tax_id" => 0, "price_rrp" => '', "price_purchase" => '', "price" => '', "promotion" => 0, "bestseller" => 0, "recommended" => 0, "price_promotion" => '', 
		"date_promotion" => '', "weight" => '', "qty" => '', "sku" => '', "ean" => '', "feature1_value_id" => '', "feature2_value_id" => '', "feature3_value_id" => '', "feature1_value" => '', "feature2_value" => '', "feature3_value" => '');
	$entity = isset($_POST['action']) ? $_POST : $entity;

	$data = array(
		'product' => $product,
		'entity' => $entity,
		'taxes' => $taxes,
		'featureValues' => $featureValues,
		'unitTransportGroups' => $unitTransportGroups,
		'datepicker' => true,
		'pageTitle' => 'Produkt ID: ' . $product['id'] . ' | Dodaj wariacje'
	);

	echo Cms::$twig->render('admin/shop/variation-add.twig', array_merge($data, $params));		
}

function showVariationEdit($variation_id, $params = []) {
	global $oProductsAdmin, $featureValue;

	$entity = $oProductsAdmin->loadVariationById($variation_id);	// variation
	$product = $oProductsAdmin->loadByIdAdmin($entity['product_id']);	//aItem

	$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
	$taxes = $taxRepository->findBy([],['position' => 'ASC']);	

	$featureValues = $featureValue->getAll()[CMS::$defaultLocale];

	require_once(MODEL_DIR . '/UnitTransportGroup.php');
	$unitTransportGroup = new UnitTransportGroup();
	$unitTransportGroups = $unitTransportGroup->getAll();

	$data = array(
		'product' => $product,
		'entity' => $entity,
		'taxes' => $taxes,
		'featureValues' => $featureValues,
		'unitTransportGroups' => $unitTransportGroups,
		'datepicker' => true,
		'pageTitle' => 'Produkt ID: ' . $product['name'] . ' | Edycja wariacji ID: ' . $entity['id2']
	);

	echo Cms::$twig->render('admin/shop/variation-edit.twig', array_merge($data, $params));	
}

function showVariationDuplicate($variation_id, $params = []) {
	global $oProductsAdmin, $featureValue;

	$entity = $oProductsAdmin->loadVariationById($variation_id);
	$product = $oProductsAdmin->loadByIdAdmin($entity['product_id']);
//	$taxes = $oProductsAdmin->loadTax();
	$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
	$taxes = $taxRepository->findBy([],['position' => 'ASC']);
	$featureValues = $featureValue->getAll(['locale' => CMS::$defaultLocale]);

	require_once(MODEL_DIR . '/UnitTransportGroup.php');
	$unitTransportGroup = new UnitTransportGroup();
	$unitTransportGroups = $unitTransportGroup->getAll();
	
	$data = array(
		'product' => $product,
		'entity' => $entity,
		'taxes' => $taxes,
		'featureValues' => $featureValues,
		'unitTransportGroups' => $unitTransportGroups,
		'datepicker' => true,
		'pageTitle' => 'Produkt ID: ' . $product['name'] . ' | Duplikat wariacji ID: ' . $entity['id2']
	);

	echo Cms::$twig->render('admin/shop/variation-add.twig', array_merge($data, $params));		
}

function showVariations($id, $params = []) {
	global $oProductsAdmin, $featureValue, $allegroTemplate, $allegroCategory;

	$item = $oProductsAdmin->loadByIdAdmin($id);
	$entities = $oProductsAdmin->loadVariationsByProductId($item['id']);	// variations
//	$taxes = $oProductsAdmin->loadTax();
//	$featureValues = $featureValue->getAll(['locale' => CMS::$defaultLocale]);
	$featureValues = $featureValue->getAll()[CMS::$defaultLocale];

	$enabledAddingVariation = true;
	
	if ($entities[0]['id2'] && $item['type'] == 2) {
		$enabledAddingVariation = false;
	}
    
//    $allegroCategories = $allegroCategory->getAll();
//    dump($allegroCategories);
	
    $allegroTemplates = $allegroTemplate->getAll();
//    dump($allegroTemplates);    
    
	$data = array(
		'item' => $item,
		'entities' => $entities,
//		'taxes' => $taxes,
		'featureValues' => $featureValues,
		'enabledAddingVariation' => $enabledAddingVariation,
		'allegroTemplates' => $allegroTemplates,
		'datepicker' => true,
		'pageTitle' => 'Produkt ID: ' . $item['name'] . ' | Wariacje'
	);

	echo Cms::$twig->render('admin/shop/variation.twig', array_merge($data, $params));	
}

function showVariationImage($variation_id = 0, $params = []) {
	global $oProductsAdmin;
	$entity = $oProductsAdmin->loadVariationById($variation_id);    
    $product = $oProductsAdmin->loadByIdAdmin($entity['product_id']);
	$images = $oProductsAdmin->getImage($product['id'], $variation_id);

	$data = array(
		'product' => $product,
		'entity' => $entity,
		'images' => $images,
		'fancybox' => true,
		'pageTitle' => $GLOBALS['LANG']['ml_shop_products'] . ' | ' . $GLOBALS['LANG']['btn_variations'] . ' | ' . $product['name'] . ' | ' . $GLOBALS['LANG']['btn_photo']
	);

	echo Cms::$twig->render('admin/shop/variation-photo.twig', array_merge($data, $params));		
}

function generateAllegroTemplate() {
	global $oProductsAdmin, $featureValue, $allegroTemplate, $allegroSetting, $allegroCategory;

	$template = $allegroTemplate->getById($_GET['template_id'])[0];

	$user = $allegroSetting->getByName('user_id');
	$userId = $user['value'];
	$allegroCategories = $allegroCategory->getAll();
	
	$allegroCategoriesHtml = '';
	
	if ($allegroCategories) {
		$allegroCategoriesHtml .= '<ul class="categories">'; 
		foreach ($allegroCategories as $category) {
			$allegroCategoriesHtml .= '<li>';
			$allegroCategoriesHtml .= '<a href="http://allegro.pl/Shop.php/Show?id=' . $userId . '&category=' . $category['id'] . '" title="' . $category['name'] . '">' . $category['name'] .'</a></li>';
		}

		$allegroCategoriesHtml .= '</ul>'; 			
	}

	$variation_id = $_GET['variation_id'];
	$entity = $oProductsAdmin->loadVariationById($variation_id);	// variation
	$product = $oProductsAdmin->loadByIdAdmin($entity['product_id']);	//aItem

	$productName = $product['trans'][CMS::$defaultLocale]['name'];
	$productContent = $product['trans'][CMS::$defaultLocale]['content'];	
	$productPhoto = isset($entity['photos'][0]) ? SERVER_URL . $entity['photos'][0]['photo']['normal'] : '';

	$featureValues = $featureValue->getAll()[CMS::$defaultLocale];
	
	$productFeatures = '<br /><small>';
	
	for ($i=1; $i<=3; $i++) {
		if ($product['feature' . $i . '_id']) {
			$productFeatures .=  $product['feature' . $i . '_name'] . ': ' . $entity['feature' . $i . '_value'] .'<br />';				
		}
	}
	
	$productFeatures .= ' </small>';

	$productName = $productName . ' ' . $productFeatures;

    $variationRelated = new VariationRelated();
    $related = $variationRelated->getBy(['variation_id' => $_REQUEST['variation_id']]);

	$variationsRelatedHtml = '';
	
	$prod = new Product();
	$params = array(
		'locale' => Cms::$defaultLocale
	);
	
	$products = $prod->getAll($params, ['name', 'tag1', 'tag2', 'tag3']);
	$products = getArrayByKey($products, 'id');

	if ($related) {
		$variationsRelatedHtml .= '<div class="variations-related">';

		foreach ($related as $row) {
			$variation = $oProductsAdmin->loadVariationById($row['variation_related_id']);
			$name = $products[$variation['product_id']]['name'];
			$tags = [];
			$tags[] = $products[$variation['product_id']]['tag1'];
			$tags[] = $products[$variation['product_id']]['tag2'];
			$tags[] = $products[$variation['product_id']]['tag3'];
			$tags = implode(',', array_filter($tags));
			
			$photo = isset($variation['photos'][0]) ? SERVER_URL . $variation['photos'][0]['photo']['middle'] : '';

			$variationsRelatedHtml .= '<a class="variation-related" href="http://allegro.pl/shop.php/Show?string=' . $tags .'&search_scope=shopItems-' . $userId .'" title="' . $name .'">';
			$variationsRelatedHtml .= '<div class="related-photo"><img width="150" src="'. $photo .'" alt="sweet world" /></div>';
			$variationsRelatedHtml .= '<div class="related-name">' . $name . '</div>';
			$variationsRelatedHtml .= '<div class="related-price">' . Cms::$conf['currency_left'] . $variation['price_gross'] . Cms::$conf['currency_right'] . '</div>';
			$variationsRelatedHtml .= '</a>';			
		}
	
		$variationsRelatedHtml .= '</div>';
		$variationsRelatedHtml .= '<div style="line-height:40px;text-align:center;font-size:19px;clear:both;">';
		$variationsRelatedHtml .= '<p></p>';
	}
	
	$search = array('#USER_ID#', '#CATEGORIES#', '#PRODUCT_NAME#', '#PRODUCT_DESCRIPTION#', '#PHOTO#', '#VARIATIONS_RELATED#');
	$replace = array($userId, $allegroCategoriesHtml, $productName, $productContent, $productPhoto, $variationsRelatedHtml);	
	$content = str_replace($search, $replace, $template['content']); 

	$data = array(
		'content' => $content,
		'product' => $product,
		'datepicker' => true,
		'pageTitle' => 'Produkt ID: ' . $product['name'] . ' | Edycja wariacji ID: ' . $entity['id2']
	);

	echo Cms::$twig->render('admin/shop/generate-allegro-template.twig', $data);
}

function showVariationRelated() {
	global $feature, $featureValue, $oProductsAdmin;

	$product = new Product();
	$params = array(
		'locale' => Cms::$defaultLocale
	);
	
	$products = $product->getAll($params, ['name', 'feature1_id', 'feature2_id', 'feature3_id']);
	$products = getArrayByKey($products, 'id');

	if ($products) {
		foreach ($products as &$product) {
			if (isset($product['variations'])) {
				foreach ($product['variations'] as $key => $var) {
					if ($var['id2'] == $_REQUEST['variation_id']) {
						unset($product['variations'][$key]);
						break 2;
					}
				}
			}			
		}
	}
	
	$features = $feature->getAll($params);
	$features = getArrayByKey($features, 'id');

	$featureValues = $featureValue->getAll($params);
	$featureValues = getArrayByKey($featureValues, 'id');

	$entity = $oProductsAdmin->loadVariationById($_REQUEST['variation_id']);	// variation
	$product = $oProductsAdmin->loadByIdAdmin($_REQUEST['product_id']);	//aItem

    $variationRelated = new VariationRelated();
    $related = $variationRelated->getBy(['variation_id' => $_REQUEST['variation_id']]);
    if ($related) {
        $related = getArrayByKey($related, 'variation_related_id');
        $related = array_keys($related);        
    }

	$data = array(
        'related' => $related,
		'entity' => $entity,
		'product' => $product,
		'products' => $products,
		'features' => $features,
		'featureValues' => $featureValues,
		'pageTitle' => 'Produkt ID: ' . $product['name'] . ' | Edycja wariacji ID: ' . $entity['id2']
	);

	echo Cms::$twig->render('admin/shop/variation-related.twig', array_merge($data, $params));	
}