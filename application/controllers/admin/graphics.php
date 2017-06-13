<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('graphics'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/graphics.php');

class GraphicsController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
        $this->entity = new GraphicsModel();
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

		foreach ($params as $key => $value) {
			switch ($key) {
				case 0:
				// no break
				case 1:
					$this->params['controller'] = $value;
					break;
				case 2:
					$this->params['action'] = $value;
					break;
				case 3:
					$this->params['id'] = $value;
					break;
				default:
					throw new \Exception('Unknown params.');
					break;
			}
		}
		
		if ($_POST) {
			foreach ($_POST as $key => $value) {
				$this->params[$key] = $value;
			}
		}				
	}

	public function setAccess() {
		$this->access = in_array($_SESSION[USER_CODE]['level'], [1,2]) ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}
	
	public function saveAction() {
		$graphics = $this->entity->getAll();
		$graphics = getArrayByKey($graphics, 'id');
		
		$updated = false;
		foreach ($graphics as $key => $color) {
			if ($color['value'] != $_POST['graphics'][$key]) {
				$item = array(
					'value' => $_POST['graphics'][$key]
				);
				
				$this->entity->updateById($key, $item);
				$updated = true;
			}
		}
		
		$params['info'] = $GLOBALS['LANG']['info_edit'];
		
		if ($updated) {
			$this->generateCss();
		}
				
		$this->listAction($params);
	}	

	function listAction($params = []) {        
        $entities = $this->entity->getAll();

		$data = array(
			'entities'	=>	$entities,
			'pageTitle'	=>	$GLOBALS['LANG']['module_graphics'],
			'module'	=>	$this->module,
			'url_add'	=> $this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false
		);

		echo Cms::$twig->render('admin/graphics/list.twig', array_merge($data, $params));			
	}
    
    function generateCss() {
        $file = CMS_DIR . '/files/theme.css';
        $myfile = fopen($file, "w") or die("Unable to open file!");
        
        $items = $this->entity->getAll();

        $content = '';
        foreach ($items as $item) {
            if (!$item['value']) {
                continue;
            }
                   
            $property = '';
            
            switch($item['name']) {
                case 'bg_color':
                    $property = 'background-color:' . $item['value'];
                    break;
                case 'txt_color':
                    $property = 'color:' . $item['value'];
                    break;
                case 'bg_color_head':
                    $property = 'background-color:' . $item['value'];
                    break;
                case 'txt_color_head':
                    $property = 'color:' . $item['value'];
                    break;                
                case 'color_active':
                    $property = null;
                    break;                
                case 'bg_footer1':
                    $property = 'background-color:' . $item['value'];
                    break;                
                case 'bg_footer2':
                    $property = 'background-color:' . $item['value'];
                    break;                
                case 'btn_border_color':
                    $property = 'border: 1px solid ' . $item['value'] . ';' . PHP_EOL;
                    $property .= 'padding: 8px 3px';
                    break;                
                case 'btn_bg_color':
                    $property = 'background-color:' . $item['value'];
                    break;                
                case 'btn_bg_hover_color':
                    $property = 'background-color:' . $item['value'];
                    break;                
                case 'btn_txt':
                    $property = 'color:' . $item['value'];
                    break;                
                case 'btn_hover_txt':
                    $property = 'color:' . $item['value'];
                    break;      
                case 'color_link':
                    $property = 'color:' . $item['value'];
                    break;      
//                default:
//                    $property = 'test:' . $item['value'];
//                    break;                
            }

            switch($item['name']) {
                case 'bg_color':
                    $content .= 'body';
                    break;
                case 'txt_color':
                    $content .= 'body';
                    break;
                case 'bg_color_head':
//                    $this->setBasketColor($item, $content);
                    $content .= '#menu-top ';
                    $content .= '.navbar';
                    $content .= ', .navbar-brand';                 
                    $content .= ', .list .panel-default > .panel-heading';               
                    $content .= ', .list .title h1, #pageTitle h1, .product-desc h1, .button-basket, .basket, .account-menu button, #menu-left h3';                                             
                    $content .= ', .lang-switcher ul, .lang-switcher > ul > li > a';
                    break;
                case 'txt_color_head':
                    $content .= '#menu-top a';
                    $content .= ', #menu-top .navbar li.active.dropdown.open .dropdown-menu a';
                    $content .= ', #menu-top .navbar .navbar-nav > .active a, #menu-top .dropdown.open a.dropdown-toggle';
                    $content .= ', .account-menu.dropdown.open button, .account-menu button:hover';
                    $content .= ', .account-menu.dropdown .dropdown-menu > li > a';
                    $content .= ', .account-menu button, .basket a';
                    $content .= ', .list .title h1, #pageTitle h1, .product-desc h1';
                    $content .= ', #menu-left h3';
                    $content .= ', .list .panel-default > .panel-heading';
                    $content .= ', .account-menu .btn:focus';
                    $content .= ', .lang-switcher ul, .lang-switcher > ul > li > a';
                    break;
                case 'color_active':
                    $this->setMenuTopColor($item, $content);
                    $this->setColorActivebeBg($item, $content);    
                    continue;
                    break;   
                case 'bg_footer1':
                    $content .= '#footer1';
                    break;                
                case 'bg_footer2':
                    $content .= '#footer2';
                    break;                
                case 'color_link':
                    $content .= '.item .name a, a.producer-name, .tags a';
                    $content .= ', a.button-continue-shopping';
                    $content .= ', .accept-terms a';
                    $content .= ', .alphabet-list a';
                    break;    
                case 'btn_border_color':
                    $content .= '.button-basket';
                    $content .= ', .btn-newsletter';
                    break;
                case 'btn_bg_color':
                    $content .= '.button-basket';
					$content .= ', .btn-newsletter';
                    break;                 
                case 'btn_bg_hover_color':
                    $content .= '.button-basket:hover';
					$content .= ', .btn-newsletter:hover';
                    break;                 
                case 'btn_txt':
                    $content .= '.button-basket a';
					$content .= ', .btn-newsletter';
                    break;                 
                case 'btn_hover_txt':
                    $content .= '.button-basket a:hover';
					$content .= ', .btn-newsletter:hover';
                    break;     
//                default:
//                    $content .= '';
//                    break;
            }

            if ($property) {
                $content .= ' {' . PHP_EOL;
                $content .= $property . ';';
                $content .= '}' . PHP_EOL . PHP_EOL;  
            }
        }

        fwrite($myfile, $content);
        fclose($myfile);
    }
    
//    function setBasketColor($item, &$content) {
//        $content .= '.basket { ' . PHP_EOL;
//        $content .= 'border: 1px solid ' . $item['value'] .';}' . PHP_EOL . PHP_EOL; 
//    }
    
    function setColorActivebeBg($item, &$content) {
        $content .= '.dropdown-menu,.account-menu .dropdown-menu, .account-menu.dropdown.open button, .account-menu button:hover, #menu-left > ul > li > a { ' . PHP_EOL;
        $content .= 'background-color: ' . $item['value'] .';}' . PHP_EOL . PHP_EOL;
         
    }
    
    function setMenuTopColor($item, &$content) {
        $content .= '#menu-top .navbar { ' . PHP_EOL;
        $content .= 'border: 0;' . PHP_EOL;
        $content .= 'border-radius: inherit;' . PHP_EOL;
        $content .= 'border-bottom: 4px solid ' . $item['value'] .';}' . PHP_EOL . PHP_EOL; 
        
        $content .= '#menu-top a:hover {' . PHP_EOL;
        $content .= 'color:' . $item['value'] .';}' . PHP_EOL . PHP_EOL; 
                
        $content .= '.menu-module .nav-layer ul li {' . PHP_EOL;
        $content .= 'color:' . $item['value'] .';}' . PHP_EOL . PHP_EOL; 
        
        $content .= '#menu-top .navbar li.active.dropdown.open .dropdown-menu a:hover {' . PHP_EOL;
        $content .= 'background-color: #f5f5f5;color:' . $item['value'] .'}' . PHP_EOL . PHP_EOL;  
        
        $content .= '#menu-top .navbar li.active a, #menu-top .nav .open > a.dropdown-toggle, .nav .open > a:focus, .nav .open > a:hover {' . PHP_EOL; 
        $content .= 'background-color:' . $item['value'] .';}' . PHP_EOL . PHP_EOL;                            
		
        $content .= '.lang-switcher .dropdown-menu > li > a:focus, .dropdown-menu > li > a:hover {' . PHP_EOL; 
        $content .= 'color:' . $item['value'] .';}' . PHP_EOL . PHP_EOL;                            
    }
}

$controller = new GraphicsController();
$controller->init($params);
