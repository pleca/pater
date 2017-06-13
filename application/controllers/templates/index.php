<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/Slider.php');

$data = [];

switch ($controller) {
	case 'switch-language':
		processRedirect($params);
		break;
	case '':
		mainPage();
		break;

	default:		
		showPage($controller, $action);
		break;
}

function processRedirect($params = []) {
	$lastUrl = $_SERVER['HTTP_REFERER'];		

	$url = str_replace('.html', '', parse_url($lastUrl, PHP_URL_PATH));
	$urlParams = explode('/', $url);
	
	$urlParams = array_filter($urlParams);
	$urlParams = array_values($urlParams);
	
	$prevLocale = null;
	
	if (isset($urlParams[0]) && in_array($urlParams[0], Cms::$locales)) {
		$prevLocale = $urlParams[0];		
		array_shift($urlParams);
	}
	
	$controller = isset($urlParams[0]) ? $urlParams[0] : false;

	$slug = isset($urlParams[1]) ? $urlParams[1] : false;
	$subSlug = isset($urlParams[2]) ? $urlParams[2] : false;
	$productNameSlug = isset($urlParams[3]) ? $urlParams[3] : false;

	switch($controller) {
		case 'product':
		case 'shop':
			if (!$slug) {
				
				if (Cms::$session->get('locale') != Cms::$defaultLocale) {
					$lastUrl = URL . '/' . $controller . '.html';
				} else {
					if ($prevLocale) {
						$lastUrl = str_replace('/' . $prevLocale, '', $lastUrl);
					}
				}
				
				redirect($lastUrl);
			}
			

			$category = new Category();
			$product = new Products();

			$locale = $prevLocale ? $prevLocale : Cms::$defaultLocale;
			$localeSlug = $category->getCurrentLocaleSlug($slug, $locale);
			$localeSubSlug = $category->getCurrentLocaleSlug($subSlug, $locale);
			$localeProductNameSlug = $product->getCurrentLocaleSlug($productNameSlug, $locale);

			$newUrl = '';
					
			if ($prevLocale) {
				$lastUrl = str_replace_first('/' . $prevLocale, '', $lastUrl);
			}
			
			if ($localeSlug) {
				$newUrl = str_replace_first($slug, $localeSlug, $lastUrl);
			}

			if ($localeSlug && $localeSubSlug) {
				$newUrl = str_replace_first($subSlug, $localeSubSlug, $newUrl);
			}
			
			if ($localeProductNameSlug) {
				$newUrl = str_replace_first($productNameSlug, $localeProductNameSlug, $newUrl);
			}

			if (Cms::$session->get('locale') != Cms::$defaultLocale) {
				$newUrl = str_replace(SERVER_URL, URL, $newUrl);
			} else {
				if ($prevLocale) {
					$newUrl = str_replace('/' . $prevLocale, '', $newUrl);
				}
			}

			if (!$newUrl) {
				redirect(URL);
			}

			redirect($newUrl);
			break;
		default:

			if ($prevLocale) {
				
				if (Cms::$session->get('locale') != Cms::$defaultLocale) {
					$lastUrl = str_replace($prevLocale, Cms::$session->get('locale'), $lastUrl);					
				} else {
					$lastUrl = str_replace('/' . $prevLocale, '', $lastUrl);					
				}				
			} else {
				
                if (Cms::$session->get('locale') != Cms::$defaultLocale && $controller != '') {
                    $lastUrl = URL . '/' . $controller . '.html';
                } else {
                    $lastUrl = $lastUrl . Cms::$session->get('locale');
                }			
			}

			redirect($lastUrl);
			break;
	}
}

function showPage($controller, $action = '') {
	$page = new Page();

	$data = [];	

    if (in_array($controller, Cms::$locales) && !$action) {
        $controller = 'mainPage';
    }
    
	if ($entity = $page->getBySlug($controller)) { // jesli istnieje podstrona o takim url		
		if ($entity['gallery_id'] > 0 AND Cms::$modules['gallery'] == 1) {	// galeria dla podstrony
			require_once(MODEL_DIR . '/gallery.php');
			$oGallery = new Gallery($oCore);
			$aGallery = $oGallery->loadById($entity['gallery_id']);
			$aPhotos = $oGallery->loadPhotos($aGallery['id']);

			$data['aGallery'] = $aGallery;
			$data['aPhotos'] = $aPhotos;
		}
		
		$data['entity'] = $entity;
		$data['pageTitle'] = $entity['seo_title'] ? $entity['seo_title'] : $entity['title'];
		$data['pageDescription'] = $entity['content_short'] ? $entity['content_short'] : Cms::$seo['meta_description'];

		echo Cms::$twig->render('templates/pages/show.twig', $data);
		
	} elseif ($slug = $page->getCurrentLocaleSlug($controller)) {
		redirect(URL . '/' . $slug . '.html');
	} elseif ($controller != 'index' && $controller != 'mainPage') {
		error_404();
		die;
	} else {
		mainPage();
	}
}

function mainPage($data = []) {
	$Product = new Products();
	$slider = new Slider();

	if (Cms::$modules['shop'] == 1) {
		$limit = 10;
		$limit = isset(Cms::$conf['main_page_limit']) ? Cms::$conf['main_page_limit'] : $limit;

		$params = array(
			'limit' => $limit,
			'start' => 0,
			'resultType'	=> 'list',
			'mainPage'	=> 1,
			'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
		);	

		$entities = $Product->getBy($params);
		$data['products'] = $entities;
	}
	
	if (Cms::$modules['slider'] == 1) {
        $sliderHasPhotos = false;        
		$items = $slider->getAll(['locale' => Cms::$session->get('locale')]);
		$numSliders = 0;		
		
		if ($items) {
			foreach ($items as $k => &$item) {
				$item['src'] = $slider->getSrc($item['file']);
				
				if ($item['active'] && $item['src'] && $item['file']) {
					$numSliders++;
				}
			}
        }
		
		$data['slider'] = $items;
		$data['numSliders'] = $numSliders;		
	}
	
	$seoAccordion = filterArrayKeyByPattern(CMS::$seo, 'accordion');
	Cms::$twig->addGlobal('seoAccordion', $seoAccordion);	

	echo Cms::$twig->render('templates/main.twig', $data);
}








function mainPage2($data = []) {
	$Product = new Products();
	$Slider = new SliderModel();

	if (Cms::$modules['shop'] == 1) {
		$limit = 10;
		$filtr = [];
        
		$filtr['limit'] = isset(Cms::$conf['main_page_limit']) ? Cms::$conf['main_page_limit'] : $limit;
		$filtr['start'] = 0;
		$filtr['type'] = 'list';	// list - lista produktow, count - ilosc wszystkich wynikow
		$filtr['mainPage'] = 1;
		$filtr['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';	// sotowanie: name_asc, name_desc, price_asc, price_desc, date_asc, date_desc
		$aProducts = $Product->getAll($filtr);
		
		$data['products'] = $aProducts;
	}
	if (Cms::$modules['slider'] == 1) {
        $sliderHasPhotos = true;
        
		if ($items = $Slider->getAll()) {
			foreach ($items as $k => &$v) {
				if($v['active'] != 1) {
					unset($items[$k]);
				}
				$v['src'] = $Slider->getSrc($v['photo']);
                
                if (!$v['src']) {
                    $sliderHasPhotos = false;
                    break;
                }
			}
        } else {
            $sliderHasPhotos = false;
        }
        
		$data['slider'] = $items;
		$data['sliderHasPhotos'] = $sliderHasPhotos;
		
	}
	
	$data['pageTitle'] = $GLOBALS['LANG']['module_home'];
	
	echo Cms::$twig->render('templates/main.twig', $data);
}