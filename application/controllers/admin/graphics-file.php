<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('graphics'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/graphics.php');

class GraphicsFileController {
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
		$action = $this->getParam('action') ? $this->getParam('action') : 'edit';
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
//        
//        echo '<pre>';
//        var_dump($params);
//        echo '</pre>';
//        
//        echo '<pre>';
//        var_dump($_POST);
//        echo '</pre>';
//        
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
	}

	public function setAccess() {
		$this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}

    protected function saveFile($key, $file) {
        $path = $file['name'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));           

        $target_dir = CMS_DIR . '/files/graphics/';       
        $target_file = $target_dir . $key . '.' . $ext;

        if ($path) {
            switch ($key) {
                case 'logo':
                    if ($ext != 'png') {
                        return false;
                    }
                    break;
                case 'favicon':
                    if ($ext != 'ico') {
                        return false;
                    }                            
                    break;
                case 'new':
                    if ($ext != 'png') {
                        return false;
                    }  
                    break;
                case 'mega_offer':
                    if ($ext != 'png') {
                        return false;
                    }                            
                    break;
            }
            
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
//                echo "The file ". basename($file["name"]). " has been uploaded.";
            } else {
				echo $GLOBALS['LANG']['error_edit']; die;
                Cms::$tpl->setError($GLOBALS['LANG']['error_edit']);
            }
            
            return true;
        }
    }
    
	public function editAction() {
		$params = [];
		if (!$_POST) {
			$this->editForm();
		} else {

            foreach ($_FILES as $key => $file) {        
                if ($file['name']) {                
                    if ($this->saveFile($key, $file)) {
						$params['info'] = $GLOBALS['LANG']['info_edit'];
                    } else {
						$params['error'] = $key . ' ma ' . 'zły format pliku!';
                    }
                }
            }
            $this->editForm($params);
		}
        
	}

	function editForm($params = []) {
        $logoFile = CMS_DIR . '/files/graphics/logo.png';
        $faviconFile = CMS_DIR . '/files/graphics/favicon.ico';
        $newProductFile = CMS_DIR . '/files/graphics/new.png';
        $megaOfferFile = CMS_DIR . '/files/graphics/mega_offer.png';
        
		$data = array(
			'pageTitle'	=>	$GLOBALS['LANG']['module_graphics_file'],
			'logoFile'	=>	$logoFile,
			'faviconFile'	=>	$faviconFile,
			'newProductFile'	=>	$newProductFile,
			'megaOfferFile'	=> $megaOfferFile
		);

		echo Cms::$twig->render('admin/graphics_file/edit.twig', array_merge($data, $params));			
	}

}

$controller = new GraphicsFileController();
$controller->init($params);
