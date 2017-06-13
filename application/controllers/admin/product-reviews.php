<?php

/* 2015-10-14 | 4me.CMS 15.3 */
require_once(ENTITY_DIR . '/ProductReview.php');
use Application\Entity\ProductReview;

check_permission('product_reviews'); // sprawdzamy dostep dla podanego modulu

//check_level(1);

class ProductReviewsController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
//		$this->entity = new Template();
	}

	public function init($params = '') {
		$this->setParams($params);
		$this->setAccess();
		$this->setModule();
        $this->run();
	}
    
    public function run() {
		$action = $this->getParam('action') ? $this->getParam('action') : 'list';
		$action .= 'Action';
		$this->$action();        
    }

	public function __call($method = '', $args = '') {
		error_404();
	}

	public function getParam($name) {
		return isset($this->params[$name]) ? $this->params[$name] : 0;
	}

	public function setParams($params = []) {        
        $params = array_merge($params, $_POST);
        $params = array_merge($params, $_GET);

		foreach ($params as $key => $value) {
			switch ($key) {
                case (string) 0:                
                case (string) 1:
					$this->params['controller'] = $value;
					break;
				case 'action':
					$this->params['action'] = $value;
					break;
				case 'id':
					$this->params['id'] = $value;
					break;
			}
		}
		
	}

	public function setAccess() {
		$this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}
  
	function listAction() {

		$productReviewRepository = CMS::$entityManager->getRepository('Application\Entity\ProductReview');
		
        if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
			$_GET['page'] = 1; 	
		}
        
        $limit = 10;
        $offset = ($_GET['page'] - 1) * $limit;        
        
        $entities = $productReviewRepository->findBy([],['active' => 'ASC', 'datePublished' => 'DESC'], $limit, $offset);
        $productReview = new \Application\Entity\ProductReview;
        $pages = $productReview->getPages($limit);
        
        $product = new Product();
        $products = $product->getAll(['locale' => Cms::$defaultLocale], ['name']);        
        $products = getArrayByKey($products, 'id'); 

		$data = array(
			'entities'	=> $entities,
			'products'	=> $products,
			'pageTitle' => $GLOBALS['LANG']['reviews'],
            'pages' => $pages
		);

		echo Cms::$twig->render('admin/product_reviews/list.twig', $data);
	}    
    
    function deleteAction() 
    {
		$productReviewRepository = CMS::$entityManager->getRepository('Application\Entity\ProductReview');
		$productReview = $productReviewRepository->find($_GET['id']); 
        CMS::$entityManager->remove($productReview);
        CMS::$entityManager->flush();
        
        Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
        redirect(URL . '/admin/product-reviews');
    }
    
    function acceptanceAction() 
    {
		$productReviewRepository = CMS::$entityManager->getRepository('Application\Entity\ProductReview');
		$productReview = $productReviewRepository->find($_GET['id']); 
        $productReview->setActive($_GET['value']);
        
		CMS::$entityManager->persist($productReview);
		CMS::$entityManager->flush($productReview);

		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		redirect(URL . '/admin/product-reviews');	        
    }
    
    
    
    
    
    
//	function colorsAction() {
//		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
//		$entity = $entityRepository->findOneBy(['id' => $_GET['template_id']]);
//				
//		if (!$entity->getActive()) {
//			redirect(URL . '/admin/templates');	
//		}
//
//		$data = array(
//			'entity'	=>	$entity,
//		);
//
//		echo Cms::$twig->render('admin/templates/colors.twig', $data);
//	}
//	
//	function saveAction() {
//		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Color');
//
//		foreach ($_POST['colors'] as $name => $value) {
//			$color = $entityRepository->findOneBy(['name' => $name, 'isDefault' => 0, 'template' => $_POST['template_id']]);
//			$color->setValue($value);
//			CMS::$entityManager->persist($color);
//		}
//		
//		CMS::$entityManager->flush();
//		$this->generateColorsCss($_POST['template_id'], $_POST['colors']);
//        
//		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//		redirect(URL . '/admin/templates?template_id=' . $_POST['template_id'] . '&action=colors');
//	}
//    
//
//	function editAction() {
//		if (!$_SESSION[USER_CODE]['available_actions']['template_change']) {
//			redirect(URL . '/admin/templates');
//		}
//				
//		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
//		$entities = $entityRepository->findAll();
//		
//		foreach ($entities as $entity) {
//			$entity->setActive(false);
//		}
//		
//		CMS::$entityManager->flush();		
//				
//		$entity =  $entityRepository->find($_REQUEST['id']);
//		$entity->setActive(true);
//		CMS::$entityManager->persist($entity);
//		CMS::$entityManager->flush($entity);
//		
//		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
//		redirect(URL . '/admin/templates');		
//	}	
	

	
	
}

$controller = new ProductReviewsController();
$controller->init($params);