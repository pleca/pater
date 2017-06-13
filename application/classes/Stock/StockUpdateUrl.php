<?php 

require_once(CLASS_DIR . '/Stock/StockUpdateInterface.php'); 
require_once(MODEL_DIR . '/Variation.php'); 
require_once(CMS_DIR . '/application/models/mailer.php');
require_once(MODEL_DIR . '/EmailTemplate.php');
require_once(MODEL_DIR . '/NotificationsStockAvailability.php');
require_once(MODEL_DIR . '/shopProducts.php');

class StockUpdateUrl implements StockUpdateInterface {
	
	private $eanColumn;
	private $qtyColumn;
	private $qtyColumnType;
	private $url;
	private $notUpdatedProducts = [];
	const QTY_TRUE = 1000;
	
	public static $availableSites = array(
		'b2b-trecnutrition.com'
	);		
	
	public function init() {			
		$this->setUrl($_POST['url']);								
		$this->validateUrl();
		
		$parse = parse_url($this->getUrl());
		$host = $parse['host'];
		
		switch ($host) {
			case 'b2b-trecnutrition.com':
				$this->setEanColumn(2); // kod_kreskowy = ean 	
				$this->setQtyColumn(11); // stock = qty	
				$this->setQtyColumnType('bool');
				break;
			default:
				throw new \Exception('Nieobsługiwany host.');
				break;
		}
				
	}
	
	protected function validateUrl() {
		if (filter_var($this->getUrl(), FILTER_VALIDATE_URL) === FALSE) {
			Cms::getFlashBag()->add('error', 'Niepoprawny format url');
			echo Cms::$twig->render('admin/main.twig');				
		}				
				
		$parse = parse_url($this->getUrl());
		
		if (!in_array($parse['host'], StockUpdateUrl::$availableSites)) {
			Cms::getFlashBag()->add('error', 'Nie obsługiwany host');
			echo Cms::$twig->render('admin/main.twig');				
			return false;
		}			
	}
	
	public function update() {
		$this->init();
		
		$lines = file($this->getUrl());		
//		$lines = file('testupdate.csv');	//for test

		$productsFromFile = [];
		foreach ($lines as $key => $line) {			
			if ($key > 0) {
				$productsFromFile[] = str_getcsv($line, ";");
			}
		}				
		
		$productsFromFile = getArrayByKey($productsFromFile, $this->getEanColumn());	// kod_kreskowy = ean 
		
        $variations = new Variation();
        $variations = $variations->getAll();			
        $variations = getArrayByKey($variations, 'ean'); 

		$toUpdateProducts = [];
		$notUpdatedProducts = [];	//didn`t find ean`s
		foreach ($productsFromFile as $ean => $productFromFile) {
			$qty = $productFromFile[$this->getQtyColumn()];
			
			if (isset($variations[$ean]) && !empty($ean) && $this->isQtyValid($qty)) {
				$toUpdateProducts[$ean] = $productFromFile;								
			} else {				
				$notUpdatedProducts[$ean] = $productFromFile;
			}

		}
		
		$this->updateProductStock($toUpdateProducts);
		$this->setNotUpdatedProducts($notUpdatedProducts);

		echo '<br /><a href="' .  SERVER_URL . '/admin.html">Wróc do poprzedniej strony</a>';
		return ;
	}
	
	protected function isQtyValid($qty) {
		$columnType = $this->getQtyColumnType($qty);

		switch ($columnType) {
			case 'bool':
				if (strtolower($qty) == 'true' || strtolower($qty) == 'false') {	
					return true;
				}
				break;

			default:	
				throw new \Exception(__FUNCTION__ . ': Niezdefiniowany typ columny.');
				break;
		}

		return false;
	}
	
	protected function parseQty($qty) {
		$columnType = $this->getQtyColumnType();
		
		switch ($columnType) {
			case 'bool':
				if (strtolower($qty) == 'true') {					
					return StockUpdateUrl::QTY_TRUE;
				}
				
				if (strtolower($qty) == 'false') {
					return 0;
				}
				
				break;

			default:
				throw new \Exception(__FUNCTION__ . ': Niezdefiniowany typ columny!');
				break;
		}			
	}
	
	protected function updateProductStock(array $toUpdateProducts) {
		if (!$toUpdateProducts) {
			return false;
		}
		
		$variation = new Variation;
		
        try {          
            Cms::$db->beginTransaction(); 
            
            $this->processStockAvailability($toUpdateProducts);
            
			foreach ($toUpdateProducts as $ean => $productFromFile) {
				$qty = $this->parseQty($productFromFile[$this->getQtyColumn()]);

				$item = array(
					'qty' => $qty
				);
								
				$variation->updateByEan($ean, $item);
				echo 'zaktualizowano: '. $ean . '<br />';
			}                        
		
            Cms::$db->commit();            			
        } catch (Exception $e) {
			Cms::$db->rollBack();
			echo "Failed: " . $e->getMessage();
        }				
	}
	
    public function processStockAvailability(array $toUpdateProducts) {
		if (!Cms::$conf['stock_availability']) {			
			return false;
		}                
                
        $eans = array_keys($toUpdateProducts);      

		$q = "SELECT `id2`, `product_id`, `qty`, `ean` FROM `" . DB_PREFIX . 'product_variation' . "` WHERE `ean` IN (" . implode(',', $eans) . ") ";        
		$beforeChangeVariations = Cms::$db->getAll($q);

        $products = getArrayByKey($beforeChangeVariations, 'product_id');
		$productsIds = array_keys($products);
        
        $product = new Products();
        
        $params = array(
            'ids' => $productsIds,
            'limit' => 50000,
            'start' => 0,
            'resultType'	=> 'list',
            'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
        );	

        $products = $product->getBy($params);        
        $products = getArrayByKey($products, 'id');
     
        $nsa = new NotificationsStockAvailability();
        $fields = ['email'];
        
		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate('notifications_stock_availability');		
		
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
        
		$searchTitle = array('#COMPANY_NAME#', '#DOMAIN#', '#PRODUCT#');        
        
		$search = array('#PRODUCT#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
        
        $mailer = new Mailer();

        if ($beforeChangeVariations) {
            foreach ($beforeChangeVariations as $beforeChangeVariation) {
                $qty = $this->parseQty($toUpdateProducts[$beforeChangeVariation['ean']][$this->getQtyColumn()]);
                
                if ($beforeChangeVariation['qty'] == 0 && $qty > 0) {     
                    $replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $products[$beforeChangeVariation['product_id']]['name']);
                    $title = str_replace($searchTitle, $replaceTitle, $template['title']);                     
                    
                    $productUrl = '<a href="' . $products[$beforeChangeVariation['product_id']]['url'] . '">' . $products[$beforeChangeVariation['product_id']]['name'] . '</a>';
                    $replace = array($productUrl, COMPANY_NAME, SERVER_URL, $serverUrl);
                    $content = str_replace($search, $replace, $template['content']);    
                    
                    // wysylanie do klienta                    
                    $mailer->setSubject($title);
                    $mailer->setBody($content);		

                    $params = array(
                        'variation_id' => $beforeChangeVariation['id2']		
                    );        
                    $notifiers = $nsa->findBy($params, $fields);

                    if ($notifiers) {
                        foreach ($notifiers as $notifier) {
                            $mailer->sendHTML($notifier['email']);
                            $mailer->ClearAllRecipients();				
                        }

                        $q = "DELETE FROM `" . DB_PREFIX . "notifications_stock_availability` WHERE `variation_id`='" . (int) $beforeChangeVariation['id2'] . "' ";
                        Cms::$db->delete($q);			
                    }
                }
                
            }
        }
    }      
    
	public function notifyAdminNotUpdated() {
		$notUpdatedProducts = $this->getNotUpdatedProducts();
		
		if (!$notUpdatedProducts) {
			return false;
		}
		
		$products = '<ul>';
		foreach ($notUpdatedProducts as $ean => $productFromFile) {
			$products .= '<li>' . $ean . '</li>';
			echo 'Nie ma takiego ean: '. $ean . '<br />';			
		}
		$products .= '</ul>';		

		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate('notifications_stock_products_not_updated');
		
        //title
		$searchTitle = array('#COMPANY_NAME#', '#DOMAIN#');
		$replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME']);
		$title = str_replace($searchTitle, $replaceTitle, $template['title']);         
        
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
		$search = array('#PRODUCTS#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
		$replace = array($products, Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
		$content = str_replace($search, $replace, $template['content']);		

		// send to admin
		$mailer = new Mailer();
		$mailer->setSubject($title);
		$mailer->setBody($content);		

		$email_array = explode(",", Cms::$conf['email_admin']);
		if (is_array($email_array)) {
			foreach ($email_array as $v) {
				$mailer->sendHTML($v);
				$mailer->ClearAllRecipients();
			}
		}				
	}
	
	public function setEanColumn($eanColumn) {
		$this->eanColumn = $eanColumn;
	}
	
	public function getEanColumn() {
		return $this->eanColumn;
	}
	
	public function setQtyColumn($qtyColumn) {
		$this->qtyColumn = $qtyColumn;
	}
	
	public function getQtyColumn() {
		return $this->qtyColumn;
	}
	
	public function setQtyColumnType($qtyColumnType) {
		$this->qtyColumnType = $qtyColumnType;
	}
	
	public function getQtyColumnType() {
		return $this->qtyColumnType;
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setNotUpdatedProducts(array $notUpdatedProducts) {
		$this->notUpdatedProducts = $notUpdatedProducts;
	}
	
	public function getNotUpdatedProducts() {
		return $this->notUpdatedProducts;
	}	
	
}