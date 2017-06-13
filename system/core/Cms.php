<?php

//namespace System\Core;
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require(SYS_DIR . '/core/CmsModel.php');
require(CLASS_DIR . '/ShoppingThresholdsHelper.php');
require(CLASS_DIR . '/Flashbag.php');
require_once(MODEL_DIR . '/Logotype.php');
require_once(MODEL_DIR . '/Language.php');
require_once(MODEL_DIR . '/SeoSetting.php');
require_once(CLASS_DIR . '/Session.php');
require_once(SYS_DIR . '/core/Logger.php');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
require_once(ENTITY_DIR . '/SiteGlobal.php');
//use Application\Entity\SiteGlobal;
require_once(ENTITY_DIR . '/OrderLog.php');

use Application\Entity\OrderLog;

class Cms {

	public static $tableConfig;
	public static $tableModules;
	public static $tableLanguages;
	private static $instance;
	public $cmsModel;
	public static $log;
	public static $db;
	public static $shoppingThresholds;
	public static $conf;
	public static $modules;
	public static $langs;
	public static $locales;
	public static $frontendLanguages;
	public static $defaultLocale;
	public static $defaultLanguage;
	public static $logotypes;
	private $config;
	public static $twig;
	public static $flashbag;
	public static $session;
	public static $loader;
	public static $entityManager;
	public static $seo;
	public static $colors;
	public static $template;
	public static $orderLog;

	private function __construct() {
		self::$tableConfig = DB_PREFIX . 'config';
		self::$tableModules = DB_PREFIX . 'modules';
		self::$tableLanguages = DB_PREFIX . 'languages';

		self::$log = new Logger();
		self::$db = new Database();
        self::$shoppingThresholds = new ShoppingThresholdsHelper();        
        self::$flashbag = new Flashbag();    
        self::$session = new Session();    
		$this->cmsModel = new CmsModel();

		self::$conf = $this->cmsModel->loadConfig();
		self::$modules = $this->cmsModel->loadModules();
		self::$langs = $this->cmsModel->loadLanguages();    
//		self::$trans = $this->cmsModel->loadLanguages();    
		self::$locales = self::locales();    
		
		$language = new Language();
		self::$frontendLanguages = $language->getFrontendLanguages();    
		self::$defaultLocale = $language->getBy(['default' => 1])[0]['code'];
		self::$defaultLanguage = $language->getBy(['default' => 1])[0];
		self::$logotypes = $this->cmsModel->loadLogotypes();    
		
		$seo = new SeoSetting();
		self::$seo = $seo->getBy(['locale' => Cms::$session->get('locale')])[0];   
        
		require_once CMS_DIR . '/vendor/autoload.php';
		self::$loader = new Twig_Loader_Filesystem(VIEW_DIR);	//katalog do szablonow
		self::$twig = new Twig_Environment(self::$loader, array(
			'cache' => CMS_DIR . '/application/compilation_cache',
			'debug' => true
		));	
		//add extenstion to can use dump
		self::$twig->addExtension(new Twig_Extension_Debug());
		
		//truncate & wordwrap
		self::$twig->addExtension(new Twig_Extensions_Extension_Text());
		
		//trans filter
		$filter = new \Twig_SimpleFilter('trans', function ($name) {
			$siteGlobalRepository = CMS::$entityManager->getRepository('Application\Entity\SiteGlobal');
			$entity = $siteGlobalRepository->findOneBy(['name' => $name]);		//if would more translations then change this!!!

			if ($entity) {
				foreach ($entity->getTranslations() as $trans) {
					if ($trans->getLocale() == Cms::$session->get('locale')) {
						return $trans;
					}
				}
			}
			
			if (isset($GLOBALS['LANG'][$name])) {
				return $GLOBALS['LANG'][$name];
			}
			
			return false;
		});
		
		self::$twig->addFilter($filter);

		//add possibility to display static properties/functions
		$staticFunc = new \Twig_SimpleFunction('static', function ($class, $property) {
			if (property_exists($class, $property)) {
				return $class::$$property;
			}

			return null;
		});	
		self::$twig->addFunction($staticFunc); //{{ static('YourNameSpace\\ClassName', 'VARIABLE_NAME') }}
		
		//add $_SERVER
		$serverFunc = new \Twig_SimpleFunction('server', function ($property) {
			if (isset($_SERVER[$property])) {
				return $_SERVER[$property];
			}

			return null;
		});	
		self::$twig->addFunction($serverFunc); //{{ static('YourNameSpace\\ClassName', 'VARIABLE_NAME') }}	
		
		//add parameter function to get parameters from url
		$parameterFunc = new \Twig_SimpleFunction('parameter', function ($property, $method = 'get') {
			
			$method = strtolower($method);
			
			switch ($method) {
				case 'get':
					if (isset($_GET[$property])) {
						return $_GET[$property];
					}									
					break;
				case 'post':
					if (isset($_POST[$property])) {
						return $_POST[$property];
					}
					break;
				default:
					throw new \Exception(__FILE__ . ' Unknown param: ' . $method .' for function parameter');
			}

			return null;
		});		
		self::$twig->addFunction($parameterFunc); //{{ static('YourNameSpace\\ClassName', 'VARIABLE_NAME') }}
		
		//add file_exists function $filename path
		$fileExistsFunc = new \Twig_SimpleFunction('file_exists', function ($filename) {
			return file_exists($filename);
		});	
		self::$twig->addFunction($fileExistsFunc);	

		$breadcrumbsFunc = new \Twig_SimpleFunction('breadcrumbs', function ($separator = ' &raquo; ', $home = 'Home', $productName = false) {
			// This gets the REQUEST_URI (/path/to/file.php), splits the string (using '/') into an array, and then filters out any empty values
			$path = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

			// This will build our "base URL" ... Also accounts for HTTPS :)
			$base = (isset($_SERVER['HTTPS']) && strtolower($_SERVER["HTTPS"]) == "on" ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

			// Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
			$breadcrumbs = Array('<ol class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' .$base .'"><span itemprop="name">' . $home . '</span></a><meta itemprop="position" content="1" /></li>');
			$category = new Category();
			$categories = $category->getAll()[Cms::$session->get('locale')];
			$categories = getArrayByKey($categories, 'slug');						

			if (in_array($path[1], Cms::$locales)) {
				$base .= $path[1] . '/';
				unset($path[1]);
			}			
			
			if ($key = array_search('product', $path)) {
				$path[$key] = 'shop';
			}
			
			$path = array_values($path);

			$keys = array_keys($path);
			// Find out the index for the last value in our path array
			$last = end($keys);

			// Build the rest of the breadcrumbs
			foreach ($path AS $x => $crumb) {

				// Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
				$title = str_replace(Array('.php', '_', '.html'), Array('', ' ', ''), $crumb);

				if ($title == 'shop' && Cms::$session->get('locale') == 'pl') {
					$title = 'sklep';
				}

				$newCrumb = '';
				if ($crumb != 'shop') {
					$newCrumb = 'shop/';
				}

				if ($x == 2) {
					$newCrumb .= $path[1] . '/' . $crumb;
				} else {					
					$newCrumb .= $crumb;
				}
				
				if (isset($categories[$title])) {
					$title = $categories[$title]['name'];
				}				
				
				$crumb = $newCrumb;

				$crumb .= '.html';
				$title = ucfirst($title);				
				
				if ($productName && $x == $last) {
					$title = $productName;
				}
				
				// If we are not on the last index, then display an <a> tag
				if ($x != $last) {
					$breadcrumbs[] = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' . $base . $crumb . '"><span itemprop="name">' . $title . '</span></a><meta itemprop="position" content="' . ($x+2) .'" /></li>';
				// Otherwise, just display the title (minus)
                } else {
					$breadcrumbs[] = $title . '</ol>';
                }
			}
                        
			// Build our temporary array (pieces of bread) into one big string :)
			return implode($separator, $breadcrumbs);
		});		
		self::$twig->addFunction($breadcrumbsFunc); 				
		
		self::setDoctrine();
		
		self::setTemplateAndColors();
	}
	
	function staticCall($class, $function, $args = array()) {
		if (class_exists($class) && method_exists($class, $function))
			return call_user_func_array(array($class, $function), $args);
		return null;
	}

	private function __clone() {
		
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Cms();
		}

		return self::$instance;
	}

	public static function initialize() {
		self::getInstance();
	}

	public static function conf() {
		return self::$instance->cmsModel->loadConfig();
	}

	public static function modules() {
		return self::$instance->cmsModel->loadModules();
	}

	public static function languages() {
		return self::$instance->cmsModel->loadLanguages();
	}
	
	public static function locales() {
		$locales = [];
		foreach (self::$langs as $lang) {
			$locales[] = $lang['code'];
		}
				
		return $locales;
	}	

	public static function loadAdmin($module) {
		return self::$instance->cmsModel->loadAdmin($module);
	}
	
	public static function save($data) {
		return self::$instance->cmsModel->save($data);
	}
    
	public static function user($property) {
		return isset($_SESSION[USER_CODE][$property]) ? $_SESSION[USER_CODE][$property] : false;
	}
	
	public static function getFlashbag() {
		return self::$flashbag;
	}
	
	public static function getSession() {
		return self::$session;
	}
	
	public static function setDoctrine() {

		$loader = require __DIR__ . '/../../vendor/autoload.php';
		AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

		$paths = array(ENTITY_DIR);
		$isDevMode = false;
		
		$dbParams = array(
			'driver'   => 'pdo_mysql',
			'host'	   => DB_SERVER,
			'port'     => DB_PORT,			
			'user'     => DB_USER,
			'password' => DB_PASSWORD,
			'dbname'   => DB_NAME,
			'charset'  => 'utf8',
		);

		$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
		$config->setProxyDir(CLASS_DIR . '/Proxies');
		$config->setProxyNamespace('Application\Classes\Proxies');		
		$config->setAutoGenerateProxyClasses(Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
		$entityManager = EntityManager::create($dbParams, $config);
		
		$platform = $entityManager->getConnection()->getDatabasePlatform();
		$platform->registerDoctrineTypeMapping('enum', 'string');		

		self::$entityManager = $entityManager;
	}
	
	public static function setTemplateAndColors() {	
		require_once(ENTITY_DIR . '/Template.php');
		$entityRepository = CMS::$entityManager->getRepository('Application\Entity\Template');
		$template = $entityRepository->findOneBy(['active' => 1]);
		
		$colors = [];
		foreach ($template->getColors() as $color) {
			$colors[$color->getName()] = $color->getValue();
		}
		
		self::$colors = $colors;
		self::$template = $template;
	}
    
    public static function orderLogSave($orderId, $action, array $params) {        
        
        if (!$orderId || !$action) {
            return false;
        }
        
        $orderLog = new OrderLog();
        $orderLog->setOrderId($orderId);
        $orderLog->setAction($action);
        $orderLog->setParams($params);
        
        Cms::$entityManager->persist($orderLog);
        Cms::$entityManager->flush(); 	
    }
}
