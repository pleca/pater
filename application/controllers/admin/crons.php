<?php

/* 2015-10-14 | 4me.CMS 15.3 */
require_once(ENTITY_DIR . '/Cron.php');
use Application\Entity\Cron;
//check_permission('payment'); // sprawdzamy dostep dla podanego modulu

check_level(1);

class CronsController {
    private $params;
    private $access;
    private $module;
    private $entity;

	public function __construct() {
//		$this->entity = new Cron();
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
    
	function addFormAction($cron = null) {		
		
		$data = array(
			'entity' => $cron,
			'pageTitle' => 'Crons',
		);
		
		echo Cms::$twig->render('admin/crons/add.twig', $data);
	}
	
	function deleteAction() {
		$cronRepository = CMS::$entityManager->getRepository('Application\Entity\Cron');
		$entity =  $cronRepository->find($_REQUEST['id']);
		CMS::$entityManager->remove($entity);
		CMS::$entityManager->flush();	
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
		redirect(URL . '/admin/crons');			
	}
	
	function addAction() {				
		$cron = new Cron();

		if ($_POST) {
			$_POST['active'] = isset($_POST['active']) ? $_POST['active'] : 0;
			$format = 'Y-m-d H:i';

			$startDate = !empty($_POST['startDate']) ? DateTime::createFromFormat($format, $_POST['startDate']) : null;
			$endDate = !empty($_POST['endDate'])  ? DateTime::createFromFormat($format, $_POST['endDate']) : null;
			
			$cron->setName($_POST['name']);
			$cron->setDescription($_POST['description']);
			$cron->setMinute($_POST['minute']);
			$cron->setHour($_POST['hour']);
			$cron->setDayOfMonth($_POST['dayOfMonth']);
			$cron->setMonth($_POST['month']);
			$cron->setDayOfWeek($_POST['dayOfWeek']);
			$cron->setStartDate($startDate);
			$cron->setEndDate($endDate);
			$cron->setActive($_POST['active']);	
			
			if ($cron->validate()) {
				CMS::$entityManager->persist($cron);
				CMS::$entityManager->flush();	
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
				redirect(URL . '/admin/crons');				
			} else {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
				$this->addFormAction($cron);
			}			
		}
		
		$data = array(
			'entity'	=> $cron,
			'pageTitle' => 'Crons',
		);	
	}	
	
	function editAction() {		

		$cronRepository = CMS::$entityManager->getRepository('Application\Entity\Cron');
		$entity =  $cronRepository->find($_REQUEST['id']);
		
		if ($_POST) {
			$_POST['active'] = isset($_POST['active']) ? $_POST['active'] : 0;
			$format = 'Y-m-d H:i';
			
			$startDate = !empty($_POST['startDate']) ? DateTime::createFromFormat($format, $_POST['startDate']) : null;
			$endDate = !empty($_POST['endDate'])  ? DateTime::createFromFormat($format, $_POST['endDate']) : null;

			$entity->setName($_POST['name']);
			$entity->setDescription($_POST['description']);
			$entity->setMinute($_POST['minute']);
			$entity->setHour($_POST['hour']);
			$entity->setDayOfMonth($_POST['dayOfMonth']);
			$entity->setMonth($_POST['month']);
			$entity->setDayOfWeek($_POST['dayOfWeek']);
			$entity->setStartDate($startDate);
			$entity->setEndDate($endDate);
			$entity->setActive($_POST['active']);

			if ($entity->validate()) {
				CMS::$entityManager->persist($entity);
				CMS::$entityManager->flush();	
				
				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
				redirect(URL . '/admin/crons');
			}
		} else {
			$entity =  $cronRepository->find($_GET['id']);
		}				

		$data = array(
			'entity'	=> $entity,
			'pageTitle' => 'Crons',
		);

		echo Cms::$twig->render('admin/crons/edit.twig', $data);
	}	
	
	function listAction() {

		$cronRepository = CMS::$entityManager->getRepository('Application\Entity\Cron');
//		$entities = $cronRepository->findBy([],['order' => 'ASC']);
		$entities = $cronRepository->findAll();

		$data = array(
			'entities'	=> $entities,
			'pageTitle' => 'Crons',
		);

		echo Cms::$twig->render('admin/crons/list.twig', $data);
	}
	
	
}

$controller = new CronsController();
$controller->init($params);