<?php

use SimpleExcel\SimpleExcel;
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/mailer.php');
require_once(CMS_DIR . '/application/models/pdf.php');
require_once(CMS_DIR . '/application/models/customer.php');
require_once(MODEL_DIR . '/SalesRepresentative.php');
require_once(MODEL_DIR . '/EmailTemplate.php');
require_once(MODEL_DIR . '/PaymentModel.php');
require_once(MODEL_DIR . '/phrase.php');

use Application\Entity\OrderLog;

class Orders 
{
	private $mailer;
	private $customer;

	public function __construct() 
    {
		$this->mailer = new Mailer();
		$this->pdf = new Pdf();
		$this->table = DB_PREFIX . 'order';
		$this->tableProducts = DB_PREFIX . 'product';
		$this->tablePayment = DB_PREFIX . 'payment';
		$this->tableStatus = DB_PREFIX . 'order_status';
		$this->tableStatusTranslation = DB_PREFIX . 'order_status_translation';
        $this->tablePhrase = DB_PREFIX . 'frazy_promocyjne';
		$this->customer = new Customer();
        $this->payment = new PaymentModel();
        $this->phrase = new Phrase();
	}

	public function add($post, $basket, $summary, $transport) 
    {

		$customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
		$post['discount'] = isset($_SESSION[CUSTOMER_CODE]['discount']) ? $_SESSION[CUSTOMER_CODE]['discount'] : 0;
		$post['promotion_code'] = isset($_SESSION[CUSTOMER_CODE]['promotion_code']) ? $_SESSION[CUSTOMER_CODE]['promotion_code'] : '';

        // uwazaj ta funkcja przeladowuje sesje customera !!!
		$this->customer->updateAddress($post, $customer_id);
        
        if (isset($_SESSION[CUSTOMER_CODE])) {
            $_SESSION[CUSTOMER_CODE]['promotion_code'] = $post['promotion_code'];
        }        

		if ($post['type'] != 2) {
			$post['company_name'] = '';
			$post['nip'] = '';
		}
        
		if ($post['shipping_type'] != 2) {
			$post['company_name'] = '';
		}
		
		$post = maddslashes($post);
		$summary = maddslashes($summary);
		$transport = maddslashes($transport);
        
        $post['phrase_id'] = 0;

        if ($post['promotion_code']) {
            
            $q = "SELECT `id` FROM `" . $this->tablePhrase . "` WHERE `fraza`='" . $post['promotion_code'] . "' ";
            if ($phrase = Cms::$db->getRow($q)) {    
                $post['phrase_id'] = $phrase['id'];
            }        
        }        
        
		$q = "INSERT INTO `" . $this->table . "` SET `customer_id`='" . $customer_id . "', `lang_id`='" . _ID . "', `price`='" . $summary['sum'] . "', "
				. "`discount`='" . $post['discount'] . "', `phrase_id`='" . $post['phrase_id'] . "', `payment_id`='" . $post['payment'] . "', `weight`='" . $summary['weight'] . "', "
				. "`comment`='" . $post['comment'] . "', `status_id`='1', `time_add`=NOW() ";

		if ($id = Cms::$db->insert($q)) {
            
            $params = ['customerId' => $customer_id, 'payment_id' => $post['payment']];     
            Cms::orderLogSave($id, OrderLog::ACTION_ORDER_CHOSEN_PAYMENT, $params);

            $params = ['customerId' => $customer_id,'comment' => $post['comment']]; 
            Cms::orderLogSave($id, OrderLog::ACTION_ORDER_CREATE, $params);

			foreach ($basket as $v) {
				$v = maddslashes($v);
				
				$q = "INSERT INTO `" . $this->table . "_product` SET `order_id`='" . $id . "', `product_id`='" . $v['product_id'] . "', `variation_id`='" . $v['variation_id'] . "', "
						. "`name`='" . $v['name'] . "', `desc`='" . $v['desc'] . "', `sku`='" . $v['sku'] . "', `ean`='" . $v['ean'] . "', `price_purchase`='" . $v['price_purchase'] . "',"
						. "`tax_val`='" . $v['tax'] . "', `price`='" . $v['price'] . "', `qty`='" . $v['qty'] . "' ";
				Cms::$db->insert($q);
			}
			
            if (isset($post['other_shipping']) && $post['other_shipping'] == 1) {
                $q = "INSERT INTO `" . $this->table . "_address` SET `order_id`='" . $id . "', `model`='billing', `type`='" . $post['type'] . "', "
                        . "`company_name`='" . $post['company_name'] . "', `nip`='" . $post['nip'] . "', `first_name`='" . $post['first_name'] . "', "
                        . "`last_name`='" . $post['last_name'] . "', `address1`='" . $post['address1'] . "', `address2`='" . $post['address2'] . "', "
                        . "`address3`='" . $post['address3'] . "', `post_code`='" . $post['post_code'] . "', `city`='" . $post['city'] . "', "
                        . "`country`='" . $post['country'] . "', `email`='" . $post['email'] . "', `phone`='" . $post['phone'] . "', "
                        . "`shipping_type`='" . $post['shipping_type'] . "', "
                        . "`shipping_company_name`='" . $post['shipping_company_name'] . "', `shipping_first_name`='" . $post['shipping_first_name'] . "', "
                        . "`shipping_last_name`='" . $post['shipping_last_name'] . "', `shipping_address1`='" . $post['shipping_address1'] . "', `shipping_address2`='" . $post['shipping_address2'] . "', "
                        . "`shipping_address3`='" . $post['shipping_address3'] . "', `shipping_post_code`='" . $post['shipping_post_code'] . "', `shipping_city`='" . $post['shipping_city'] . "', "
                        . "`shipping_country`='" . $post['shipping_country'] . "', `shipping_phone`='" . $post['shipping_phone'] . "' ";                        
            } else {
                $q = "INSERT INTO `" . $this->table . "_address` SET `order_id`='" . $id . "', `model`='billing', `type`='" . $post['type'] . "', "
                        . "`shipping_type`='" . $post['shipping_type'] . "', "
                        . "`shipping_company_name`='" . $post['shipping_company_name'] . "', `shipping_nip`='" . $post['shipping_nip'] . "', `shipping_first_name`='" . $post['shipping_first_name'] . "', "
                        . "`shipping_last_name`='" . $post['shipping_last_name'] . "', `email`='" . $post['email'] . "', `shipping_address1`='" . $post['shipping_address1'] . "', `shipping_address2`='" . $post['shipping_address2'] . "', "
                        . "`shipping_address3`='" . $post['shipping_address3'] . "', `shipping_post_code`='" . $post['shipping_post_code'] . "', `shipping_city`='" . $post['shipping_city'] . "', "
                        . "`shipping_country`='" . $post['shipping_country'] . "', `shipping_phone`='" . $post['shipping_phone'] . "' ";                 
            }
            
			Cms::$db->insert($q);
			
			if (Cms::$modules['unit_transport']) {
				$q = "INSERT INTO `" . $this->table . "_transport` SET `order_id`='" . $id . "', `price`='" . $transport['price_gross'] . "' ";				
			} else {
				$q = "INSERT INTO `" . $this->table . "_transport` SET `order_id`='" . $id . "', `courier_id`='" . $transport['courier_id']. "', "
						. "`courier_name`='" . $transport['courier_name'] . "', `service_id`='" . $transport['service_id'] . "', `service_name`='" . $transport['service_name'] . "', "
						. "`region_id`='" . $transport['region_id'] . "', `region_name`='" . $transport['region_name'] . "', `option_id`='" . $transport['id'] . "', "
						. "`price`='" . $transport['price'] . "', `tax`='" . $transport['tax'] . "' ";				
			}

			Cms::$db->insert($q);

            if ($post['payment'] > 3) {
                Cms::getFlashBag()->add('info', $GLOBALS['LANG']['order_ok']);
            }
            
			return $id;
		}
        Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_no']);
		return false;
	}

	public function checkData($data) 
    {
        $errors = [];

        if ($data) {
            foreach ($data as $field => $value) {
                switch ($field) {
                    case 'shipping_first_name':
                        if (strlen($value) < 2) {
                            $errors[] = $GLOBALS['LANG']['c_check1'];
                        }
                        break;
                    case 'shipping_last_name':
                        if (strlen($value) < 2) {
                            $errors[] = $GLOBALS['LANG']['c_check2'];
                        }
                        break;
                    case 'email':
                        if (!checkEmail($value)) {
                            $errors[] = $GLOBALS['LANG']['c_check3'];
                        }
                        break;                        
                    case 'shipping_phone':
//                            if (strlen($value) > 0 && strlen($value) < 3) {
                        if (strlen($value) < 3) {
                            $errors[] = $GLOBALS['LANG']['c_check16'];
                        }
                        break;
                    case 'shipping_country':
                        if ($value < 1) {
                            $errors[] = $GLOBALS['LANG']['c_check18'];
                        }
                        break;
                    case 'shipping_address1':
                        if (strlen($value) < 3) {
                            $errors[] = $GLOBALS['LANG']['c_check13'];
                        }
                        break;
                    case 'shipping_post_code':
                        if (strlen($value) < 3) {
                            $errors[] = $GLOBALS['LANG']['c_check14'];
                        }
                        break;
                    case 'shipping_city':
                        if (strlen($value) < 3) {
                            $errors[] = $GLOBALS['LANG']['c_check15'];
                        }
                        break; 
                    default:
                        break;                     
                }
            }
            
            if ($data['shipping_type'] == 2 AND strlen($data['shipping_company_name']) < 2) {
                $errors[] = $GLOBALS['LANG']['c_check11'];
            }

            if ($data['delivery_country'] != $data['shipping_country']) {
                $errors[] = 'Kraj dostawy jest inny niż kraj użytkownika.';
            }
                        
			if (!Cms::$modules['unit_transport']) {
				if (!isset($data['delivery_service']) OR $data['delivery_service'] < 1) {
					$errors[] = $GLOBALS['LANG']['order_deliver2'];
				}
			}
            
            if (!isset($data['payment']) OR $data['payment'] < 1) {
                $errors[] = $GLOBALS['LANG']['order_payment2'];
            }
            
            if (isset($data['other_shipping'])) {
                
                if ($data['type'] == 2 AND strlen($data['company_name']) < 2) {
                    $errors[] = $GLOBALS['LANG']['c_check11'];
                }
            
                foreach ($data as $field => $value) {
                    switch ($field) {
                        case 'delivery_country':
                            if ($value < 1) {
                                $errors[] = $GLOBALS['LANG']['c_check18'];
                            }
                            break;
                        case 'first_name':
                            if (strlen($value) < 2) {
                                $errors[] = $GLOBALS['LANG']['c_check1'];
                            }
                            break;
                        case 'last_name':
                            if (strlen($value) < 2) {
                                $errors[] = $GLOBALS['LANG']['c_check2'];
                            }
                            break;
                        case 'phone':
    //                        if (strlen($value) > 0 && strlen($value) < 3) {
                            if (strlen($value) < 3) {
                                $errors[] = $GLOBALS['LANG']['c_check16'];
                            }
                            break;
                        case 'country':
                            if ($value < 1) {
                                $errors[] = $GLOBALS['LANG']['c_check18'];
                            }
                            break;
                        case 'address1':
                            if (strlen($value) < 3) {
                                $errors[] = $GLOBALS['LANG']['c_check13'];
                            }
                            break;
                        case 'post_code':
                            if (strlen($value) < 3) {
                                $errors[] = $GLOBALS['LANG']['c_check14'];
                            }
                            break;
                        case 'city':
                            if (strlen($data['city']) < 3) {
                                $errors[] = $GLOBALS['LANG']['c_check15'];
                            }
                            break;                        

                        default:
                            break; 
                        }
                }
                
                
            }
            
        }
        
        if ($errors) {
            
            foreach ($errors as $error) {
                Cms::getFlashBag()->add('error', $error);
            }

            return false;
        }
        
        return true;
	}
	
	public function checkAccept($data) 
    {
		if (!isset($data['accept']) OR $data['accept'] != 1) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_please_accept_terms']);
			return false;
		} else {
			return true;
		}
	}

	public function generateCsv($id = 0) 
    {
		require_once(SYS_DIR . '/libraries/SimpleExcel/SimpleExcel.php');

		$csv = new SimpleExcel('csv');
		$headers = ['kod_kreskowy','kod','ilosc'];
		$data = [];

		$data[] = $headers;

		if ($aOrder = $this->getById($id)) {

			foreach ($aOrder['products'] as $product) {
				$data[] = array($product['ean'], $product['sku'], $product['qty']);
			}

		}

		$csv->writer->setData($data);
		$csv->writer->setDelimiter(";");                  // (optional) if delimiter not set, by default comma (",") will be used instead
		
		return $csv->writer->saveString();  		
//		$csv->writer->saveFile('order nr -' . $id);  		
	}
	
    public function sendEmailOrderAddCustomer(array $data) 
    {		
		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate($data['template_name']);

		$i = 1;
		$products = '<br />';

		$sum = formatPrice($data['order']['sum']);
//		$sum = formatPrice($data['order']['sum'] + $data['order']['transport_price']);
//		$sum = formatPrice($item['sum'] - $item['sum'] * $item['discount'] / 100 + $item['transport_price']);
		$sum_name = CMS::$conf['currency_left'] . $sum . CMS::$conf['currency_right'];

		$transport = $data['order']['transport_name'] . ' - ' . CMS::$conf['currency_left'] . $data['order']['transport_price'] . CMS::$conf['currency_right'];
		foreach ($data['order']['products'] as $v) {
			$products.= $i . '. ' . $v['name'] . '<small> ' . $v['desc'] . '</small>' . ' - ' . CMS::$conf['currency_left'] . $v['price_gross'] . CMS::$conf['currency_right'] . ' * ' . $v['qty'];
			$products.= $GLOBALS['LANG']['order_szt'] . ' = ' . CMS::$conf['currency_left'] . $v['sum'] . CMS::$conf['currency_right'] . '<br />';
			$i++;
		}
		$products.= '<br />';
        
		$title = str_replace($data['search_title'], $data['replace_title'], $template['title']);     
        
        //content
		$search = array('#COMPANY_NAME#','#DOMAIN#', '#FIRST_NAME#', '#LAST_NAME#', '#PRODUCTS#', '#SUM#', '#TOTAL#', '#TRANSPORT#', '#PAYMENT#', '#COMMENT#', '#URL#', '#ORDER_ID#', '#DISCOUNT#', '#SERVER_URL#');
		$replace = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $data['order']['shipping_first_name'], $data['order']['shipping_last_name'], $products, $sum_name, CMS::$conf['currency_left'] . $data['order']['total'] . CMS::$conf['currency_right'], $transport, $data['order']['payment'], '<i>' . $data['order']['comment'] . '</i>', $data['order_url'], $data['order_id'], $data['order']['discount'] .'%', $data['server_url']);
		$content = str_replace($search, $replace, $template['content']);
                
		// wysylanie do klienta
		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);		
		
		$result = $this->mailer->sendHTML($data['order']['email']);
		$this->mailer->ClearAllRecipients();

        $customerId = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
        
        $params = array(
            'result' => $result ? $result : $this->mailer->getError(),
            'customerId' => $customerId,
            'email' => $data['order']['email']
        );
            
        Cms::orderLogSave($data['order_id'], OrderLog::ACTION_ORDER_CREATE_EMAIL_NOTIFICATION_SEND, $params);

        $data = array(
            'title' => $title,
            'content' => $content
        );
        
        $this->sendEmailOrderAddSalesRepresentative($data);        
        
		return $result;        
    }
    
    public function sendEmailOrderAddSalesRepresentative(array $data) 
    {        
		// wysylka do przedstawiciela handlowego
		if ($salesRepresentativeId = $_SESSION[CUSTOMER_CODE]['sales_representative']) {
			$salesRepresentative = new SalesRepresentative();
			$entity = $salesRepresentative->getById($salesRepresentativeId);
			$this->mailer->setSubject($data['title']);
			$this->mailer->setBody($data['content']);		

            $result = $this->mailer->sendHTML($entity[0]['email']);
            $this->mailer->ClearAllRecipients();
                
            return $result;		
		}
        
        return false;
    }
    
    public function sendEmailOrderAddAdmin(array $data) 
    {
		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate($data['template_name'] . '_admin');      
        
		$i = 1;
		$products = '<br />';

		$sum = formatPrice($data['order']['sum']);
//		$sum = formatPrice($data['order']['sum'] + $data['order']['transport_price']);
//		$sum = formatPrice($item['sum'] - $item['sum'] * $item['discount'] / 100 + $item['transport_price']);
		$sum_name = CMS::$conf['currency_left'] . $sum . CMS::$conf['currency_right'];

		$transport = $data['order']['transport_name'] . ' - ' . CMS::$conf['currency_left'] . $data['order']['transport_price'] . CMS::$conf['currency_right'];
		foreach ($data['order']['products'] as $v) {
			$products.= $i . '. ' . $v['name'] . '<small> ' . $v['desc'] . '</small>' . ' - ' . CMS::$conf['currency_left'] . $v['price_gross'] . CMS::$conf['currency_right'] . ' * ' . $v['qty'];
			$products.= $GLOBALS['LANG']['order_szt'] . ' = ' . CMS::$conf['currency_left'] . $v['sum'] . CMS::$conf['currency_right'] . '<br />';
			$i++;
		}
		$products.= '<br />';
        
        $data['search_title'] = array_merge($data['search_title'], ['#FIRST_NAME#', '#LAST_NAME#', '#TOTAL#']);
        $data['replace_title']= array_merge($data['replace_title'], [$data['order']['shipping_first_name'], $data['order']['shipping_last_name'], $data['order']['total']]);
        
		$title = str_replace($data['search_title'], $data['replace_title'], $template['title']);     
        
        //content
		$search = array('#COMPANY_NAME#','#DOMAIN#', '#FIRST_NAME#', '#LAST_NAME#', '#PRODUCTS#', '#SUM#', '#TOTAL#', '#TRANSPORT#', '#PAYMENT#', '#COMMENT#', '#URL#', '#ORDER_ID#', '#DISCOUNT#', '#SERVER_URL#');
		$replace = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $data['order']['shipping_first_name'], $data['order']['shipping_last_name'], $products, $sum_name, CMS::$conf['currency_left'] . $data['order']['total'] . CMS::$conf['currency_right'], $transport, $data['order']['payment'], '<i>' . $data['order']['comment'] . '</i>', $data['order_url'], $data['order_id'], $data['order']['discount'] .'%', $data['server_url']);
		$content = str_replace($search, $replace, $template['content']);
        
		if (Cms::$modules['price_groups']) {
			$file = $this->generateCsv($data['order_id']);
            $this->mailer->AddStringAttachment($file, 'order-' . $data['order_id']. 'csv');
		}                

        // wysyla do administratora
		$email_array = explode(",", Cms::$conf['email_order']);
		if (is_array($email_array)) {
			$this->mailer->setSubject($title);
			$this->mailer->setBody($content);
            
			foreach ($email_array as $v) {
				$this->mailer->sendHTML($v);
				$this->mailer->ClearAllRecipients();
			}
		}        
        
    }    
    
	public function sendEmailOrderAdd($orderId = 0) 
    {        
		$order = $this->getById($orderId);        

		if (isset($_SESSION[CUSTOMER_CODE]['id']) AND $_SESSION[CUSTOMER_CODE]['id'] > 0) {
			$uid = URL . '/customer/order/' . md5($order['id'] . $order['time_add']) . '.html';
			$orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
			$templateName = 'order_add_registered';
		} else {
			$uid = URL . '/customer/order/' . md5($order['id'] . $order['time_add']) . '-' . base64_encode(date("Y-m-d")) . '.html';
			$orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
			$templateName = 'order_add_unregistered';
		}        

        $data = array(
            'template_name' => $templateName,
            'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#'],
            'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $orderId],            
            'order' => $order,            
            'order_id' => $orderId,            
            'order_url' => $orderUrl,
            'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>'
        );

        $this->sendEmailOrderAddCustomer($data);
        $this->sendEmailOrderAddAdmin($data);
	}
    
	public function sendEmailOrderPaymentCustomer(array $data) 
    {
		$order = isset($data['order_id']) ? $this->getById($data['order_id']) : $data['order'];      
        $status = isset($order['status_id']) ? $this->getStatus($order['status_id']) : $data['status'];

        $emailTemplate = new EmailTemplate();
        $template = $emailTemplate->getTemplate('order_payment');			
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
        
        $searchTitle = array('#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#', '#SERVER_URL#');
        $replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $order['id'], SERVER_URL);
        $title = str_replace($searchTitle, $replaceTitle, $template['title']);        
        
        $search = array('#FIRST_NAME#', '#LAST_NAME#', '#TOTAL#', '#PAYMENT#', '#ORDER_ID#', '#STATUS#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
        $replace = array($order['first_name'], $order['last_name'], CMS::$conf['currency_left'] . $order['total'] . CMS::$conf['currency_right'], $order['payment'], $order['id'], '<span style="color: ' . $status['color'] . ';">' . $status['name'] . '</span>', Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
        $content = str_replace($search, $replace, $template['content']);

		// wysylanie do klienta
		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);		
		
		$result = $this->mailer->sendHTML($order['email']);
		$this->mailer->ClearAllRecipients();

        $customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
        
        $params = array(
            'result' => $result ? $result : $this->mailer->getError(),
            'customerId' => $customer_id,
            'email' => $order['email']
        );           
            
        Cms::orderLogSave($id, OrderLog::ACTION_ORDER_PAYMENT_EMAIL_NOTIFICATION_SEND, $params);

		return $result;
	}
    
	public function sendEmailOrderDispatchedCustomer(array $data)
    {        
		$order = isset($data['order_id']) ? $this->getById($data['order_id']) : $data['order'];      
        $status = isset($order['status_id']) ? $this->getStatus($order['status_id']) : $data['status'];

        $emailTemplate = new EmailTemplate();
        $template = $emailTemplate->getTemplate('order_dispatched');			
        $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
        
        $searchTitle = array('#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#', '#SERVER_URL#');
        $replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $order['id'], $serverUrl);
        $title = str_replace($searchTitle, $replaceTitle, $template['title']);
        
        $transport = $order['transport_name'] . ' - ' . CMS::$conf['currency_left'] . $order['transport_price'] . CMS::$conf['currency_right'];
        
        $search = array('#FIRST_NAME#', '#LAST_NAME#', '#ORDER_ID#', '#STATUS#', '#TRANSPORT#', '#TRACKING#','#DATE_COMPLETE#', '#COMMENT#', '#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
        $replace = array($order['first_name'], $order['last_name'], $order['id'], '<span style="color: ' . $status['color'] . ';">' . $status['name'] . '</span>', $transport, $order['tracking'], $order['time_complete'], $order['comment_admin'], Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
        $content = str_replace($search, $replace, $template['content']);

		// wysylanie do klienta
		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);		
		
		$result = $this->mailer->sendHTML($order['email']);
		$this->mailer->ClearAllRecipients();

        $params = array(
            'result' => $result ? $result : $this->mailer->getError(),
            'email' => $order['email']
        );           
            
        Cms::orderLogSave($data['order_id'], OrderLog::ACTION_ORDER_SENT_EMAIL_NOTIFICATION, $params);

		return $result;
	}    

	function edit($post, $entity) 
    {
		if ($post['id']) {
			// sprawdzamy czy mozemy anulowac zamoweinie, Admin
			if ($post['status_id'] == 4 AND !in_array($_SESSION[USER_CODE]['level'], ['1','2'])) {
//				saveHistory('o', $_SESSION[USER_CODE]['login'], $post['id'], 13);
				Cms::getFlashBag()->add('error', 'Nie masz odpowiednich uprawnień by anulować to zamówienie.');
				return false;
			}

			$q = "SELECT `status_id`, `comment_admin` FROM `" . $this->table . "` WHERE `id`='" . (int) $post['id'] . "' ";
			$item = Cms::$db->getRow($q);

			if ($post['comment_admin'] AND $item['comment_admin'])
				$comment = '<br />' . addslashes($post['comment_admin']);
			elseif ($post['comment_admin'] AND ! $item['comment_admin'])
				$comment = addslashes($post['comment_admin']);
			else
				$comment = '';

			// anulujemy zamowienie aby odliczyc punkty
			if ($post['status_id'] == 4) {
				$this->cancel($post['id']);
			}

			$q = "UPDATE `" . $this->table . "` SET `status_id`='" . $post['status_id'] . "', `comment_admin`=CONCAT(`comment_admin`, '" . $comment . "') ";
			if ($post['status_id'] == 3)
				$q.= ", time_complete=NOW() ";
			$q.= "WHERE `id`='" . (int) $post['id'] . "' ";
			Cms::$db->update($q);

			$order = $this->getById($post['id']);
			if ($post['status_id'] == 2 OR $post['status_id'] == 3) {
				$q = "UPDATE `" . $this->table . "` SET `time_payment`=NOW() WHERE `id`='" . (int) $post['id'] . "' ";
				Cms::$db->update($q);
			}
                        
            if ($post['status_id'] == 2 && $order['phrase_id']) {                                
                $q = "SELECT * FROM `" . $this->tablePhrase . "` WHERE `id`='" . (int) $order['phrase_id'] . "' ";
                if ($phrase = Cms::$db->getRow($q)) {
                    $this->phrase->usePhrase($phrase['fraza'], $post['id'], $order['customer_id']);
                }      
            }            
            
            if ($entity['status_id'] != $post['status_id']) {
                $params = array(
                    'login' => $_SESSION[USER_CODE]['login'],
                    'userId' => $_SESSION[USER_CODE]['id'],
                    'before'    => $entity['status_id'],
                    'after' => $post['status_id'],
                    'commentAdmin' => $post['comment_admin']
                );                
                Cms::orderLogSave($post['id'], OrderLog::ACTION_ORDER_MANUAL_STATUS_CHANGE, $params);           
            }
            
			$status = $this->getStatus($post['status_id']);
			
            if (isset($post['email']) AND $post['email'] == 1 && $entity['status_id'] != $post['status_id']) {
                switch ($post['status_id']) {
                    case 2:
                        $result = $this->sendEmailOrderPaymentCustomer(['order_id' => $post['id']]);
                        break;
                    case 3:
                        $result = $this->sendEmailOrderDispatchedCustomer(['order_id' => $post['id']]);
                        break;    
                    default:                        
                        break;
                }
                
                if (in_array($post['status_id'], [2,3])) {
                    
                    $params = array(
                        'result' => $result ? $result : $this->mailer->getError(),
                        'login' => $_SESSION[USER_CODE]['login'],
                        'userId' => $_SESSION[USER_CODE]['id'],
                        'email' => $entity['email']
                    );
                
                    Cms::orderLogSave($post['id'], OrderLog::ACTION_ORDER_STATUS_EMAIL_NOTIFICATION_SEND, $params);
                    Cms::getFlashBag()->add('info', 'Zmieniono status i wysłano powiadomienie.');
                } else {
                    Cms::getFlashBag()->add('info', 'Zmieniono status.');
                }                                
                                
            } else {
                Cms::getFlashBag()->add('info', 'Zmieniono status bez wysyłania powiadomienia.');
            }

			return true;
		}
        Cms::getFlashBag()->add('error', 'Błąd operacji!');
		return false;
	}
	
	public function loadStatus() 
    {
        $locale = Cms::$session->get('locale_admin') ? Cms::$session->get('locale_admin') : LOCALE;

		$q = "SELECT s.id, s.color, s.order, st.name as name, st.locale FROM `" . $this->tableStatus . "` s ";
		$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
		$q.= " ORDER By `order`";
		$array = Cms::$db->getAll($q);
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}
	
	public function getStatus($id = 0) 
    {
        $locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;

		if ($id > 0) {
			$q = "SELECT s.id, s.color, s.order, st.name, st.locale FROM `" . $this->tableStatus . "` s ";
			$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
			$q.= "WHERE s.id='" . (int) $id . "' ";

			return Cms::$db->getRow($q);
		}
		return false;
	}
    
    public function setStatus($id, $statusId) 
    {
        $q = "UPDATE `" . $this->table . "` SET `status_id`='" . $statusId . "'";

		if ($statusId == 2) {
			$q .= ",`time_payment`=NOW()";   
		}
		
		$q .= " WHERE `id`='" . $id . "' ";		
        Cms::$db->update($q);
    }    
	
    public function setData($id, array $data = []) 
    {
		if (!$id) {
			return false;
		}
		
		$q = "UPDATE `" . $this->table . "`";
		
		$lastElement = end($data);
		if (count($data) > 0) {
			$q .= " SET "; 
		}
		
		foreach ($data as $key => $value) {
			$q .= "`$key` = '". $value . "'";
			
			if ($value !== $lastElement) {
				$q .= " , ";
			}			
		}
		
		$q .= " WHERE `id`='" . $id . "' ";
                   
        Cms::$db->update($q);
    }   	

	public function loadByCustomer() 
    {
        $locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;

		$customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
		$q = "SELECT o.*, p.id as payment_id, p.name as payment, s.id as status_id, st.name as status, ";
		$q.= "s.color as status_color FROM `" . $this->table . "` o LEFT JOIN `" . $this->tablePayment . "` p ON o.payment_id=p.id ";
		$q.= "LEFT JOIN `" . $this->tableStatus . "` s ON o.status_id=s.id ";
		$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
		$q.= "WHERE o.customer_id='" . $customer_id . "' AND o.customer_id>0 ";
		$q.= "ORDER BY o.time_add DESC ";
		
		$array = Cms::$db->getAll($q);

		$items = array();
		foreach ($array as $v) {
			
			$q = "SELECT * FROM `" . $this->table . "_transport` WHERE `order_id`='" . $v['id'] . "' ";
			$t = Cms::$db->getRow($q);
			$v['transport_price'] = formatPrice($t['price'], $t['tax']);
			$v['transport_name'] = $t['service_name'];
			
			$v['title'] = $GLOBALS['LANG']['order_id'] . ' ' . $v['id'];
			$v['total'] = formatPrice($v['price'] - $v['price'] * $v['discount'] / 100 + $v['transport_price']);
			$v['uid'] = md5($v['id'] . $v['time_add']);

			$items[] = $v;
		}
		

		return $items;
	}

	public function loadByCustomerUid($uid = '') 
    {
        $locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;

		if (!empty($uid)) {
			$customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
			$q = "SELECT o.*, p.name as payment, st.name as status, s.color as status_color, ";
			$q.= "a.company_name, a.nip, a.first_name, a.last_name, a.email, a.address1, a.address2, a.address3, a.post_code, a.city, a.phone, ";
			$q.= "a.shipping_company_name, a.shipping_nip, a.shipping_first_name, a.shipping_last_name, a.shipping_address1, a.shipping_address2, a.shipping_address3, a.shipping_post_code, a.shipping_city, a.shipping_phone, ";
			$q.= "(SELECT `name` FROM `" . DB_PREFIX . "transport_country` WHERE `id`=a.country) as country, ";
			$q.= "(SELECT `name` FROM `" . DB_PREFIX . "transport_country` WHERE `id`=a.shipping_country) as shipping_country ";
			$q.= "FROM `" . $this->table . "` o ";
			$q.= "LEFT JOIN `" . $this->tablePayment . "` p ON o.payment_id=p.id ";
			$q.= "LEFT JOIN `" . $this->tableStatus . "` s ON o.status_id=s.id ";
			$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
			$q.= "LEFT JOIN `" . $this->table . "_address` a ON o.id=a.order_id ";
			$q.= "WHERE md5(CONCAT(o.id,o.time_add))='" . $uid . "' ";
			if ($v = Cms::$db->getRow($q)) {
				
				$q = "SELECT * FROM `" . $this->table . "_transport` WHERE `order_id`='" . $v['id'] . "' ";
				$t = Cms::$db->getRow($q);
				$v['transport_price'] = formatPrice($t['price'], $t['tax']);
				$v['transport_name'] = $t['service_name'];
			
				$v['price_val'] = $v['price'];
				$v['sum'] = $v['price'] - $v['price'] * $v['discount'] / 100;
				$v['total'] = formatPrice($v['sum'] + $v['transport_price']);
				$v['saving'] = formatPrice($v['price'] - $v['sum']);
				$v['price_gross'] = 0;
				$v['price'] = 0;
				$v['tax_val'] = 0;
				$q = "SELECT * FROM `" . $this->table . "_product` WHERE `order_id`='" . $v['id'] . "' ";
				$array = Cms::$db->getAll($q);
				if (is_array($array)) {
					foreach ($array as $w) {
						$w['price_gross'] = formatPrice($w['price'], $w['tax_val']);
						$w['price'] = formatPrice($w['price']);
						$w['sum'] = formatPrice($w['price_gross'] * $w['qty']);
						$v['products'][] = $w;
						if (isset($w['price']))
							$v['price']+= $w['qty'] * $w['price'];
						$v['price_gross']+= round($w['qty'] * $w['price_gross'], 2);
						if (isset($w['price']))
							$v['tax_val']+= round($w['qty'] * $w['price'] * $w['tax_val'] / 100, 2);
					}
					$v['price'] = formatPrice($v['price']);
					$v['price_val'] = formatPrice($v['price_val']);
					$v['price_gross'] = formatPrice($v['price_gross']);
					$v['tax_val'] = formatPrice($v['tax_val']);
					$v['transport_price'] = formatPrice($v['transport_price']);
					$v['comment'] = nl2br($v['comment']);
					$v['comment_admin'] = nl2br($v['comment_admin']);
					return $v;
				}
			}
		}
		return false;
	}

	public function loadOrdersAdmin($filtr = '', $limitStart = 0, $limit = 100) 
    {
        $locale = Cms::$session->get('locale_admin') ? Cms::$session->get('locale_admin') : LOCALE;
        
		$q = "SELECT o.*, p.name as payment, st.name as status, s.color as status_color, t.price as `transport_price`, t.tax as `transport_tax`, t.service_name as `transport_name`, ";
		$q.= "a.shipping_first_name, a.shipping_last_name, a.email ";
		$q.= "FROM `" . $this->table . "` o LEFT JOIN `" . $this->tablePayment . "` p ON o.payment_id=p.id ";
		$q.= "LEFT JOIN `" . $this->tableStatus . "` s ON o.status_id=s.id ";
		$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
		$q.= "LEFT JOIN `" . $this->table . "_transport` t ON o.id=t.order_id ";
		$q.= "LEFT JOIN `" . $this->table . "_address` a ON o.id=a.order_id ";
		$q.= "WHERE a.model='billing' ";
		if (isset($filtr['action']) AND $filtr['action'] == 'search') {
			if ($filtr['first_name'])
				$q.= "AND a.shipping_first_name LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND a.shipping_last_name LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND a.email LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['id'])
				$q.= "AND (o.id='" . addslashes($filtr['id']) . "' OR a.order_id='" . addslashes($filtr['id']) . "') ";
			if ($filtr['status'])
				$q.= "AND o.status_id='" . $filtr['status'] . "' ";
		}
		if ($filtr['order_name'] == 'date_add') {
			$q.= "ORDER BY o.time_add ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'id') {
			$q.= "ORDER BY o.id ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'transport') {
			$q.= "ORDER BY o.transport_name ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'status') {
			$q.= "ORDER BY s.name ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'buyer') {
			$q.= "ORDER BY o.buyer_id ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'name') {
			$q.= "ORDER BY o.name ";
			$order = 1;
		} elseif ($filtr['order_name'] == 'email') {
			$q.= "ORDER BY o.email ";
			$order = 1;
		}
		if ($order == 1) {
			if ($filtr['order_type'] == 'up')
				$q.= "ASC ";
			elseif ($filtr['order_type'] == 'down')
				$q.= "DESC ";
		}
		$q.= "LIMIT " . $limitStart . ", " . $limit;

		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = $v['id'];
			$v['transport_price'] = formatPrice($v['transport_price'], $v['transport_tax']);
			$v['total'] = formatPrice($v['price'] - $v['price'] * $v['discount'] / 100 + $v['transport_price']);
			$v['url'] = URL . '/admin/shop-orders/' . $v['id'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesOrdersAdmin($filtr = '', $limit = 100) 
    {
		$q = "SELECT COUNT(o.`id`) FROM `" . $this->table . "` o ";
        $q.= "LEFT JOIN `" . $this->table . "_address` a ON o.id=a.order_id ";
        
		if (isset($filtr['action']) AND $filtr['action'] == 'search') {
			$q.= "WHERE 1 ";
			if ($filtr['first_name'])
				$q.= "AND a.shipping_first_name LIKE '" . $filtr['first_name'] . "%' ";
			if ($filtr['last_name'])
				$q.= "AND a.shipping_last_name LIKE '" . $filtr['last_name'] . "%' ";
			if ($filtr['email'])
				$q.= "AND a.email LIKE '" . $filtr['email'] . "%' ";
			if ($filtr['id'])
				$q.= "AND (o.id='" . $filtr['id'] . "' OR a.order_id='" . $filtr['id'] . "') ";
			if ($filtr['status'])
				$q.= "AND o.status_id='" . $filtr['status'] . "' ";
		}
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function getById($id = 0) 
    {		
        $locale = Cms::$session->get('locale_admin') ? Cms::$session->get('locale_admin') : LOCALE;
		
		if ($id > 0) {
			$q = "SELECT o.*, p.name as payment, st.name as status, s.color as status_color, t.price as `transport_price`, t.tax as `transport_tax`, t.service_name as `transport_name`, t.option_id as `transport_option_id`, ";
			$q.= "a.company_name, a.nip, a.first_name, a.last_name, a.email, a.address1, a.address2, a.address3, a.post_code, a.city, a.phone, a.country, ";
			$q.= "a.shipping_company_name, a.shipping_nip, a.shipping_first_name, a.shipping_last_name, a.shipping_address1, a.shipping_address2, a.shipping_address3, a.shipping_post_code, a.shipping_city, a.shipping_country, a.shipping_phone, ";
			$q.= "(SELECT f.fraza FROM `" . DB_PREFIX . "frazy_promocyjne_uzycia` u LEFT JOIN `" . DB_PREFIX . "frazy_promocyjne` f ON f.id=u.id_frazy WHERE u.id_zam=o.id LIMIT 1) as fraza, ";
			$q.= "(SELECT `name` FROM `" . DB_PREFIX . "transport_country` WHERE `id`=a.country) as country_name, ";
			$q.= "(SELECT `name` FROM `" . DB_PREFIX . "transport_country` WHERE `id`=a.shipping_country) as shipping_country_name ";
			$q.= "FROM `" . $this->table . "` o LEFT JOIN `" . $this->tablePayment . "` p ON o.payment_id=p.id ";
			$q.= "LEFT JOIN `" . $this->table . "_transport` t ON o.id=t.order_id ";			
			$q.= "LEFT JOIN `" . $this->tableStatus . "` s ON o.status_id=s.id ";
			$q.= "LEFT JOIN `" . $this->tableStatusTranslation . "` st ON st.translatable_id = s.id AND st.locale = '" . $locale . "' ";
			$q.= "LEFT JOIN `" . $this->table . "_address` a ON o.id=a.order_id ";
			$q.= "WHERE o.id='" . $id . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v['cid'] = md5($v['id']);
				$v['price_val'] = $v['price'];
				$v['sum'] = $v['price'] - $v['price'] * $v['discount'] / 100;
				$v['transport_price'] = formatPrice($v['transport_price'], $v['transport_tax']);
				$v['total'] = formatPrice($v['sum'] + $v['transport_price']);
				$v['saving'] = formatPrice($v['price'] - $v['sum']);
				$v['price_gross'] = 0;
				$v['price'] = 0;
				$v['tax_val'] = 0;
				$q = "SELECT * FROM `" . $this->table . "_product` WHERE `order_id`='" . $id . "' ";
				$array = Cms::$db->getAll($q);

				if (is_array($array)) {
					foreach ($array as $w) {
						$w['price_gross'] = formatPrice($w['price'], $w['tax_val']);
						$w['price'] = formatPrice($w['price']);
						$w['sum'] = formatPrice($w['price_gross'] * $w['qty']);
						$v['products'][] = $w;
						if (isset($w['price']))
							$v['price']+= $w['qty'] * $w['price'];
						$v['price_gross']+= round($w['qty'] * $w['price_gross'], 2);
						if (isset($w['price']))
							$v['tax_val']+= round($w['qty'] * $w['price'] * $w['tax_val'] / 100, 2);
					}
					$v['price'] = formatPrice($v['price']);
					$v['price_val'] = formatPrice($v['price_val']);
					$v['price_gross'] = formatPrice($v['price_gross']);
					$v['tax_val'] = formatPrice($v['tax_val']);					
					$v['comment'] = nl2br($v['comment']);
					$v['comment_admin'] = nl2br($v['comment_admin']);
					return $v;
				}
			}
		}
		return false;
	}

	function cancel($id) 
    {
		if ($id) {
			$q = "SELECT `customer_id`, `status_id` FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$q = "SELECT *  FROM `" . $this->table . "_product` WHERE `order_id`='" . (int) $id . "' ";
				$products = Cms::$db->getAll($q);
			}

			if ($v['status_id'] != 4) {
				$q = "UPDATE `" . $this->table . "` SET `status_id`='4' WHERE `id`='" . (int) $id . "' ";
				Cms::$db->update($q);

				foreach ($products as $w) {
//					$q = "UPDATE `" . $this->tableProducts . "` SET `sold`=`sold`-" . $w['qty'] . " ";
//					$q.= "WHERE `id`='" . (int) $w['product_id'] . "' ";
//					Cms::$db->update($q);
				}
                Cms::getFlashBag()->add('error', 'Anulowano wybrany element.');
				return true;
			}
		}
        Cms::getFlashBag()->add('error', 'Anulowanie elementu nie powiodło się!');
		return false;
	}

	// zamowienia z eBay
	public function loadCustomersAll() 
    {
		$q = "SELECT `id`, `first_name`, `last_name`, `email` FROM `" . DB_PREFIX . "customer` ORDER BY `first_name`, `last_name` ";
		$array = Cms::$db->getAll($q);
		return $array;
	}

	public function loadCustomersId($id, $email) 
    {
		$q = "SELECT * FROM `" . DB_PREFIX . "customer` ";
		if ($id > 0)
			$q.= "WHERE `id`='" . (int) $id . "' ";
		elseif (isset($email))
			$q.= "WHERE `email`='" . $email . "' ";
		if ($v = Cms::$db->getRow($q)) {
			return $v;
		}
        Cms::getFlashBag()->add('error', 'Brak klienta w bazie serwisu!');
		return false;
	}

	public function loadProducts() 
    {
		$q = "SELECT p.id, p.price, d.name FROM `" . DB_PREFIX . "shop_products` p ";
		$q.= "LEFT JOIN `" . DB_PREFIX . "shop_products_desc` d ON p.id=d.parent_id ";
		$q.= "WHERE d.lang_id=1 ORDER BY d.name ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$items[] = $v;
		}
		return $items;
	}

	public function loadProductsPost($id) 
    {
		$q = "SELECT p.id as product_id, p.producer_id, p.price, p.price_purchase, p.sku, p.weight, t.value as tax_val, d.title FROM `" . DB_PREFIX . "shop_products` p ";
		$q.= "LEFT JOIN `" . DB_PREFIX . "shop_products_desc` d ON p.id=d.parent_id LEFT JOIN `" . DB_PREFIX . "shop_tax` t ON p.tax_id=t.id ";
		$q.= "WHERE d.lang_id=1 AND p.id='" . $id . "' ";
		if ($v = Cms::$db->getRow($q)) {
			$v['amount'] = 1;
			$v['price'] = formatPrice($v['price'] / (1 + $v['tax_val'] / 100));
			$v['price_purchase'] = formatPrice($v['price_purchase']);
			$v['sum'] = formatPrice($v['price'] * $v['amount']);
			return $v;
		}
		return false;
	}

	public function emailExistsCustomer($email) 
    {
		$q = "SELECT `id` FROM `" . DB_PREFIX . "customer` WHERE `email`='" . $email . "' ";
		if ($v = Cms::$db->getRow($q)) {
			return false;
		}
		return true;
	}

	function noPL($txt) 
    {
		$search = array('Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', '&', 'quot;');
		$replace = array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', '', '');
		return str_replace($search, $replace, $txt);
	}

	function cancelOrdersOld($interval = '24') 
    {   // anulujemy stare zamowienia
		$q = "SELECT `id` FROM " . $this->table . " WHERE `status_id`='1' AND `time_add`<DATE_SUB(NOW(), INTERVAL " . (int) $interval . " HOUR)";
		$array = Cms::$db->getAll($q);
		if ($array) {
			foreach ($array as $v) {
				$this->cancel($v['id']);
			}
		}
		return true;
	}

	function orderComplete($uid = '') 
    {   // zmeiniamy znacznik rozliczenia
		if ($uid) {
			$c = date("N");
			$q = "UPDATE " . $this->table . " SET `complete`='" . $c . "' WHERE md5(`id`)='" . addslashes($uid) . "' ";
			if (Cms::$db->update($q))
				return true;
		}
		return false;
	}

	function clearTxt($txt) 
    {
		$txt = preg_replace('/[^A-Za-z0-9 ]/', ' ', $txt);
		return $txt;
	}

	function decodeMonth($txt) 
    {
		switch ($txt) {
			case 'Jan': return '01';
			case 'Feb': return '02';
			case 'Mar': return '03';
			case 'Apr': return '04';
			case 'May': return '05';
			case 'Jun': return '06';
			case 'Jul': return '07';
			case 'Aug': return '08';
			case 'Sep': return '09';
			case 'Oct': return '10';
			case 'Nov': return '11';
			case 'Dec': return '12';
		}
	}

	public function getCustomerExists($login, $first_name, $last_name, $email) 
    {
		$q = "SELECT `id` FROM `" . DB_PREFIX . "customer` WHERE `login`='" . $login . "' OR (`first_name` LIKE '%" . $first_name . "%' AND `last_name` LIKE '%" . $last_name . "%' AND `email`='" . $email . "') ";
		if ($v = Cms::$db->getRow($q)) {
			return $v;
		}
		return false;
	}

	function changeStatus($orders) 
    {
		if ($orders) {
			foreach ($orders as $v) {
				$q = "UPDATE `" . $this->table . "` SET `status_id`='3', `time_complete`=NOW() WHERE `id`='" . (int) $v . "' AND `status_id`='2' ";
				Cms::$db->update($q);
//				saveHistory('o', $_SESSION[CUSTOMER_CODE]['login'], $v, 20);
			}
            Cms::getFlashBag()->add('info', 'Zmieniono status na ZREALIZOWANE dla wybranych zamówień.');
			return true;
		}
        Cms::getFlashBag()->add('error', 'Nie wybrano zamówień.');
		return false;
	}

	public function loadOrderUid($uid = '') 
    {
		if (!empty($uid)) {
			$q = "SELECT `id`, `time_add`, `comment`, `comment_admin` FROM `" . $this->table . "` WHERE md5(CONCAT(`id`, `time_add`))='" . $uid . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['uid'] = md5($v['id'] . $v['time_add']);
				return $v;
			}
		}
		return false;
	}

	public function saveOrderUid($post) 
    {
		$q = "SELECT `id`, `comment_admin` FROM " . $this->table . " WHERE md5(CONCAT(`id`, `time_add`))='" . addslashes($post['uid']) . "' ";
		$item = Cms::$db->getRow($q);
		if ($post['comment_admin'] AND $item['comment_admin'])
			$comment = '<br />' . addslashes($post['comment_admin']);
		elseif ($post['comment_admin'] AND ! $item['comment_admin'])
			$comment = addslashes($post['comment_admin']);

		$q = "UPDATE " . $this->table . " SET `comment_admin`=CONCAT(`comment_admin`, '" . $comment . "') WHERE `id`='" . (int) $item['id'] . "' ";
		if (Cms::$db->update($q))
			return true;
		return false;
	}

	public function loadCountry() 
    {
		$q = "SELECT c.* FROM `" . DB_PREFIX . "country` c LEFT JOIN `" . DB_PREFIX . "transport_zone` z ON c.zone_id=z.id WHERE z.active='1' ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

	public function orderEditAjax($cid = '') 
    {
		if ($cid) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE md5(`id`)='" . addslashes($cid) . "' ";
			$item = Cms::$db->getRow($q);
			$item['cid'] = md5($item['id']);
			return $item;
		}
		return false;
	}

	public function orderEditSave($post) 
    {
		$post = maddslashes($post);        
        
        $entity = $this->getById($post['order_id']);
        unset($post['order_id']);
        unset($post['action']);

        $basket = new BasketModel();
        $transport = $basket->getDelivery($post['delivery_service'], $entity['payment_id']);        

        $paramsOrder = array('weight', 'tracking');
        $paramsAddress = array('company_name', 'nip', 'first_name', 'last_name', 'address1',
                               'address2', 'address3', 'post_code', 'city', 'country', 'email', 'phone',
                               'shipping_company_name', 'shipping_nip', 'shipping_first_name', 'shipping_last_name', 'shipping_address1',
                               'shipping_address2', 'shipping_address3', 'shipping_post_code', 'shipping_city', 'shipping_country', 'shipping_country', 'shipping_phone',
        );
        
        $paramsTransport = array('delivery_service');
                        
        $orderAddress = [];
        foreach ($post as $key => $value) {            
            if (in_array($key, $paramsAddress) && $value != $entity[$key]) {
                $orderAddress[$key] = $value;
            }
        }

        $where = "WHERE `order_id`= '" . $entity['id'] ."' LIMIT 1";
        
        if ($orderAddress) {
            $q2 = "UPDATE `" . $this->table . "_address` SET ";
            $lastElement = end($orderAddress);
            
            foreach ($orderAddress as $key => $value) {
                $q2.= "`" . $key . "`='" . $value . "' ";
                
                if ($value !== $lastElement) {
                    $q2 .= ", ";
                }           
            }
            
            $q2.= $where;
            Cms::$db->update($q2);
        }
        
        foreach ($post as $key => $value) {
            
            if (in_array($key, $paramsOrder) && $value != $entity['weight'] && $value != $entity['tracking']) {
                $q1 = "UPDATE `" . $this->table . "` SET `" . $key . "`='" . $value . "' ";
                Cms::$db->update($q1);
            }

            if (in_array($key, $paramsTransport) && $post['delivery_service'] != $entity['transport_option_id'] && $transport) {
                $q3 = "UPDATE `" . $this->table . "_transport` SET `courier_id`='" . $transport['courier_id']. "', "
                        . "`courier_name`='" . $transport['courier_name'] . "', `service_id`='" . $transport['service_id'] . "', `service_name`='" . $transport['service_name'] . "', "
                        . "`region_id`='" . $transport['region_id'] . "', `region_name`='" . $transport['region_name'] . "', `option_id`='" . $transport['id'] . "', "
                        . "`tax`='" . $transport['tax'] . "' "; 

                $q3.= $where;
                Cms::$db->update($q3);
                
                $params = array(
                    'userId' => addslashes($_SESSION[USER_CODE]['id']),
                    'before' => $entity['transport_option_id'],
                    'after' => $value,
                    'attribute' => $key,
                );       
                
                Cms::orderLogSave($entity['id'], OrderLog::ACTION_ORDER_EDIT, $params);
            }
        }

        unset($post['delivery_service']);
        
        foreach ($post as $key => $value) {
            $params = array(
                'userId' => addslashes($_SESSION[USER_CODE]['id'])
            );            
            
            if ($entity[$key] != $value) {
                        
                $params['before'] = $entity[$key];
                $params['after'] = $value;
                $params['attribute'] = $key;

                Cms::orderLogSave($entity['id'], OrderLog::ACTION_ORDER_EDIT, $params);
            }
        }
        
        Cms::getFlashBag()->add('info', $GLOBALS['LANG']['o_info1']);
        
        return $entity['id'];

//		if (isset($post['cid'])) {
//			$z = 0;
//			$a = array('company_name', 'nip', 'first_name', 'last_name', 'address1', 'address2', 'address3', 'post_code', 'city', 'country', 'email', 'phone');
//			$item = $this->orderEditAjax($post['cid']);
//			$info = $item['info'] . "\n" . 'Zmieniono przez: <b>' . $_SESSION[CUSTOMER_CODE]['login'] . '</b> dnia: <b>' . date("Y-m-d H:i:s") . '</b>';
//			$q = "UPDATE `" . $this->table . "` SET ";
//			foreach ($a as $v) {
//				if ($item[$v] != $post[$v]) {
//					if ($z == 1)
//						$q.= ", ";
//					$q.= "`" . $v . "`='" . $post[$v] . "' ";
//					$info.= "\n" . '<b>' . $v . '</b> z: <b>' . $item[$v] . '</b> na: <b>' . $post[$v] . '</b>';
//					$z = 1;
//				}
//			}
//			if ($z == 1) {
//				$q.= "WHERE md5(`id`)='" . $post['cid'] . "' ";
//				if (Cms::$db->update($q)) {
//					$q = "UPDATE `" . $this->table . "` SET `info`='" . $info . "' WHERE md5(`id`)='" . $post['cid'] . "' ";
//					Cms::$db->update($q);
//                    Cms::getFlashBag()->add('info', $GLOBALS['LANG']['o_info1']);
//					return $item['id'];
//				}
//			}
//		}
//		Cms::$tpl->setError($GLOBALS['LANG']['o_error1']);
//		return $item['id'];
	}
    
//	public function orderEditSave($post) {
//		$post = maddslashes($post);
//		if (isset($post['cid'])) {
//			$z = 0;
//			$a = array('company_name', 'nip', 'first_name', 'last_name', 'address1', 'address2', 'address3', 'post_code', 'city', 'country', 'email', 'phone');
//			$item = $this->orderEditAjax($post['cid']);
//			$info = $item['info'] . "\n" . 'Zmieniono przez: <b>' . $_SESSION[CUSTOMER_CODE]['login'] . '</b> dnia: <b>' . date("Y-m-d H:i:s") . '</b>';
//			$q = "UPDATE `" . $this->table . "` SET ";
//			foreach ($a as $v) {
//				if ($item[$v] != $post[$v]) {
//					if ($z == 1)
//						$q.= ", ";
//					$q.= "`" . $v . "`='" . $post[$v] . "' ";
//					$info.= "\n" . '<b>' . $v . '</b> z: <b>' . $item[$v] . '</b> na: <b>' . $post[$v] . '</b>';
//					$z = 1;
//				}
//			}
//			if ($z == 1) {
//				$q.= "WHERE md5(`id`)='" . $post['cid'] . "' ";
//				if (Cms::$db->update($q)) {
//					$q = "UPDATE `" . $this->table . "` SET `info`='" . $info . "' WHERE md5(`id`)='" . $post['cid'] . "' ";
//					Cms::$db->update($q);
//                    Cms::getFlashBag()->add('info', $GLOBALS['LANG']['o_info1']);
//					return $item['id'];
//				}
//			}
//		}
//		Cms::$tpl->setError($GLOBALS['LANG']['o_error1']);
//		return $item['id'];
//	}

	public function orderCustomerAjax($cid = '') 
    {
		if ($cid) {
			$q = "SELECT * FROM `" . DB_PREFIX . "customer` WHERE md5(`id`)='" . addslashes($cid) . "' ";
			$item = Cms::$db->getRow($q);
			$item['cid'] = md5($item['id']);
			return $item;
		}
		return false;
	}

	public function orderProductAjax($cid = '') 
    {
		if ($cid) {
			$q = "SELECT p.*, d.*, t.value as tax_value FROM `" . DB_PREFIX . "shop_products` p LEFT JOIN `" . DB_PREFIX . "shop_products_desc` d ON p.id=d.parent_id ";
			$q.= "LEFT JOIN `" . DB_PREFIX . "shop_tax` t ON p.tax_id=t.id ";
			$q.= "WHERE d.lang_id='" . _ID . "' AND md5(p.id)='" . $cid . "' ";
			$item = Cms::$db->getRow($q);
			$item = mstripslashes($item);
			$item['price_gross'] = formatPrice($item['price'], $item['tax_value']);
			$item['price_purchase_gross'] = formatPrice($item['price_purchase'], $item['tax_value']);
			$item['price_promotion_gross'] = formatPrice($item['price_promotion'], $item['tax_value']);
			$item['price_promotion_vat'] = formatPrice($item['price_promotion_gross'] - $item['price_promotion']);
			$item['price_purchase_vat'] = formatPrice($item['price_purchase_gross'] - $item['price_purchase']);
			$item['price_rrp'] = formatPrice($item['price_rrp']);
			$item['price'] = formatPrice($item['price']);
			$item['price_purchase'] = formatPrice($item['price_purchase']);
			$item['price_promotion'] = formatPrice($item['price_promotion']);
			$item['price_vat'] = formatPrice($item['price_gross'] - $item['price']);
			$item['url'] = CMS_URL . '/admin/shop-products.html?action=edit&amp;id=' . $item['id'];

			$q = "SELECT * FROM `" . DB_PREFIX . "shop_photos` WHERE `parent_id`='" . $item['id'] . "' ORDER BY `order` ASC ";
			$array = Cms::$db->getAll($q);
			if (isset($array)) {
				foreach ($array as $v) {
					$fileNameM = changeFileName($v['file'], '_m');
					$item['photo'][] = SERVER_URL . CMS_URL . '/files/products/' . $fileNameM;
				}
			}
			return $item;
		}
		return false;
	}

}