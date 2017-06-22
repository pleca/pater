<?php

ini_set('max_execution_time', 600);
use SimpleExcel\SimpleExcel;

/* 2015-10-14 | 4me.CMS 15.3 */
//if ($_SESSION[USER_CODE]['level'] != 1)
//    die('No permission at this level!');

class RunController {    

    private $params;
    private $access;
    private $module;
    private $entity;

	public function init($params = '') {
        $this->importProductsAction();
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
			}
		}
	}
    
	public function setAccess() {
		$this->access = $_SESSION[USER_CODE]['level'] == 1 ? 1 : 0; // Admin moga: dodawac, edytowac, usuwac
	}

	public function setModule() {
		$this->module = $this->getParam('controller') ? $this->getParam('controller') : '';
	}

    public function httpsUpdateProductDescAction() {        
        require_once(MODEL_DIR . '/Product.php');
        
        $entity = new Product();
        $products = $entity->getAll();

        foreach ($products as $product) {
            $product = maddslashes($product);
            $desc  = str_replace("http","https", $product['desc']);    

            $q = "UPDATE " . $entity->table . " SET `desc`='" . $desc . "' ";
            $q.= "WHERE `id`='" . $product['id'] . "' ";    

            Cms::$db->update($q);
        }
        
        redirect(URL . '/admin.html');
    }
    
    public function exportProductsAction() {
        require_once(MODEL_DIR . '/shopProducersAdmin.php');
        require_once(MODEL_DIR . '/Category.php');
        require_once(MODEL_DIR . '/Status.php');
        require_once(MODEL_DIR . '/Tax.php');
        require_once(SYS_DIR . '/libraries/SimpleExcel/SimpleExcel.php'); // load the main class file (if you're not using autoloader)
        
        $producersAdmin = new ProducersAdmin();
        $producers = $producersAdmin->loadAdmin();        
        $producers = getArrayByKey($producers, 'id');
        
        $category = new Category();
        $categories = $category->getAll(true); 

        $status = new Status();
        $statuses = $status->getAll(true); 

        $products = $this->getProducts();
        
        $tax = new TaxModel();
        $taxes = $tax->getAll(); 
        $taxes = getArrayByKey($taxes, 'id');

        $excel = new SimpleExcel('csv');                    // instantiate new object (will automatically construct the parser & writer type as CSV)
        $data = [];
        $headers1 = ['','','','','',
            '','','','','',
            '','','','IMAGES','',
            '','FEATURES','','','',
            'VARIATION','','','','',
            '','','','','',
            '','','','','',
            '','','',''];
        
        $data[] = $headers1;
        $headers2 = ['SKU', 'NAME', 'MANUFACTURER', 'CATEGORY', 'STATUS', 
            'TYPE', 'DESC', 'DESC_SHORT', 'TAG1', 'TAG2', 
            'TAG3', 'DATE_ADD', 'DATE_MOD', 'IMAGE1', 'IMAGE2', 
            'IMAGE3', 'FEATURE1_NAME', 'FEATURE2_NAME', 'FEATURE3_NAME', 'PARENTAGE', 
            'TAX', 'PRICE_PURCHASE', 'PRICE_RRP', 'PRICE', 'PRICE2', 
            'PRICE3', 'PRICE_PROMOTION', 'PROMOTION', 'BESTSELLER', 'RECOMMENDED', 
            'MAIN_PAGE', 'MEGA_OFFER', 'WEIGHT', 'QTY', 'DATE_PROMOTION', 
            'FEATURE1_VALUE', 'FEATURE2_VALUE', 'FEATURE3_VALUE', 'EXPORTED_DOMAIN'];

        $data[] = $headers2;
                
        foreach ($products as $product) {
            $row = $this->getProductRow($product, $producers, $categories, $statuses);
            $data[] = $row;
            
            if (isset($product['variations'])) {
                foreach ($product['variations'] as $variation) {
                    $row = $this->getVariationRow($variation, $taxes);    
                    $data[] = $row;
                }
            }
        }
        
        $excel->writer->setData($data);
//        $excel->writer->setDelimiter(";");                  // (optional) if delimiter not set, by default comma (",") will be used instead
        $excel->writer->saveFile('products-' . date('YmdHis'));      
        die;
    }
    
    protected function getProductRow($product, $producers, $categories, $statuses) {
        $row[0] = '';
        $row[1] = $product['name'];
        $row[2] = isset($producers[$product['producer_id']]) ? $producers[$product['producer_id']]['name'] : '';
        $row[3] = isset($categories[$product['category_id']]) ? $categories[$product['category_id']]['name'] : '';
        $row[4] = isset($statuses[$product['status_id']]) ? $statuses[$product['status_id']]['name']: '';
        $row[5] = $product['type'];
        $row[6] = $product['desc'];
        $row[7] = $product['desc_short'];
        $row[8] = $product['tag1'];
        $row[9] = $product['tag2'];
        $row[10] = $product['tag3'];
        $row[11] = $product['date_add'];
        $row[12] = $product['date_mod'];
        
        for ($i = 0; $i <= 2; $i++) {
            $row[] = isset($product['images'][$i]) ? $product['images'][$i]['file'] : '';
        }
        
        $row[16] = $product['feature1_name'];
        $row[17] = $product['feature2_name'];
        $row[18] = $product['feature3_name'];        

        $row[19] = 'Parent';
        for( $i = 20; $i < 38; $i++) {
            $row[$i] = '';
        }
        
        $row[38] = SERVER_URL;
        
        return $row;            
    }    
        
    protected function getVariationRow($variation, $taxes) {
        $row[0] = $variation['sku'];
        $row[1] = '';
        $row[2] = '';
        $row[3] = '';
        $row[4] = '';
        $row[5] = '';
        $row[6] = '';
        $row[7] = '';
        $row[8] = '';
        $row[9] = '';
        $row[10] = '';
        $row[11] = '';
        $row[12] = '';

        for ($i = 0; $i <= 2; $i++) {
            $row[] = isset($variation['images'][$i]) ? $variation['images'][$i]['file'] : '';
        }
        
        $row[16] = $variation['feature1_value'];
        $row[17] = $variation['feature2_value'];
        $row[18] = $variation['feature3_value'];         

        $row[19] = 'Child';
        $row[20] = $taxes[$variation['tax_id']]['value']; //get or create
        $row[21] = $variation['price_purchase']; 
        $row[22] = $variation['price_rrp']; 
        $row[23] = $variation['price']; 
        $row[24] = $variation['price2']; 
        $row[25] = $variation['price3']; 
        $row[26] = $variation['price_promotion']; 
        $row[27] = $variation['promotion']; 
        $row[28] = $variation['bestseller']; 
        $row[29] = $variation['recommended']; 
        $row[30] = $variation['main_page']; 
        $row[31] = $variation['mega_offer']; 
        $row[32] = $variation['weight']; 
        $row[33] = $variation['qty']; 
        $row[34] = $variation['date_promotion']; 
        $row[35] = $variation['feature1_value']; 
        $row[36] = $variation['feature2_value']; 
        $row[37] = $variation['feature3_value']; 
        $row[38] = SERVER_URL;
        
        return $row;            
    }    

    public function importProductsAction() {
        require_once(MODEL_DIR . '/Status.php');
        require_once(MODEL_DIR . '/shopProducersAdmin.php');
        require_once(MODEL_DIR . '/Category.php');        
        require_once(MODEL_DIR . '/Product.php');        
        require_once(MODEL_DIR . '/Variation.php');        
        require_once(MODEL_DIR . '/../entity/Tax.php');
        require_once(CLASS_DIR . '/ImportExportCsv/CsvImporter.php');
//		echo 'skrypt wylaczony poniewaz wymaga aktualizacji...';
        $importer = new \Application\Classes\ImportExportCsv\CsvImporter(EXP_DIR . '/products.csv');                    //nie widzi bez całej ścieżki, mimo że jest require.
        $array = $importer->get();
//		die;
		$data = array(
			'pageTitle'	=> $GLOBALS['LANG']['panel_cms'],
		);					
		
//        if (!isset($_FILES['products']) OR $_FILES['products']['error']) {
//			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['Błąd importu pliku.']);
//			echo Cms::$twig->render('admin/main.twig', $data);
//            return false;
//        }
//
//        $row = 1;
//        $array = [];
//        if (($handle = fopen($_FILES['products']['tmp_name'], "r")) !== FALSE) {
//            $delimiter = $this->guessDelimiter(file_get_contents($_FILES['products']['tmp_name']));
//
//            while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
//                $num = count($data);
//                $items = [];
//                $i = 0;
//
//                for ($c=0; $c < $num; $c++) {
//                    $items[$c] = strip_tags($data[$c]);
//                }
//
//                $array[$row] = $items;
//                $row++;
//
//            }
//            fclose($handle);
//        }
        
//        $status = new Status();
//        $statuses = $status->getAll();
//        $statuses = getArrayByKey($statuses, 'name');



        $producersAdmin = new ProducersAdmin();
        $producers = $producersAdmin->loadAdmin();        
        $producers = getArrayByKey($producers, 'name');
        //Poniżej print_r częściowy, czyli zamienia tablicę numeryczną [0] producentów na coś takiego [Trec Nutrition]
        /**
         * Array
        (
        [Trec Nutrition] => Array
        (
        [id] => 1
        [status_id] => 2
        [name] => Trec Nutrition
        [name_url] => trec-nutrition
        [file] =>
        [popular] => 1
        [order] => 1
        [url] => http://pattern.dev/producers/trec-nutrition.html
        )

        [Trec Wear] => Array
        (
        [id] => 2
        [status_id] => 2
        [name] => Trec Wear
        [name_url] => trec-wear
        [file] =>
        [popular] => 1
        [order] => 2
        [url] => http://pattern.dev/producers/trec-wear.html
        )
         *
         */

//        $category = new Category();
//        $categories = $category->getAll();
//        $categories = getArrayByKey($categories, 'name');
//
//        $products = new Product();
//        $products = $products->getAll();
//        $products = getArrayByKey($products, 'name');
//
//        $variations = new Variation();
//        $variations = $variations->getAll();
//        $variations = getArrayByKey($variations, 'sku');
//
//        $tax = new TaxModel();
//        $taxes = $tax->getAll();
//        $taxes = getArrayByKey($taxes, 'value');

        try {          
            Cms::$db->beginTransaction();      
            $lastProduct = null;
            foreach ($array as $key => $row) {
                if ($key > 2) {                        
                    switch ($row['19']) {            
                        case 'Parent':
//                            $status = $row[4];
//                            if (!array_key_exists($status, $statuses)) {
//                                $this->createStatus($status, $statuses);
//                            }

                            $producer = $row[2];
                            if (!array_key_exists($producer, $producers)) {
                                $this->createProducer($producer, $producers, $statuses, $status);
                            }

//                            $category = $row[3];
//                            if (!array_key_exists($category, $categories)) {
//                                $this->createCategory($category, $categories, $statuses, $status);
//                            }
//
//                            $product = $row[1];
//
//                            if (!array_key_exists($product, $products)) {
//                                $this->createProduct($row, $statuses, $producers, $categories, $products);
//                            }
//
//                            $lastProduct = $product;

                            break;
                        case 'Child':
                            $tax = $row[20];
                            if (!array_key_exists($tax, $taxes)) {
                                $this->createTax($tax, $taxes);
                            } 

                            $variation = $row[0];
                            if (!array_key_exists($variation, $variations)) {
                                $this->createVariation($row, $products, $variations, $lastProduct, $taxes);
                            }
                            break;
                        default:
                            throw new \Exception('Unknown parentage param: ' . $row['19']);
                            break;
                    }
                }
            }
            Cms::$db->commit();
            echo '<br /><a href="' .  SERVER_URL . '/admin.html">Wróc do poprzedniej strony</a>';

        } catch (Exception $e) {
          Cms::$db->rollBack();
          echo "Failed: " . $e->getMessage();
        }

die;

    }
    
    protected function createStatus($name, &$statuses) {
        if ($_POST['status']) {
            return false;
        }
        echo 'utworzono nową kategorię: ' . $name . '<br />';
        require_once(MODEL_DIR . '/Status.php');
        $entity = new Status();

        $item['name'] = $name;
        $item['order'] = $entity->getMaxOrder()[0] + 1;
        $statusId = $entity->set($item);
        
        $data = [];
        $data['id'] = $statusId;
        $data['name'] = $name;
        $statuses[$name] = $data;
    }
    
    protected function createProducer($name, &$producers, $statuses, $status) {
        if (!$name) {
            return false;
        }
        
        echo 'utworzono nowego producenta: ' . $name . '<br />';
        require_once(MODEL_DIR . '/shopProducersAdmin.php');
        $entity = new ProducersAdmin();

        if ($_POST['status']) {            
            $status = $statuses[$_POST['status']];
        } else {
            $status = $statuses[$status];
        }        
        
        $item = [];
        $item['name'] = $name;
        $item['status_id'] = $status['id'];
        $item['popular'] = 0;
        
        $producerId = $entity->addAdmin($item);
        
        $data = [];
        $data['id'] = $producerId;
        $producers[$name] = $data;
    }
    
    protected function createCategory($name, &$categories, $statuses, $status) {
        if (!$name) {
            return false;
        }
        
        echo 'utworzono nową kategorię: ' . $name . '<br />';
        require_once(MODEL_DIR . '/shopCategoriesAdmin.php');
        $entity = new CategoriesAdmin();

        if ($_POST['status']) {            
            $status = $statuses[$_POST['status']];
        } else {
            $status = $statuses[$status];
        }        
        
        $item = [];
        $item['name'] = $name;
        $item['status_id'] = $status['id'];   
        $item['parent_id'] = 0;   
        $id = $entity->addAdmin($item);     
        
        $data = [];
        $data['id'] = $id;
        $categories[$name] = $data;
    }
    
    protected function createTax($tax, &$taxes) {
        if (!$tax) {
            return false;
        }
        
        echo 'utworzono nowy tax: ' . $tax . '<br />';
        require_once(MODEL_DIR . '/Tax.php');
        $entity = new TaxModel();  
        
        $item = [];
        $item['value'] = $tax;
        $item['order'] = $entity->getMaxOrder()[0] + 1;

        $id = $entity->set($item);
        
        $data = [];
        $data['id'] = $id;
        $data['value'] = $tax;
        $taxes[$tax] = $data;        
    }      
    
    protected function createProduct($row, $statuses, $producers, $categories, &$products) {
        if (!isset($row[1])) {
            return false;
        }
        
        echo 'utworzono nowy produkt: ' . $row[1] . '<br />';
        require_once(MODEL_DIR . '/shopProductsAdmin.php');
        $entity = new ProductsAdmin();
        
        if ($_POST['status']) {            
            $statusId = $statuses[$_POST['status']]['id'];
        } else {
            $statusId = isset($statuses[$row['4']]) ? $statuses[$row['4']]['id'] : 0;
        }          
        
        $item = [];
        $item['name'] = $row[1];
        $item['category_id'] = isset($categories[$row[3]]) ? $categories[$row[3]]['id'] : 0;   
        $item['producer_id'] = isset($producers[$row[2]]) ? $producers[$row[2]]['id'] : 0;   
        $item['status_id'] = $statusId;  
        $item['type'] = isset($row['5']) ? $row['5'] : 1;
        $item['desc'] = isset($row['6']) ? $row['6'] : '';
                
        $id = $entity->addAdmin($item); 
        
        $item2 = [];
        $item2['id'] = $id;
        $item2['desc_short'] = isset($row['7']) ? $row['7'] : '';
        $item2['feature1_name'] = isset($row['16']) ? $row['16'] : '';
        $item2['feature2_name'] = isset($row['17']) ? $row['17'] : '';
        $item2['feature3_name'] = isset($row['18']) ? $row['18'] : '';
        $item2['tag1'] = isset($row['8']) ? $row['8'] : '';
        $item2['tag2'] = isset($row['9']) ? $row['9'] : '';
        $item2['tag3'] = isset($row['10']) ? $row['10'] : '';
        
        $entity->expandedAdmin($item2); 
        
        $data = [];
        $data['id'] = $id;
        $products[$row[1]] = $data;
        
        //copy products images
        $this->createImages($row, $data, $entity);
    }
    
    protected function createImages($row, $data) {
        require_once(MODEL_DIR . '/shopProductsAdmin.php');
        $entity = new ProductsAdmin();        
        
        if (isset($row[13]) && !empty($row[13])) {     
            echo 'zapisano zdjecia produktu<br />';
            $files = [];            
            $file1 = getFileFormat($row['38'] . Product::IMAGE_DIR . $row[13]);
            $file1['local'] = 1;
            $files[] = $file1;
            
            if ($row[14]) {
                $file2 = getFileFormat($row['38'] . Product::IMAGE_DIR . $row[14]);
                $file2['local'] = 1;
                $files[] = $file2;
            }
            
            if ($row[15]) {
                $file3 = getFileFormat($row['38'] . Product::IMAGE_DIR . $row[15]);
                $file3['local'] = 1;
                $files[] = $file3;
            }
            
            switch($row[19]) {
                case 'Parent':
                    $entity->imageAdmin($data, $files);
                    break;
                case 'Child':
                    $entity->variationImageAdmin($data, $files);
                    break;
            }    
        }        
    }
  
    
    protected function createVariation($row, $products, &$variations, $lastProduct, $taxes) {
        if (!isset($row[0])) {
            return false;
        }
        
        echo 'utworzono nowa wariację: ' . $row[0] . '<br />';
        require_once(MODEL_DIR . '/Variation.php');
        $entity = new Variation();
        
        $item = [];
        $item['product_id'] = $products[$lastProduct]['id'];
        $item['tax_id'] = isset($taxes[$row[20]]['id']) ? $taxes[$row[20]]['id'] : 0;
        $item['sku'] = $row[0];
        $item['price_purchase'] = isset($row[21]) ? $row[21] : 0;
        $item['price_rrp'] = isset($row[22]) ? $row[22] : 0;
        $item['price'] = isset($row[23]) ? $row[23] : 0;
        $item['price2'] = isset($row[24]) ? $row[24] : 0;
        $item['price3'] = isset($row[25]) ? $row[25] : 0;
        $item['price_promotion'] = isset($row[26]) ? $row[26] : 0;
        $item['promotion'] = isset($row[27]) ? $row[27] : 0;
        $item['bestseller'] = isset($row[28]) ? $row[28] : 0;
        $item['recommended'] = isset($row[29]) ? $row[29] : 0;
        $item['main_page'] = isset($row[30]) ? $row[30] : 0;
        $item['mega_offer'] = isset($row[31]) ? $row[31] : 0;
        $item['weight'] = isset($row[32]) ? $row[32] : 0;
        $item['qty'] = isset($row[33]) ? $row[33] : 0;
        $item['date_promotion'] = isset($row[34]) ? $row[34] : '';
        $item['feature1_value'] = isset($row[35]) ? $row[35] : '';
        $item['feature2_value'] = isset($row[36]) ? $row[36] : '';
        $item['feature3_value'] = isset($row[37]) ? $row[37] : '';

        $id = $entity->set($item);
        
        $item['id2'] = $id;
        $variations[$row[0]] = $item;
        
        $data = [];
        $data['id'] = $item['product_id'];
        $data['variation_id'] = $id;
        //copy products images
        $this->createImages($row, $data);        
    }


    protected function addVariationsToProducts(&$products) {
        require_once(MODEL_DIR . '/Variation.php');        
        
        $variation = new Variation();
        $variations = $variation->getAll();
        foreach ($products as $key => $product) {
            foreach ($variations as $variation) {
                if ($product['id'] == $variation['product_id']) {
                    $products[$key]['variations'][$variation['id2']] = $variation;
                }
            }
        }        
    }
    
    protected function addImagesToProducts(&$products) {
        require_once(MODEL_DIR . '/ProductImage.php'); 
        
        $productImage = new ProductImage();
        $productImages = $productImage->getAll();

        foreach ($products as $key => $product) {
            foreach ($productImages as $image) {
                if ($product['id'] == $image['product_id']) {
                    if ($image['variation_id']) {
                        $products[$key]['variations'][$image['variation_id']]['images'][] = $image;
                    } else {
                        $products[$key]['images'][] = $image;
                    }
                    
                }
            }      
        }             
    }
    
    protected function getProducts() {
        require_once(MODEL_DIR . '/shopProductsAdmin.php');
        $filtr = [];
        $filtr['limit'] = 100000;
        $filtr['start'] = 0;
        $filtr['id'] = '';
        $filtr['name'] = '';
        $filtr['category_id'] = 0;
        $filtr['producer_id'] = 0;        
        
        $oProductsAdmin = new ProductsAdmin();
        $products = $oProductsAdmin->loadAdmin($filtr);
        
        $this->addVariationsToProducts($products);
        $this->addImagesToProducts($products);
        
        return $products;
    }    
    
    protected function getSkuList(array $products) {
        if (!$products) {
            return false;
        }
        
        foreach ($products as $product) {
            if (isset($product['variations'])) {
                
            }
        }
    }
    
    function arrayToXml($data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
//                    $key = 'item';
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
    
    protected function guessDelimiter($str) {
		$pattern = "/\r\n|\n|\r/";
		$lines   = preg_split($pattern, $str, -1, PREG_SPLIT_NO_EMPTY);
		$line = $lines[0];

        $separators = array(';' => 0, ',' => 0);
        foreach ($separators as $sep => $count) {
            $args  = str_getcsv($sep, $line);
            $count = count($args);

            $separators[$sep] = $count;
        }

        $sep = ',';
        if (($separators[';'] > $separators[','])) {
            $sep = ';';
        }

        return $sep;  
    }    
    
	public function updateStockAction() {
        require_once(MODEL_DIR . '/Variation.php');        
        require_once(CLASS_DIR . '/Stock/StockUpdate.php');        
        require_once(CLASS_DIR . '/Stock/StockUpdateUrl.php');        
        require_once(CLASS_DIR . '/Stock/StockUpdateCsv.php');        
                
		$source = $_POST['source'];
		
		if (!$source) {
			Cms::getFlashBag()->add('error', 'Wybierz źródło dla aktualizacji stanów magazynowych!');
			echo Cms::$twig->render('admin/main.twig');
			die;
		}
		
		$stockUpdate = new StockUpdate();

		switch ($source) {
			case 'url':
				$stockUpdate->setStrategy(new StockUpdateUrl);
				break;
			case 'csv':
				$stockUpdate->setStrategy(new StockUpdateCsv);
				break;
			default:
				break;
		}
		
		$stockUpdate->getStrategy()->update();
		$stockUpdate->getStrategy()->notifyAdminNotUpdated();
	}
	
	protected function createNewCategory($entity) {
		require_once(MODEL_DIR . '/Category.php');
		
		$category = new Category();
		
		$item = array(						
			'parent_id' => 0,
			'status_id' => $entity['status_id'],
			'order'		=> $entity['order'],
			'old_category_id' => $entity['id']
		);

		$parentId = $category->insert('categories', $item);		
		
		$translation = array(
			'translatable_id' => $parentId,					
			'name' => $entity['name'],					
			'slug' => $entity['name_url']
		);
		
		foreach (Cms::$langs as $lang) {
			$translation['locale'] = $lang['code'];
			$category->insert('categories_translation', $translation);
		}
		
		if ($entity['submenu']) {
			foreach ($entity['submenu'] as $row) {
				$item = array(						
					'parent_id' => $parentId,
					'status_id' => $row['status_id'],
					'order'		=> $row['order'],
					'old_category_id' => $row['id']
				);

				$subId = $category->insert('categories', $item);
				
				$translation = array(
					'translatable_id' => $subId,					
					'name' => $row['name'],					
					'slug' => $row['name_url']
				);
		
				foreach (Cms::$langs as $lang) {
					$translation['locale'] = $lang['code'];
					$category->insert('categories_translation', $translation);
				}				
			}
		}		
	}
	
	protected function updateProductsCategory() {
        require_once(MODEL_DIR . '/Product.php');
		require_once(MODEL_DIR . '/Category.php');
				       		
        $entity = new Product();
        $products = $entity->getAll();

		$category = new Category(); 
		$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
		$categories = $category->getAll(['locale' => $locale]);
		$categories = getArrayByKey($categories, "old_category_id");

        foreach ($products as $product) {
			if (isset($categories[$product['category_id']])) {
				$catNew = $categories[$product['category_id']];

				$q = "UPDATE `product` SET `category_id`='" . $catNew['id'] . "' ";
				$q.= "WHERE `id`='" . $product['id'] . "' ";    
				
				Cms::$db->update($q);
			} else {
				echo 'brak: ' . $product['category_id'] . '<br />';
				dump($product);
			}
        }
	}
	
	public function mapCategoriesAction() {
		require_once(MODEL_DIR . '/shopCategories.php');				
		$GLOBALS['LANG']['url_shop'] = 'shop';
		
		$categories = new CategoriesOld();
		$entities = $categories->getAll(1);

		if ($entities) {
			foreach ($entities as $entity) {
				$this->createNewCategory($entity);
			}
		}
		
		$this->updateProductsCategory();

	}
	
	public function mapProductsAction() {	
		require_once(MODEL_DIR . '/Feature.php');
		require_once(MODEL_DIR . '/shopProducts.php');
		
		$product = new Products;
		$products = $product->getAll();

		if ($products) {
			foreach ($products as $entity) {
				$this->createNewProductTranslation($entity);
			}

			$features = $product->getFeatures();
			
			if ($features) {
				foreach ($features as $feature) {

					$q = "INSERT INTO `features` VALUES(null)";
					$id = Cms::$db->insert($q);
		
					$translation = array(
						'translatable_id' => $id,					
						'name' => $feature,					
					);
		
					foreach (Cms::$langs as $lang) {
						$translation['locale'] = $lang['code'];
						$product->insert('features_translation', $translation);
					}						
				}												
			}					
			
			$this->updateProductsFeatures($products);
		}	
	}
	
	protected function updateProductsFeatures($products) {
		require_once(MODEL_DIR . '/Feature.php');
		require_once(MODEL_DIR . '/shopProducts.php');
		
		$product = new Products;		
		$feature = new Feature();
		$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
		$features = $feature->getAll(['locale' => $locale]);
		$features = getArrayByKey($features, "name");
		
		if ($products && $features) {
						
			foreach ($products as $entity) {
				$item = [];
				$doUpdate = false;
				
				if ($entity['feature1_name'] && isset($features[$entity['feature1_name']])) {
					$featureEntity = $features[$entity['feature1_name']];
					$item['feature1_id'] = $featureEntity['id'];
					$doUpdate = true;
				}
				
				if ($entity['feature2_name'] && isset($features[$entity['feature2_name']])) {
					$featureEntity = $features[$entity['feature2_name']];
					$item['feature2_id'] = $featureEntity['id'];
					$doUpdate = true;
				}
				
				if ($entity['feature3_name'] && isset($features[$entity['feature3_name']])) {
					$featureEntity = $features[$entity['feature3_name']];
					$item['feature3_id'] = $featureEntity['id'];
					$doUpdate = true;
				}
				
				if ($doUpdate) {
					$where = $product->where(["id" => $entity['id']]);		
					$product->update('product', $where, $item);		
				}
			}
		}
	}	
	
	public function createNewProductTranslation($entity) {
		require_once(MODEL_DIR . '/shopProducts.php');
		
		$product = new Products;

		$translation = array(
			'translatable_id' => $entity['id'],					
			'content' => $entity['desc'],					
			'content_short' => $entity['desc_short']
		);
		
		foreach (Cms::$langs as $lang) {
			$translation['locale'] = $lang['code'];
			$product->insert('product_translation', $translation);
		}		
	}
	
	public function mapFeatureValuesAction() {
		require_once(MODEL_DIR . '/Product.php');
		require_once(MODEL_DIR . '/Variation.php');
		require_once(MODEL_DIR . '/FeatureValue.php');
		
        $product = new Product();
        $products = $product->getAll(); 
        $products = getArrayByKey($products, 'id');   		
		
		$variation = new Variation();
		$variations = $variation->getAll();
		
		$featureValuesToCreate = [];
		
		if ($variations) {
			foreach ($variations as $variation) {
				if (isset($products[$variation['product_id']]['feature1_id']) && $variation['feature1_value']) {
					$featureValuesToCreate[$products[$variation['product_id']]['feature1_id']][] = $variation['feature1_value'];
				}
				
				if (isset($products[$variation['product_id']]['feature2_id']) && $variation['feature2_value']) {
					$featureValuesToCreate[$products[$variation['product_id']]['feature2_id']][] = $variation['feature2_value'];
				}
				
				if (isset($products[$variation['product_id']]['feature3_id']) && $variation['feature3_value']) {
					$featureValuesToCreate[$products[$variation['product_id']]['feature3_id']][] = $variation['feature3_value'];
				}
			}
		}

		foreach ($featureValuesToCreate as &$row) {
			$row = array_unique(array_filter($row));
		}
		
		$this->createFeatureValues($featureValuesToCreate);
		$this->updateProductsFeatureValues($variations);

		echo 'Mapowanie cech zakonczone';
		die;
	}
	
	protected function createFeatureValues(array $features = []) {
		
		if (!$features) {
			return false;
		}

		$featureValue = new FeatureValue();
		
		foreach ($features as $featureId => $featureValues) {
			
			$item = array(
				'feature_id' => $featureId
			);
					
			if ($featureValues) {
				foreach ($featureValues as $value) {
					$id = $featureValue->insert('feature_values', $item);

					$translation = array(
						'translatable_id' => $id,			
						'name' => $value,
					);

					foreach (Cms::$langs as $lang) {
						$translation['locale'] = $lang['code'];
						$featureValue->insert('feature_values_translation', $translation);
					}				
				}
			}								
		}
		
	}
	
	protected function updateProductsFeatureValues($variations) {
		$featureValue = new FeatureValue();
		$featureValues = $featureValue->getAll(['locale' => Cms::$defaultLocale]);
		$featureValues = getArrayByKey($featureValues, 'name');
		
		$variation = new Variation();

		if ($variations) {
			foreach ($variations as $row) {
				if ($row['feature1_value'] || $row['feature2_value'] || $row['feature3_value']) {
					
					$item = array(
						'feature1_value_id' => isset($featureValues[$row['feature1_value']]['id']) ? $featureValues[$row['feature1_value']]['id'] : 0,
						'feature2_value_id' => isset($featureValues[$row['feature2_value']]['id']) ? $featureValues[$row['feature2_value']]['id'] : 0,
						'feature3_value_id' => isset($featureValues[$row['feature3_value']]['id']) ? $featureValues[$row['feature3_value']]['id'] : 0
					);

					$where = $variation->where(["id2" => $row['id2']]);
					$variation->update('product_variation', $where, $item);						
				}
			}
		}
	}
	
	public function migrateCustomersAction() {
		echo 'Customers migration....<br />';        
		
		try {
			Cms::$db->beginTransaction(); 

			$q = "SELECT `email` FROM `" . DB_PREFIX . "customer`";
			$array = Cms::$db->getAll($q);
			$emails = [];
			foreach ($array as $row) {
				$emails[] = $row['email'];
			}

			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}

			$q = "SELECT * FROM `" . DB_PREFIX . "customerMIGRATE`";
			$array = Cms::$db->getAll($q);

			foreach ($array as $row) {
				if (!in_array($row['email'], $emails)) {
					$row = maddslashes($row);
					$q = "INSERT INTO `" . DB_PREFIX . 'customer' . "` SET `login`='" . $row['login'] . "', `pass`='', ";
					$q.= "`first_name`='" . $row['first_name'] . "', `last_name`='" . $row['last_name'] . "', `email`='" . $row['email'] . "', ";
					$q.= "`company_name`='" . $row['company_name'] . "', `nip`='" . $row['nip'] . "', `address1`='" . $row['address1'] . "', ";
					$q.= "`address2`='" . $row['address2'] . "', `address3`='" . $row['address3'] . "', `post_code`='" . $row['post_code'] . "', ";
					$q.= "`city`='" . $row['city'] . "', `province`='" . $row['province'] . "', `country`='" . $row['country'] . "', ";
					$q.= "`phone`='" . $row['phone'] . "', `discount`='" . $row['discount'] . "', `price_group`='1', ";								
					$q.= "`type`=1, `active`='" . $row['active'] ."', `date_add`='" . $row['date_add']."' ";

					foreach (Cms::$langs as $key => $lang) {
						if ($lang['code'] == $row['lang']) {
							$langId = $key;
							break;
						}
					}

					if (Cms::$db->insert($q)) {
						if (!in_array($row['email'], $newsletterEmails)) {
							$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $row['first_name'] . "', `last_name`='" . $row['last_name'] . "', ";
							$q.= "`email`='" . $row['email'] . "', `lang_id`='" . $langId . "', `active`='1' ";
							Cms::$db->insert($q);
						}
					}				
				}
			}

			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}		

			$q = "SELECT * FROM `" . DB_PREFIX . "newsletter_usersMIGRATE`";
			$array = Cms::$db->getAll($q);	
			foreach ($array as $row) {
				if (!in_array($row['email'], $newsletterEmails)) {
					$row = maddslashes($row);
					
					$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $row['first_name'] . "', `last_name`='" . $row['last_name'] . "', ";
					$q.= "`email`='" . $row['email'] . "', `lang_id`='" . $row['lang_id'] . "', `active`='1' ";
					Cms::$db->insert($q);
				}
			}
			
			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}			
			
			$q = "SELECT `first_name`, `last_name`, `email`,`lang_id` FROM `" . DB_PREFIX . "orderMIGRATE`";
			$array = Cms::$db->getAll($q);		
			foreach ($array as $row) {
				if (!in_array($row['email'], $newsletterEmails)) {
					$row = maddslashes($row);
					
					$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $row['first_name'] . "', `last_name`='" . $row['last_name'] . "', ";
					$q.= "`email`='" . $row['email'] . "', `lang_id`='" . $row['lang_id'] . "', `active`='1' ";
					Cms::$db->insert($q);
				}
			}			
			
            Cms::$db->commit();
            echo '<br /><a href="' .  SERVER_URL . '/admin.html">Wróc do poprzedniej strony</a>';

        } catch (Exception $e) {
          Cms::$db->rollBack();
          echo "Failed: " . $e->getMessage();
        }
	}
    
    //only sote kazde zakodowane pole mozna rozszyfrowac ponizsza funkcja
    
    public static function Decrypt($string) { // funkcja z SOTE
        $key = '9955bdd4e0e58b6ab45a7c3bcddacf20';
        $string = base64_decode($string);

        /* Open module, and create IV */
        $td = mcrypt_module_open('des', '', 'cfb', '');
        $key = substr($key, 0, mcrypt_enc_get_key_size($td));
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv = substr($string, 0, $iv_size);
        $string = substr($string, $iv_size);
        /* Initialize encryption handle */
        if (mcrypt_generic_init($td, $key, $iv) != -1) {
            /* Encrypt data */
            $c_t = @mdecrypt_generic($td, $string);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            return $c_t;
        } //end if
    }
    
	public function migrateSoteCustomersAction() {
		echo 'Customers sote migration....<br />';
		
		try {
			Cms::$db->beginTransaction(); 

			$q = "SELECT `email`, `login` FROM `" . DB_PREFIX . "customer`";
			$array = Cms::$db->getAll($q);
			$emails = [];
			$logins = [];
			foreach ($array as $row) {
				$emails[] = $row['email'];
				$logins[] = $row['login'];
			}

			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}

			$q = "SELECT * FROM `" . DB_PREFIX . "customerMIGRATE`";
			$customersToMigrate = Cms::$db->getAll($q);
            $customersToMigrate = getArrayByKey($customersToMigrate, 'id');
            
			$q = "SELECT * FROM `" . DB_PREFIX . "customer_dataMIGRATE` WHERE `is_billing` = 0 AND `is_default`=1";
			$customersToMigrateData = Cms::$db->getAll($q);
            $customersToMigrateData = getArrayByKey($customersToMigrateData, 'sf_guard_user_id');

            $customerNames = [];
            $customerNames[0]['firstName'] = '';
            $customerNames[0]['lastName'] = '';
            
			foreach ($customersToMigrate as $id => $row) {
                $row['username'] = lcfirst($row['username']);
                
				if (!in_array($row['username'], $emails) && !in_array($row['username'], $logins)) {
					$row = maddslashes($row);
                    
                    $firstName = '';
                    $lastName = '';
                    $customerNames[$id]['firstName'] = '';
                    $customerNames[$id]['lastName'] = '';

                    if (isset($customersToMigrateData[$id])) {
                        $name = explode(' ', $customersToMigrateData[$id]['full_name']);
                        $firstName = isset($name[0]) ? $name[0] : ''; 
                        $lastName = isset($name[1]) ? $name[1] : ''; 
                        $customerNames[$id]['firstName'] = $firstName;
                        $customerNames[$id]['lastName'] = $lastName;    
                    }

                    $address = '';
                    $code = '';
                    $city = '';
                    $phone = '';
                    $company = '';
                    $nip = '';
                    if (isset($customersToMigrateData[$id])) {
                        $address = maddslashes($this->Decrypt($customersToMigrateData[$id]['address']));
                        $code = $this->Decrypt($customersToMigrateData[$id]['code']);
                        $city = maddslashes($this->Decrypt($customersToMigrateData[$id]['town']));
                        $phone = maddslashes($this->Decrypt($customersToMigrateData[$id]['phone']));                        
                        $company = $customersToMigrateData[$id]['company'];                        
                        $nip = $customersToMigrateData[$id]['vat_number'];                      
                    }

					$q = "INSERT INTO `" . DB_PREFIX . 'customer' . "` SET `login`='" . $row['username'] . "', `pass`='', ";
					$q.= "`first_name`='" . $firstName . "', `last_name`='" . $lastName . "', `email`='" . $row['username'] . "', ";
					$q.= "`company_name`='" . $company . "', `nip`='" . $nip . "', `address1`='" . $address . "', ";
					$q.= "`address2`='', `address3`='', `post_code`='" . $code . "', ";
					$q.= "`city`='" . $city . "', `province`='', `country`='1', ";
					$q.= "`phone`='" . $phone . "', `discount`='0.00', `price_group`='1', ";								
					$q.= "`type`='1', `active`='1', `date_add`=NOW() ";

					if (Cms::$db->insert($q)) {
						if (!in_array($row['username'], $newsletterEmails)) {
							$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $firstName . "', `last_name`='" . $lastName . "', ";
							$q.= "`email`='" . $row['username'] . "', `lang_id`='1', `active`='1' ";
							Cms::$db->insert($q);
						}
					}				
				}
			}

			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}		

			$q = "SELECT * FROM `" . DB_PREFIX . "newsletterMIGRATE`";
			$array = Cms::$db->getAll($q);
            
			foreach ($array as $row) {
				if (!in_array($row['email'], $newsletterEmails)) {
					$row = maddslashes($row);
					
                    
                    $firstName = isset($customerNames[$row['sf_guard_user_id']]) ? $customerNames[$row['sf_guard_user_id']]['firstName'] : '';
                    $lastName = isset($customerNames[$row['sf_guard_user_id']]) ? $customerNames[$row['sf_guard_user_id']]['lastName'] : '';
                                        
					$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $firstName . "', `last_name`='" . $lastName . "', ";
					$q.= "`email`='" . $row['email'] . "', `lang_id`='1', `active`='1' ";
					Cms::$db->insert($q);
				}
			}
			
			$q = "SELECT `email` FROM `" . DB_PREFIX . "newsletter_users`";
			$array = Cms::$db->getAll($q);
			$newsletterEmails = [];
			foreach ($array as $row) {
				$newsletterEmails[] = $row['email'];
			}			
			
			$q = "SELECT `opt_client_name`, `opt_client_email`,`sf_guard_user_id` FROM `" . DB_PREFIX . "newsletter_orderMIGRATE`";
			$array = Cms::$db->getAll($q);		
			foreach ($array as $row) {
				if (!in_array($row['opt_client_email'], $newsletterEmails)) {
					$row = maddslashes($row);
					
                    $firstName = isset($customerNames[$row['sf_guard_user_id']]) ? $customerNames[$row['sf_guard_user_id']]['firstName'] : '';
                    $lastName = isset($customerNames[$row['sf_guard_user_id']]) ? $customerNames[$row['sf_guard_user_id']]['lastName'] : '';
                    
                    if ($row['opt_client_name']) {
                        $name = explode(' ', $row['opt_client_name']);
                        $firstNameOpt = isset($name[0]) ? $name[0] : ''; 
                        $lastNameOpt = isset($name[1]) ? $name[1] : '';                         
                    }
                    
                    if (!$firstName && $firstNameOpt) {
                        $firstName = $firstNameOpt;
                    }
                    
                    if (!$lastName && $lastNameOpt) {
                        $lastName = $lastNameOpt;
                    }
                    
					$q = "INSERT INTO `" . DB_PREFIX . "newsletter_users` SET `first_name`='" . $firstName . "', `last_name`='" . $lastName . "', ";
					$q.= "`email`='" . $row['opt_client_email'] . "', `lang_id`='1', `active`='1' ";
					Cms::$db->insert($q);
				}
			}			
			
            Cms::$db->commit();
            echo '<br /><a href="' .  SERVER_URL . '/admin.html">Wróc do poprzedniej strony</a>';

        } catch (Exception $e) {
          Cms::$db->rollBack();
          echo "Failed: " . $e->getMessage();
        }
	}    
    
    public function migratePansuplerAction() {
        echo 'migracja pansupler....<br />';
        
        require_once(MODEL_DIR . '/Feature.php');
        require_once(MODEL_DIR . '/FeatureValue.php');
        $feature = new Feature();
        $featureValue = new FeatureValue();
                
		try {
			Cms::$db->beginTransaction();  
            
            //wez produkty
			$q = "SELECT pl.*, p.* FROM `" . DB_PREFIX . "ps_product_lang` pl ";
			$q .= "LEFT JOIN `" . DB_PREFIX . "ps_product` p ON p.id_product=pl.id_product";
			$products = Cms::$db->getAll($q);                        
            $products = getArrayByKey($products, 'id_product');
  
            $q = "SELECT pa.*, pc.id_attribute, agl.name as feature_name, agl.id_attribute_group as feature_id, al.name as feature_value FROM `" . DB_PREFIX . "ps_product_attribute_combination` pc ";
            $q .= "LEFT JOIN `" . DB_PREFIX . "ps_product_attribute` pa ON pc.id_product_attribute=pa.id_product_attribute ";
            $q .= "LEFT JOIN `" . DB_PREFIX . "ps_attribute` a ON a.id_attribute=pc.id_attribute ";
            $q .= "LEFT JOIN `" . DB_PREFIX . "ps_attribute_group_lang` agl ON agl.id_attribute_group=a.id_attribute_group ";
            $q .= "LEFT JOIN `" . DB_PREFIX . "ps_attribute_lang` al ON al.id_attribute=pc.id_attribute";

			$productAttributes = Cms::$db->getAll($q);       
                      
//            dump($productAttributes);
            
            $q = "SELECT * FROM `" . DB_PREFIX . "features_translation`";
            $features = Cms::$db->getAll($q);
            $features = getArrayByKey($features, 'name');
            
            foreach ($products as $productId => $row) {
                foreach ($productAttributes as $key => $productAttribute) {
                    if ($productAttribute['id_product'] == $productId) {
                        $products[$productId]['variations'][] = $productAttribute;
                    }
                }
            }
            
            $featureValues = $featureValue->getAll()['pl'];
            $featureValues = getArrayByKey($featureValues, 'name');
//            
            $small = [];
            foreach ($featureValues as $key => $val) {
                $small[strtolower($key)] = $val;
            }
            
            $featureValues = $small;
            
            $q = "SELECT * FROM `" . DB_PREFIX . "ps_image`";
            $images = Cms::$db->getAll($q); 
//            $images = getArrayByKey($images, 'id_product');
            
//            dump($images);
            dump($products); die;
//            dump($small);
//dump($images);die;
//dump($featureValues);die;
//dump($products);
//die;

            //tworzenie produktow i wariacji
            require_once(MODEL_DIR . '/shopProductsAdmin.php');
            require_once(MODEL_DIR . '/Variation.php');                
            $product = new ProductsAdmin();
            $variation = new Variation();          
            
            $taxes = array(
                '1' => 4,
                '2' => 5,
                '3' => 2,
                '4' => 1,
                '5' => 1,
            );
            
            $features = array(
                '1' => 21,   //size
                '2' => 22,   //shoes size
                '3' => 23,   //kolor
                '5' => 24,   //wielkosc opakowania
                '6' => 25   //smak
            );
            
            $producers = array(
                '9' => 1,   //activelab
                '15' => 2,   //allnutrition
                '4' => 3,   //beltor
                '12' => 4,   //biogenix
                '17' => 5,   //biotech usa
                '6' => 6,   //dna
                '13' => 7,   //fa nutrition
                '8' => 8,   //formotiva
                '7' => 9,   //masters
                '14' => 10,   //nutrend
                '3' => 11,   //olimp nutrition
                '10' => 12,   //ostrovit
                '16' => 13,   //sante
                '2' => 14,   //trec nutrition
                '11' => 15,   //lowickie wpc
            );
            
            $categories = array(
                '12'    => 2,//odzywki bialkowe
                '14'    => 3,//gainery
                '15'    => 7,//witaminy i mineraly
                '16'    => 6,//zma
                '17'    => 5,//aminokwasy
                '18'    => 9,//kreatyny
                '19'    => 8,//zdrowe tluszcze
                '20'    => 10,//reduktory tluszczu
                '21'    => 11,//ochrona stawow
                '22'    => 6,//przedtreningowki
                '23'    => 8,//batony
                '24'    => 12,//HMB
                '25'    => 6,//boostery testosteronu
                '26'    => 18,//odziez sportowa 
                '27'    => 13,//akcesoria sportow walki 
                '28'    => 13,//akcesoria treningowe 
                '29'    => 22,//specialne 
                '30'    => 22,//specialne dla kobiet 
                '31'    => 22,//inne 
                '32'    => 21,//zestawy promocyjne 
                '33'    => 8,//masla orzechowe i inne 
                '34'    => 4,//odzwyki weglowodanowe 
                '35'    => 6,//aktywatory azotu 
                '37'    => 5,//beta alanina 
                '38'    => 6,//stymulatory hormonow 
                '39'    => 17,//shaker i pillbox 
                '41'    => 6,//produkty wspomagajace 
                '42'    => 22,//wypszedaz 
                '43'    => 5,//BCAA  
            );
   
            foreach ($products as $productId => $row) {
                echo 'utworzono produkt: ' . $row['name'] . '<br />';
          
                $item = [];
                $item['pl']['name'] = $row['name'];
                $item['en']['name'] = $row['name'];
                $item['category_id'] = $categories[$row['id_category_default']];   
//                $item['category_id'] = 1;   
                $item['producer_id'] = $producers[$row['id_manufacturer']];
                $item['status_id'] = $row['available_for_order'] ? 2 : 3;  
                $item['type'] = isset($row['variations']) ? 1 : 2;
                
                $item['seo_title'] = $row['meta_title'];
                $item['pl']['content_short'] = $row['meta_description'];    //meta description
                $item['en']['content_short'] = $row['meta_description'];    //meta description
                $item['pl']['content'] = $row['description'] . '<br />' . $row['szczegoly'];   
                $item['en']['content'] = $row['description'] . '<br />' . $row['szczegoly'];   
                                
                //set unique feature for product                
                $id = $product->addAdmin($item);        

                $files = [];  
                foreach ($images as $img) {
                    if ($img['id_product'] == $productId) {
                        $link = "http://www.pansupler.pl/" . $img['id_image'] . "-large_default/" . $row['link_rewrite'] . ".jpg";
                        $file = getFileFormat($link);
                        $file['local'] = 1;
                        $files[] = $file;                        
                    }
                }
                
                $data = [];
                $data['id'] = $id;
                $product->imageAdmin($data, $files);                

                if (isset($row['variations'])) {                    
                    $featureId = $featureValues[strtolower($row['variations'][0]['feature_value'])]['feature_id'];
                    $q = "UPDATE `" . DB_PREFIX . "product` SET `feature1_id`='" . $featureId . "' ";
                    $q.= "WHERE `id`='" . $id . "' ";    

                    Cms::$db->update($q);                    
                }                
                
                if (isset($row['variations'])) {
                    foreach ($row['variations'] as $var) {
                        
                        echo 'utworzono nowa wariację: ' . $var['feature_name'] . ' ' . $var['feature_value'] . '<br />';
                                                
                        $ref = isset($var['reference']) && $var['reference'] ? $var['reference'] : $var['id_product'] . $var['id_product_attribute'];
                        
                        $price_purchase = $var['wholesale_price'];
                        if ($var['wholesale_price'] <= 0 ) {
                            $price_purchase = $row['wholesale_price'];
                        }
                        
                        $price = $var['price'];
                        if ($var['price'] <= 0 ) {
                            $price = $row['price'];
                        }          
                        
                        $item = [];
                        $item['product_id'] = $id;
                        $item['tax_id'] = $taxes[$row['id_tax_rules_group']];
                        $item['sku'] = $ref;
                        $item['ean'] = $ref;
                        $item['price_purchase'] = $price_purchase;
                        $item['price'] = $price;
                        $item['bestseller'] = 0;
                        $item['recommended'] = 0;
                        $item['main_page'] = 0;
                        $item['mega_offer'] = 0;
                        $item['weight'] = $var['weight'];
                        $item['qty'] = 10;

                        $item['feature1_value_id'] = $featureValues[strtolower($var['feature_value'])]['translatable_id'];

                        $variation->set($item);                          
                    }
                    
                } else {
                    //create one wariation
                    echo 'utworzono nowa pusta wariację dla: ' . $row['name'] . '<br />';
                    $ref = isset($row['reference']) && $row['reference'] ? $row['reference'] : $id;
                    
                    $item = [];
                    $item['product_id'] = $id;
                    $item['tax_id'] = $taxes[$row['id_tax_rules_group']];
                    $item['sku'] = $ref;
                    $item['ean'] = $ref;
                    $item['price_purchase'] = $row['wholesale_price'];
                    $item['price'] = $row['price'];
                    $item['promotion'] = 0;
                    $item['bestseller'] = 0;
                    $item['recommended'] = 0;
                    $item['main_page'] = 0;
                    $item['mega_offer'] = 0;
                    $item['weight'] =  $row['weight'] ? $row['weight'] : 0;
                    $item['qty'] = 5;

                    $id = $variation->set($item);                     
                }
                
              
//                dump($id, 'wynik');
            }
               
            Cms::$db->commit();          
            
        } catch (Exception $e) {
          Cms::$db->rollBack();
          echo "Failed: " . $e->getMessage();
        }        
    }    
    
    
	
//    public function exportProductsAction() {
//        $products = $this->getProducts();
//                
//        // creating object of SimpleXMLElement
//        $xml = new SimpleXMLElement('<products/>');
//
//        // function call to convert array to xml
//        $this->arrayToXml($products,$xml);
//
//        Header("Content-type: text/xml/force-download");
//        header("Content-Disposition: attachment; filename=products.xml");
//
//        print $xml->asXML();    
//        die;
//    }    
        
}

$controller = new RunController();
$controller->init($params);
