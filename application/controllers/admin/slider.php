<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('graphics'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/Slider.php');

class SliderController {

    private $params;
    private $access;
    private $module;
    private $entity;
    private $dir;
    private $url;    

    public function __construct() {
        $this->entity = new Slider();        
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
        foreach ($params as $key => $value) {
            switch ($key) {
                case 0:
                // no break
                case 1:
                    $this->params['controller'] = $value;
                    $this->params['lang_id'] = _ID;
                    break;
                case 2:
                    $this->params['lang_id'] = $value;
                    break;
                default:
                    throw new \Exception('Unknown params.');
                    break;
            }
        }
		
		if ($_REQUEST) {
			foreach ($_REQUEST as $key => $value) {
				$this->params[$key] = $value;
			}
		}			
    }

    public function setAccess() {
        $this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
    }

    public function setModule() {
        $this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
        $this->dir = CMS_DIR . '/files/' . $this->module;
        $this->url = CMS_URL . '/files/' . $this->module;		
    }

	function addAction($params = []) { 
		
		$data = array(
			'pageTitle' => $GLOBALS['LANG']['module_slider']
		);
		
		if ($_POST) {

			if (!$this->entity->add($_POST, $_FILES)) {
				redirect(URL . '/admin/slider?action=add');
			}

			redirect(URL . '/admin/slider');
		} else {
			echo Cms::$twig->render('admin/slider/add.twig', $data);
		}
		
	}
	
	function editAction() {
		
		$entity = $this->entity->getById($_GET['id']);

		$data = array(
			'entity' => $entity,
			'url'	=> $this->url,
			'pageTitle' => $GLOBALS['LANG']['module_slider']
		);
		
		echo Cms::$twig->render('admin/slider/edit.twig', $data);
	}
	
	function saveAction() {
		if (!$this->entity->edit($_POST)) {
			$this->editAction();
		}

		redirect(URL . '/admin/slider');	
	}
	
	function deleteImageAction() { 
		
		if ($this->entity->deleteImageByName($_GET['file'])) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		}
		
		redirect(URL . '/admin/slider');
	}
	
	function deleteAction() { 
		$this->entity->deleteById($_GET['id']);		
		redirect(URL . '/admin/slider');
	}
	
	function upAction() { 
		$this->entity->moveUp($_GET);
		redirect(URL . '/admin/slider');
	}
	
	function downAction() { 
		$this->entity->moveDown($_GET);
		redirect(URL . '/admin/slider');
	}
	
	
    function listAction($params = []) {   
		$entities = $this->entity->getAll();
		
		$data = array(
			'entities'	=>	$entities,
			'pageTitle'	=>	$GLOBALS['LANG']['module_slider'],
			'langs'	=>	CMS::$langs,
			'module'	=>	$this->module,
			'url_add'	=>	$this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false,
			'url'	=> $this->url,
			'dir'	=> $this->url
		);

		echo Cms::$twig->render('admin/slider/list.twig', array_merge($data, $params));		
    }
	
    function listOldAction($params = []) {        
        $actionGet = (isset($_GET['action']) AND $_GET['action']) ? $_GET['action'] : '';
        $actionPost = (isset($_POST['action']) AND $_POST['action']) ? $_POST['action'] : '';

        switch ($actionPost) {
            case 'add':
				$result = $this->entity->add($_POST, $_FILES);
				
				if ($result === true) {
					$params['info'] = $GLOBALS['LANG']['info_add'];
				} else {
					$params['error'] = $result;
				}

                break;
            case 'edit':
                $this->entity->edit($_POST);
                break;
        }
        
        switch ($actionGet) {
            case 'up':
                $this->entity->moveUp($_GET);
                break;
            case 'down':
                $this->entity->moveDown($_GET);
                break;
            case 'delete':
                $this->entity->deleteAdmin($_GET);
                break;
        }        

		$entities = $this->entity->loadAdmin($this->params['lang_id']);
		
		$data = array(
			'entities'	=>	$entities,
			'pageTitle'	=>	$GLOBALS['LANG']['module_slider'],
			'langs'	=>	CMS::$langs,
			'module'	=>	$this->module,
			'url_add'	=>	$this->access == 1 ? CMS_URL . '/admin/' . $this->module . '/add.html' : false,
			'url'	=> $this->url,
			'dir'	=> $this->url
		);

		echo Cms::$twig->render('admin/slider/list.twig', array_merge($data, $params));		
    }

}

$controller = new SliderController();
$controller->init($params);
