<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['config'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['config'] != 1)
	die('No permission at this level!');

require_once(MODEL_DIR . '/EmailTemplate.php');

$emailTemplate = new EmailTemplate();
$entities = $emailTemplate->getAll();

if (isset($_GET['action']) AND $_GET['action'] == 'edit') {

	$data = array(
		'entities' => $entities,
		'pageTitle' => $GLOBALS['LANG']['email_title'] . ': ' . $_GET['name']
	);	
	
	echo Cms::$twig->render('admin/email_templates/edit.twig', $data);

} elseif (isset($_GET['action']) AND $_GET['action'] == 'test') {
        
    runEmailTemplateTest();

    Cms::getFlashBag()->add('info', $GLOBALS['LANG']['sent_email_test']);
    redirect(URL . '/admin/email-templates.html');
    
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {

	if ($emailTemplate->edit($_POST, $entities)) {
		redirect(URL . '/admin/email-templates.html');
	}

} else {

	$data = array(
		'entities' => $entities,
		'pageTitle' => $GLOBALS['LANG']['email_title']
	);
	
	echo Cms::$twig->render('admin/email_templates/list.twig', $data);
}


function runEmailTemplateTest() 
{
    $data = array(
        'email' => $_SESSION[USER_CODE]['email'],
        'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
    );
    
    switch ($_GET['name']) {
        case 'contact_form':
            require_once(MODEL_DIR . '/Contact.php');
            $contact = new Contact();
            
            $params = array(
                'contact' => ['name' => 'Customer service'],
                'subject' => 'Your subject',
                'content' => 'Your content',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],
                'phone' => '222222222222222',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#SUBJECT#', '#SECTION#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 'Your subject', 'Customer service'],
            );
          
            $contact->sendEmailCustomer(array_merge($data, $params));
            Cms::getFlashBag()->get('info');            
            
            break;
        case 'contact_form_admin':
            require_once(MODEL_DIR . '/Contact.php');
            $contact = new Contact();
            
            $params = array(
                'contact' => ['name' => 'Customer service', 'email' => $_SESSION[USER_CODE]['email']],
                'subject' => 'Your subject',
                'content' => 'Your content',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],
                'phone' => '3333333333333',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#SUBJECT#', '#SECTION#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 'Your subject', 'Customer service'],
            );
            
            $contact->sendEmailAdmin(array_merge($data, $params));  
            Cms::getFlashBag()->get('info');
            break;   
        case 'customer_add_active':
            require_once(MODEL_DIR . '/customer.php');
            $customer = new Customer();
            
            $params = array(
                'template_name' => 'customer_add_active',
                'login2' => $_SESSION[USER_CODE]['login'],
                'pass2' => 'Your password',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],
            );
            
            $replace = array($data['login2'], $data['pass2'], $data['first_name'], $data['last_name'], $data['email'], Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $data['server_url']);
            $customer->sendEmailCustomer(array_merge($data, $params));
            Cms::getFlashBag()->get('info');
            break;
        case 'customer_add_active_admin':
            require_once(MODEL_DIR . '/customer.php');
            $customer = new Customer();
            
            $params = array(
                'template_name' => 'customer_add_active',
                'email_admin' => $data['email'],
                'customer_id' => 1,
                'login2' => $_SESSION[USER_CODE]['login'],
                'pass2' => 'Your password',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],                
            );

            $customer->sendEmailAdmin(array_merge($data, $params));
            Cms::getFlashBag()->get('info');
            
            break;
        case 'customer_add_inactive':
            require_once(MODEL_DIR . '/customer.php');
            $customer = new Customer();

            $params = array(
                'template_name' => 'customer_add_inactive',
                'email_admin' => $data['email'],
                'customer_id' => 1,
                'login2' => $_SESSION[USER_CODE]['login'],
                'pass2' => 'Your password',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],                
            );  
            
            $customer->sendEmailCustomer(array_merge($data, $params));            
            break;
        case 'customer_add_inactive_admin':
            require_once(MODEL_DIR . '/customer.php');
            $customer = new Customer();

            $params = array(
                'template_name' => 'customer_add_inactive',
                'email_admin' => $data['email'],
                'customer_id' => 1,
                'login2' => $_SESSION[USER_CODE]['login'],
                'pass2' => 'Your password',
                'first_name' => $_SESSION[USER_CODE]['name'],
                'last_name' => $_SESSION[USER_CODE]['surname'],                
            );            
            
            $customer->sendEmailAdmin(array_merge($data, $params));        
            break;        
        case 'customer_new_password':
            require_once(MODEL_DIR . '/customer.php');
            $customer = new Customer();
            
            $params = array(
                'customer' => array(
                    'login' => $_SESSION[USER_CODE]['login'],
                    'first_name' => $_SESSION[USER_CODE]['name'],
                    'last_name' => $_SESSION[USER_CODE]['surname'],                    
                ),
                'template_name' => 'customer_add_inactive',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME']],                
                'new_password' => 'Your new password'
            );             
            
            $customer->sendEmailNewPasswordCustomer(array_merge($data, $params));
            break;        
        case 'notifications_stock_availability':
            $mailer = new Mailer();
            $productUrl = '<a href="#" title="Product name">Product name</a>';
            $template = $emailTemplate->getTemplate('notifications_stock_availability');		
            $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';        

            //title
            $searchTitle = array('#COMPANY_NAME#', '#DOMAIN#', '#PRODUCT#');
            $replaceTitle = array(Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 'Product name');
            $title = str_replace($searchTitle, $replaceTitle, $template['title']);

            $serverUrl = '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>';
            $search = array('#PRODUCT#','#COMPANY_NAME#', '#DOMAIN#', '#SERVER_URL#');
            $replace = array($productUrl, Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $serverUrl);
            $content = str_replace($search, $replace, $template['content']);

            // wysylanie do klienta
            $mailer->setSubject($title);
            $mailer->setBody($content); 
            $mailer->sendHTML($data['email']);
            $mailer->ClearAllRecipients();
                
            break;        
        case 'notifications_stock_products_not_updated':
            break;        
        case 'order_add_registered':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders();            
            
            $products = array(
                0 => ['name' => 'Product 1', 'desc' => 'Desc for product 1', 'price_gross' => 10, 'qty' => 1, 'sum' => 10],
                1 => ['name' => 'Product 2', 'desc' => 'Desc for product 2', 'price_gross' => 20, 'qty' => 2, 'sum' => 40],
                2 => ['name' => 'Product 3', 'desc' => 'Desc for product 3', 'price_gross' => 30, 'qty' => 1, 'sum' => 30],
            );
            
            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            
            $uid = URL . '/customer/order/' . md5(5 . $date) . '.html';
            $orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
            
            $params = array(
                'template_name' => 'order_add_registered',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 5],            
                'order_id' => 5,            
                'order_url' => $orderUrl,
                'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
                'order' => array(
                    'sum' => 80,
                    'total' => 85,
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'products'  => $products,
                    'email' => $data['email'],
                    'payment' => 'PayPal'
                ),
            );
            
            $order->sendEmailOrderAddCustomer(array_merge($data, $params));
            break;        
        case 'order_add_registered_admin':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders();            
            
            $products = array(
                0 => ['name' => 'Product 1', 'desc' => 'Desc for product 1', 'price_gross' => 10, 'qty' => 1, 'sum' => 10],
                1 => ['name' => 'Product 2', 'desc' => 'Desc for product 2', 'price_gross' => 20, 'qty' => 2, 'sum' => 40],
                2 => ['name' => 'Product 3', 'desc' => 'Desc for product 3', 'price_gross' => 30, 'qty' => 1, 'sum' => 30],
            );
            
            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            
            $uid = URL . '/customer/order/' . md5(5 . $date) . '.html';
            $orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
            
            $params = array(
                'template_name' => 'order_add_registered',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 5],            
                'order_id' => 6,            
                'order_url' => $orderUrl,
                'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
                'order' => array(
                    'sum' => 80,
                    'total' => 85,
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'products'  => $products,
                    'email' => $data['email'],
                    'payment' => 'PayPal',
                    'shipping_first_name' => $_SESSION[USER_CODE]['name'],
                    'shipping_last_name' => $_SESSION[USER_CODE]['surname']
                ),
            );            
            
            $order->sendEmailOrderAddAdmin(array_merge($data, $params));
            break;        
        case 'order_add_unregistered':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders();            
            
            $products = array(
                0 => ['name' => 'Product 1', 'desc' => 'Desc for product 1', 'price_gross' => 10, 'qty' => 1, 'sum' => 10],
                1 => ['name' => 'Product 2', 'desc' => 'Desc for product 2', 'price_gross' => 20, 'qty' => 2, 'sum' => 40],
                2 => ['name' => 'Product 3', 'desc' => 'Desc for product 3', 'price_gross' => 30, 'qty' => 1, 'sum' => 30],
            );
            
            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            
            $uid = URL . '/customer/order/' . md5(5 . $date) . '.html';
            $orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
            
            $params = array(
                'template_name' => 'order_add_unregistered',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 5],            
                'order_id' => 5,            
                'order_url' => $orderUrl,
                'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
                'order' => array(
                    'sum' => 80,
                    'total' => 85,
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'products'  => $products,
                    'email' => $data['email'],
                    'payment' => 'PayPal'
                ),
            );
            
            $order->sendEmailOrderAddCustomer(array_merge($data, $params));
            break;        
        case 'order_add_unregistered_admin':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders();            
            
            $products = array(
                0 => ['name' => 'Product 1', 'desc' => 'Desc for product 1', 'price_gross' => 10, 'qty' => 1, 'sum' => 10],
                1 => ['name' => 'Product 2', 'desc' => 'Desc for product 2', 'price_gross' => 20, 'qty' => 2, 'sum' => 40],
                2 => ['name' => 'Product 3', 'desc' => 'Desc for product 3', 'price_gross' => 30, 'qty' => 1, 'sum' => 30],
            );
            
            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            
            $uid = URL . '/customer/order/' . md5(5 . $date) . '.html';
            $orderUrl = '<a href="' . $uid . '" title="order">' . $uid . '</a>';
            
            $params = array(
                'template_name' => 'order_add_unregistered',
                'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#ORDER_ID#'],
                'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], 5],            
                'order_id' => 5,            
                'order_url' => $orderUrl,
                'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
                'order' => array(
                    'sum' => 80,
                    'total' => 85,
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'products'  => $products,
                    'email' => $data['email'],
                    'payment' => 'PayPal'
                ),
            );
            
            $order->sendEmailOrderAddAdmin(array_merge($data, $params));
            break;        
        case 'order_dispatched':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders(); 
            
            $params = array(
                'template_name' => 'order_dispatched',
                'order' => array(
                    'id' => 6,
                    'first_name' => $_SESSION[USER_CODE]['name'],
                    'last_name' => $_SESSION[USER_CODE]['surname'],
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'email' => $data['email'],
                    'tracking' => '234324234234324234',
                    'time_complete' => '2017-02-01 07:57:39',
                    'comment_admin' => 'Comment from admin'                    
                    ),
                'status' => array(
                    'color' => 'green',
                    'name' => 'order has been dispatched',
                )
            );
            
            $order->sendEmailOrderDispatchedCustomer(array_merge($data, $params));
            break;        
        case 'order_payment':
            require_once(MODEL_DIR . '/shopOrders.php');
            $order = new Orders(); 
            
            $params = array(
                'template_name' => 'order_dispatched',
                'order' => array(
                    'id' => 6,
                    'first_name' => $_SESSION[USER_CODE]['name'],
                    'last_name' => $_SESSION[USER_CODE]['surname'],
                    'transport_name' => 'Transport name',
                    'transport_price' => 5,
                    'email' => $data['email'],
                    'tracking' => '234324234234324234',
                    'time_complete' => '2017-02-01 07:57:39',
                    'comment_admin' => 'Comment from admin'                    
                    ),
                'status' => array(
                    'color' => 'blue',
                    'name' => 'payment accepted',
                )
            );
            
            $order->sendEmailOrderPaymentCustomer(array_merge($data, $params));            
            break;        
        default:                        
            break;
    }    
}


