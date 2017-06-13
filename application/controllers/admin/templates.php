<?php

/* 2015-10-14 | 4me.CMS 15.3 */
require_once(ENTITY_DIR . '/Template.php');
//use Application\Entity\Template;
//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

//check_level(1);

class TemplatesController {
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
  
	function colorsAction() {
		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
		$entity = $entityRepository->findOneBy(['id' => $_GET['template_id']]);
				
		if (!$entity->getActive()) {
			redirect(URL . '/admin/templates');	
		}

		$data = array(
			'entity'	=>	$entity,
		);

		echo Cms::$twig->render('admin/templates/colors.twig', $data);
	}
	
	function saveAction() {
		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Color');

		foreach ($_POST['colors'] as $name => $value) {
			$color = $entityRepository->findOneBy(['name' => $name, 'isDefault' => 0, 'template' => $_POST['template_id']]);
			$color->setValue($value);
			CMS::$entityManager->persist($color);
		}
		
		CMS::$entityManager->flush();
		$this->generateColorsCss($_POST['template_id'], $_POST['colors']);
        
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		redirect(URL . '/admin/templates?template_id=' . $_POST['template_id'] . '&action=colors');
	}
    
    protected function addPropertyToSelectorWithMedia($media, $selector, $setProperties = [], $properties, &$content, $important = false) {
        $content .= $media . '{ ' . PHP_EOL;
        $this->addPropertyToSelector($selector, $setProperties, $properties, $content, $important);        
        $content .= '}' . PHP_EOL . PHP_EOL;
    }
    
    protected function addPropertyToSelector($selector, $setProperties = [], $properties, &$content, $important = false, $fromPattern = false) {
        
        $content .= $selector . '{ ' . PHP_EOL;
                
        $num = count($setProperties);
        $i = 0;
        foreach ($setProperties as $setProperty) {
            if ($fromPattern) {
                $content .= $setProperty . ': ' . str_replace("%value%", $properties[$setProperty], $fromPattern) . ';';
            } else {
                $content .= $setProperty . ': ' . $properties[$setProperty] . ($important ? ' !important' : '') . ';';
            }            
            
            
            if ($i > 0 && $i !== $num - 1) {
                $content .= ',';
            }

            $i++;            
        }
        
        $content .= '}' . PHP_EOL . PHP_EOL; 
    }
    
    function getFormatedColors($colors) {
        
        if ($colors && is_object($colors[0])) {
            
            $colorsFlat = [];
            
            foreach ($colors as $color) {
                $colorsFlat[$color->getName()] = $color->getValue();
            }
            
            $colors = $colorsFlat;
            
        } else {
            $colors = isset($_POST['colors']) ? $_POST['colors'] : null;
        }
        
        return $colors;
    }
    
    function generateTemplateDefault($colors) {
        
        $content = '';
        
        foreach ($colors as $name => $value) {
            if (!$value) {
                continue;
            }
            
            $properties = [];                        
            
            switch($name) {
                case 'bg_color':    
                    $properties['background-color'] = $value;
                    break;
                case 'txt_color':      
                    $properties['color'] = $value;
                    break;
                case 'theme_color':
                    $properties['color'] = $value;
                    $properties['background-color'] = $value;
                    $properties['background'] = $value;
                    break;
                case 'theme_color_active':
                    $properties['color'] = $value;
                    $properties['background-color'] = $value;
                    $properties['border-bottom-color'] = $value;
                    $properties['border-color'] = $value;
                    break;
                case 'theme_color_txt':
                    $properties['color'] = $value;
                    break;                
                case 'theme_color_txt_active':
                    $properties['color'] = $value;
                    break;              
                case 'bg_footer1':
                    $properties['background-color'] = $value;
                    break;
                case 'bg_footer2':
                    $properties['background-color'] = $value;
                    break;
            }
            
            switch($name) {
                case 'bg_color':
                    $this->addPropertyToSelector('body, header, section.content, header .main-top, .slider', ['background-color'], $properties, $content, true);
                    break;
                case 'txt_color':
                    $this->addPropertyToSelector('body', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.dropdown-menu', ['color'], $properties, $content);
                    break;
                case 'theme_color':        
                    $this->addPropertyToSelector('aside.side-bar ul li a', ['color'], $properties, $content); 
                    $this->addPropertyToSelector('.btn-primary:hover, .btn-primary:focus, .btn-primary:active', ['background-color'], $properties, $content, true);                 
                    $this->addPropertyToSelector('header nav ul', ['background-color'], $properties, $content);
                    $this->addPropertyToSelector('header nav ul li a', ['background-color'], $properties, $content, true);
                    $this->addPropertyToSelector('header nav #top-menu li ul a:hover', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-default', ['background-color'], $properties, $content); 
                    $this->addPropertyToSelector('.text-primary', ['color'], $properties, $content); 
                    $this->addPropertyToSelector('.label-primary', ['background-color'], $properties, $content);                      
                    $this->addPropertyToSelector('section.content h2.title', ['background'], $properties, $content);                                                              
                    $this->addPropertyToSelector('footer .second-footer-row .social-icons a i', ['color'], $properties, $content);                      
                    break;  
                case 'theme_color_active':
                    $this->addPropertyToSelector('a:hover, a:focus', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('.btn-default:hover, .btn-default:focus, .btn-default:active', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('.btn-primary', ['background-color'], $properties, $content);                                     
                    $this->addPropertyToSelector('.checkbox a', ['color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('.nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover', ['background-color'], $properties, $content);                                                                                             
                    $this->addPropertyToSelector('header nav ul', ['border-bottom-color'], $properties, $content);                                     
                    $this->addPropertyToSelector('header nav #top-menu li.active a, header nav #top-menu li:hover ul + a, header nav #top-menu li:hover ul a', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('header nav #top-menu li:hover a', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('header nav button:not(.collapsed)', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('aside.side-bar ul li a', ['background-color'], $properties, $content, true);                                                         
                    $this->addPropertyToSelectorWithMedia('@media (max-width: 767px)', 'aside.side-bar h2.title', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('footer .second-footer-row .social-icons a i:hover', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('section.content article a', ['color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('section.content article a', ['color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('input:focus, textarea:focus, .select2-container--bootstrap.select2-container--open .select2-selection, .select2-container--bootstrap .select2-dropdown, .form-control:focus', ['border-color'], $properties, $content);                                     
                    $this->addPropertyToSelector('.select2-container--bootstrap .select2-results__option--highlighted', ['background-color'], $properties, $content);
                    $this->addPropertyToSelector('.select2-container--bootstrap .select2-results__option[aria-selected="true"]:after', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.select2-container--bootstrap .select2-results__option--highlighted[aria-selected="true"]:after', ['color'], $properties, $content);
                    break; 
                case 'theme_color_txt':                                        
                    $this->addPropertyToSelector('header nav ul li a', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.btn-default', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('section.content h2.title', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-primary', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-primary:hover, .btn-primary:focus, .btn-primary:active', ['color'], $properties, $content, true);                    
                    $this->addPropertyToSelector('.dropdown-menu', ['background-color'], $properties, $content);                    
                    $this->addPropertyToSelector('.select2-container--bootstrap .select2-results__option--highlighted', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.select2-container--bootstrap .select2-results__option--highlighted[aria-selected="true"]:after', ['color'], $properties, $content);                    
                    break;                
                case 'theme_color_txt_active':
                    $this->addPropertyToSelector('header nav #top-menu li.active a, header nav #top-menu li:hover ul + a, header nav #top-menu li:hover ul a', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-default:hover, .btn-default:focus, .btn-default:active', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('aside.side-bar ul li a', ['color'], $properties, $content);
                    $this->addPropertyToSelector('aside.side-bar > ul > li > i', ['color'], $properties, $content);
                    break;   
                case 'bg_footer1':
                    $this->addPropertyToSelector('footer .first-footer-row .container', ['background-color'], $properties, $content);
                    break;      
                case 'bg_footer2':
                    $this->addPropertyToSelector('footer .second-footer-row .container', ['background-color'], $properties, $content);
                    break;                                   
            }
           
        }

        return $content;
    }
    
    function generateTemplate1($colors) {
        
        $content = '';
        
        foreach ($colors as $name => $value) {
            if (!$value) {
                continue;
            }
            
            $properties = [];                        
            
            switch($name) {
                case 'bg_color':    
                    $properties['background-color'] = $value;
                    break;
                case 'txt_color':      
                    $properties['color'] = $value;
                    break;
                case 'theme_color':
                    $properties['color'] = $value;
                    $properties['background-color'] = $value;
                    $properties['background'] = $value;
                    $properties['border-bottom-active'] = $value;
                    $properties['text-shadow'] = $value;
                    break;
                case 'theme_color_active':
                    $properties['color'] = $value;
                    $properties['background-color'] = $value;
                    $properties['border-bottom-color'] = $value;
                    $properties['border-bottom'] = $value;
                    $properties['border-color'] = $value;
                    break;
                case 'theme_color_txt':
                    $properties['color'] = $value;
                    break;
                case 'theme_color_txt_active':
                    $properties['color'] = $value;
                    break;                 
                case 'bg_footer1':
                    $properties['background-color'] = $value;
                    break;
                case 'bg_footer2':
                    $properties['background-color'] = $value;
                    break;
            }
            
            switch($name) {
                case 'bg_color':
                    $this->addPropertyToSelector('body, header, section.content, header .main-top, .slider', ['background-color'], $properties, $content, true);
                    break;
                case 'txt_color':
                    $this->addPropertyToSelector('body', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.dropdown-menu', ['color'], $properties, $content);
                    break;
                case 'theme_color':   
                    $this->addPropertyToSelector('aside.side-bar ul li a', ['color'], $properties, $content);    
                    $this->addPropertyToSelector('.btn-primary:hover, .btn-primary:focus, .btn-primary:active', ['background-color'], $properties, $content, true);                 
                    $this->addPropertyToSelector('header nav', ['background-color'], $properties, $content); 
                    $this->addPropertyToSelector('header nav ul', ['background-color'], $properties, $content);
                    $this->addPropertyToSelector('header nav ul li,header nav #top-menu > li > a', ['background-color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-default', ['background-color'], $properties, $content, true); 
                    $this->addPropertyToSelector('.text-primary', ['color'], $properties, $content); 
                    $this->addPropertyToSelector('.label-primary', ['background-color'], $properties, $content);                      
                    $this->addPropertyToSelector('section.content h2.title', ['background'], $properties, $content);                      
                    $this->addPropertyToSelector('footer .second-footer-row .social-icons a i', ['color'], $properties, $content);                                                               
                    $this->addPropertyToSelector('.login-page i', ['text-shadow'], $properties, $content, false, '1px 1px 0px %value%');                                                               
                    break;  
                case 'theme_color_active':
                    $this->addPropertyToSelector('a:hover, a:focus', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('.btn-default:hover, .btn-default:focus, .btn-default:active', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('.btn-primary', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('.checkbox a', ['color'], $properties, $content, true);                                     
                    $this->addPropertyToSelector('.nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover', ['background-color'], $properties, $content);                                                                                                                
                    $this->addPropertyToSelector('header nav', ['border-bottom'], $properties, $content, false, '3px solid %value%');                   
                    $this->addPropertyToSelector('header nav ul', ['border-bottom-color'], $properties, $content);  
                    $this->addPropertyToSelector('header nav #top-menu li.active a, header nav #top-menu li a:hover, header nav button:not(.collapsed)', ['background-color'], $properties, $content, true);  
                    $this->addPropertyToSelector('header address span i', ['background-color'], $properties, $content);  
                    $this->addPropertyToSelector('header nav #top-menu li:hover a', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('header nav button:not(.collapsed)', ['background-color'], $properties, $content, true);                                     
                    $this->addPropertyToSelectorWithMedia('@media (max-width: 767px)', 'aside.side-bar h2.title', ['background-color'], $properties, $content, true);
                    $this->addPropertyToSelector('footer .second-footer-row .social-icons a i:hover', ['color'], $properties, $content);                                     
                    $this->addPropertyToSelector('section.content article a', ['color'], $properties, $content, true);  
                    $this->addPropertyToSelector('.search-form input:focus + .input-group-btn button', ['background-color'], $properties, $content, true);  
                    $this->addPropertyToSelector('.search-form input:focus + .input-group-btn button', ['border-color'], $properties, $content, true);  
                    $this->addPropertyToSelector('.products-list li .product-price-data .special', ['color'], $properties, $content);  
                    $this->addPropertyToSelector('.product-frame .product-image-frame .product-image-captions .product-img-nav', ['background-color'], $properties, $content);  
                    $this->addPropertyToSelector('.pagination li.active a, .pagination li:hover a', ['background-color'], $properties, $content, true);  
                    $this->addPropertyToSelector('ul.pagination>li:first-child>a:hover, ul.pagination>li:last-child>a:hover', ['color'], $properties, $content, true);  
                    $this->addPropertyToSelector('aside.side-bar > ul > li.active > a', ['background-color'], $properties, $content, true);  
                    $this->addPropertyToSelector('aside.side-bar > ul > li:not(.active) > i:hover,aside.side-bar ul ul li.active a', ['color'], $properties, $content);  
                    $this->addPropertyToSelector('.login-page i', ['color'], $properties, $content);  
                    $this->addPropertyToSelector('footer .first-footer-row ul li a:before', ['color'], $properties, $content);                      
                    break;  
                case 'theme_color_txt':
                    $this->addPropertyToSelector('header nav ul li a', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('section.content h2.title', ['color'], $properties, $content, true);
                    
                    $this->addPropertyToSelector('.btn-default', ['color'], $properties, $content, true);
                    
                    $this->addPropertyToSelector('.btn-default:hover, .btn-default:focus, .btn-default:active', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-primary', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.btn-primary:hover, .btn-primary:focus, .btn-primary:active', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('.dropdown-menu', ['background-color'], $properties, $content);
                    $this->addPropertyToSelector('header nav #top-menu li.active a, header nav #top-menu li:hover ul + a, header nav #top-menu li:hover ul a', ['color'], $properties, $content, true);
                    $this->addPropertyToSelector('header address span i', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.search-form input:focus + .input-group-btn button', ['color'], $properties, $content);
                    $this->addPropertyToSelector('.product-frame .product-image-frame .product-image-captions .product-img-nav', ['color'], $properties, $content);                    
                    break; 
                case 'theme_color_txt_active':
                    $this->addPropertyToSelector('aside.side-bar > ul > li.active > i, aside.side-bar > ul > li.active > a', ['color'], $properties, $content);
                    $this->addPropertyToSelector('header nav #top-menu li.active a, header nav #top-menu li:hover ul + a, header nav #top-menu li:hover ul a', ['color'], $properties, $content, true);
//                        $this->addPropertyToSelector('.btn-default:hover, .btn-default:focus, .btn-default:active', ['color'], $properties, $content, true);
//                        $this->addPropertyToSelector('aside.side-bar ul li a', ['color'], $properties, $content);
//                        $this->addPropertyToSelector('aside.side-bar > ul > li > i', ['color'], $properties, $content);
                        
                    break;                 
                case 'bg_footer1':
                    $this->addPropertyToSelector('footer .first-footer-row', ['background-color'], $properties, $content);
                    break;      
                case 'bg_footer2':
                    $this->addPropertyToSelector('footer .second-footer-row', ['background-color'], $properties, $content);
                    break;                  
            }
           
        }

        return $content;
    }
    
    function generateColorsCss($templateId, $colors = null) {
        $colors = $this->getFormatedColors($colors);

        if (!$colors) {
            return false;
        }
        
        $file = CMS_DIR . '/public/css/template/' . Cms::$template->getSlug() . '/colors.css';
        $myFile = fopen($file, "w") or die("Unable to open file!");              
        
        switch ($templateId) {
            case 1:
                $content = $this->generateTemplateDefault($colors);
                break;
            case 2:
                $content = $this->generateTemplate1($colors);
                break;        
            default :
				throw new \Exception('Nieobsługiwany szablon!');
				break;
        }

        fwrite($myFile, $content);
        fclose($myFile);        
    }

	function restoreAction() {			
		$colorRepository = CMS::$entityManager->getRepository('Application\Entity\Color');		
		
		$defaultColors = $colorRepository->findBy(['template' => $_GET['template_id'], 'isDefault' => 1]);
		$colors = $colorRepository->findBy(['template' => $_GET['template_id'], 'isDefault' => 0]);
		
		foreach ($defaultColors as $defaultColor) {			
			foreach ($colors as $color) {
				if ($defaultColor->getName() == $color->getName()) {
					$color->setValue($defaultColor->getValue());
					CMS::$entityManager->persist($color);							
					break;
				}
			}
		}
		
		CMS::$entityManager->flush();
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
        
        $this->generateColorsCss($_GET['template_id'], $defaultColors);

		redirect(URL . '/admin/templates?template_id=' . $_GET['template_id'] . '&action=colors');	

	}
	
	function editAction() {
		if (!$_SESSION[USER_CODE]['available_actions']['template_change']) {
			redirect(URL . '/admin/templates');
		}
				
		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
		$entities = $entityRepository->findAll();
		
		foreach ($entities as $entity) {
			$entity->setActive(false);
		}
		
		CMS::$entityManager->flush();		
				
		$entity =  $entityRepository->find($_REQUEST['id']);
		$entity->setActive(true);
		CMS::$entityManager->persist($entity);
		CMS::$entityManager->flush($entity);
		
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
		redirect(URL . '/admin/templates');		
	}	
	
	function listAction() {
		$templateRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
		$entities = $templateRepository->findAll();

		$data = array(
			'entities'	=> $entities,
			'actionTemplateChange'	=> $_SESSION[USER_CODE]['available_actions']['template_change'],
			'pageTitle' => 'Templates',
		);

		echo Cms::$twig->render('admin/templates/list.twig', $data);
	}
	
	
}

$controller = new TemplatesController();
$controller->init($params);