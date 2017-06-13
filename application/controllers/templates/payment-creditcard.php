<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}
if (Cms::$modules['shop'] != 1) {
	die('This module is disabled!');
}

require_once(MODEL_DIR . '/PaymentModel.php');
require_once(MODEL_DIR . '/shopOrders.php');
require_once(LIB_DIR . '/paypalApi/PayPalApi.php');
require_once(MODEL_DIR . '/phrase.php');
require_once(MODEL_DIR . '/customer.php');
require_once(ENTITY_DIR . '/OrderLog.php');

use Application\Entity\OrderLog;

class Creditcard {

	private $order;
	private $payment;
    private $phrase;
    private $customer;
    
	public function __construct() {
		$this->order = new Orders();
		$this->payment = new PaymentModel();
        $this->phrase = new Phrase();
        $this->customer = new Customer();        
	}

	public function init($params = '') {
		$action = isset($_POST['action']) ? $_POST['action'] : 'list';
		$action .= 'Action';
		$this->$action();
	}
	
	public function __call($method = '', $args = '') {
		error_404();
	}
	
	public function listAction() {			
		if (!isset($_SESSION['order_id'])) { // czy w sesji jest ID nowego zamowienia
			Cms::getFlashBag()->add('info', 'Brak ID zamówienia');
			return false;
		}
		if (!$order = $this->order->getById($_SESSION['order_id'])) { // czy w bazie jest zamoweinie
			Cms::getFlashBag()->add('info', 'Brak zamówienia w systemie.');
			return false;
		}
		if ($order['status_id'] != 1) { // czy nie oplacone
			Cms::getFlashBag()->add('info', 'Zamówienie już opłacone.');
			return false;
		}

		$payment = $this->payment->getById($order['payment_id'])[0];	
		
		if ($payment['name_url'] != 'creditcard') { // wybrano inna metode platnosci
			Cms::getFlashBag()->add('info', 'Wybrano inna metode platnosci.');
			return false;
		}

        if ($this->doPayment($order)) {
            
			$data = array(
				'order' => $order
			);
			
			echo Cms::$twig->render('templates/payment/payment-succeed.twig', $data);
        } else {
			
			$data = array(
				'title' => 'Paypal Express Credit Card'
			);
			
			echo Cms::$twig->render('templates/paypal/paypal-express-credit-card.twig', $data);
        }
                          
	}
    
    protected function usePhrase() {
        $result = false;
        $params = [];
        
        $params['promotion_code'] = isset($_SESSION[CUSTOMER_CODE]['promotion_code']) ? $_SESSION[CUSTOMER_CODE]['promotion_code'] : '';
        
        // dopisuje do bazy użycie frazy promocyjnej, o ile ta fraza istnieje
        if(isset($_SESSION[CUSTOMER_CODE]['promotion_code']))
        {
            $customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
            $result = $this->phrase->usePhrase($_SESSION[CUSTOMER_CODE]['promotion_code'], $_SESSION['order_id'], $customer_id);
            
            $params['customer_id'] = $customer_id;
            
            $customer = $this->customer->loadById($customer_id);                        

            $_SESSION[CUSTOMER_CODE]['discount'] = $customer['discount'];
            $_SESSION[CUSTOMER_CODE]['promotion_code'] = '';
        }
        
        $params['result'] = (bool) $result;
        
        Cms::orderLogSave($_SESSION['order_id'], OrderLog::ACTION_ORDER_USE_PHRASE, $params);          
    }
    
    private function doPayment(array $order)
    {
        $oPayPal = new PayPalApi();

        try
        {
            $oPayPal->setOrder($order);
        }
        catch (Exception $ex)
        {
            Cms::$log->LogError('Paypal set order: ' . $ex);
        }

        $customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';

        $params = array(
            'customerId' => $customer_id,
            'payment_type' => OrderLog::PAYMENT_TYPE_CREDIT_CARD,
            'before' => 1
        );

        try
        {
            // uzywane w przypadku platnosci karta
            $oPayPal->setExpressCreditCard(true);

            $response = $oPayPal->processExpress(CREDIT_CARD_RETURN_URL, CANCEL_URL);
            $paypalTransactionId = isset($response['PAYMENTINFO_0_TRANSACTIONID']) ? urlencode($response['PAYMENTINFO_0_TRANSACTIONID']) : false;
            $paypalAmountFee = urlencode($response['PAYMENTINFO_0_FEEAMT']);

            if (!$paypalTransactionId)
            {
                if ($oPayPal->isError())
                {
                    $params['error'] = $oPayPal->getErrors()[0];
                    // przekaz klientowi ten blad
                    Cms::getFlashBag()->add('error', $oPayPal->getErrors()[0]);
                } else
                {
                    // przekaz klientowi jako blad zawartosc $sExceptionError i - wrzuc do logow to,
                    // ze cos poszlo z naszym systemem nie tak i/lub wyslij email do Marka/Jarka/Marcina
                    Cms::getFlashBag()->add('error', PayPalApi::EXCEPTION_ERROR);
                    $params['error_for_client'] = PayPalApi::EXCEPTION_ERROR;
                }

                $data = ['after' => 1];
                $params = array_merge($params, $data);
                Cms::orderLogSave($_SESSION['order_id'], OrderLog::ACTION_ORDER_PAYMENT, $params);
            } else
            {
                // PLATNOSC Z POWODZENIEM PRZESZLA, MOZESZ WZIAC Z BIBLIOTEKI ID ZAMOWIENIA I DALEJ ROBIC CO TRZEBA
                // np. email do klienta, email do admina, zmiana statusu zamowienia, ...
                $orderId = $oPayPal->getOrderId();

                //email do klienta i admina poszedl przy zapisie zamowienia
                $this->order->setStatus($_SESSION['order_id'], 2);
                $this->order->setData($_SESSION['order_id'], ['paypal_transaction_id' => $paypalTransactionId, 'paypal_amount_fee' => $paypalAmountFee]);
                $this->usePhrase();

                $data = ['paypal_transaction_id' => $paypalTransactionId, 'paypal_amount_fee' => $paypalAmountFee, 'after' => 2];
                $params = array_merge($params, $data);

                Cms::orderLogSave($_SESSION['order_id'], OrderLog::ACTION_ORDER_PAYMENT, $params);

                return true;
            }
        }
        catch (Exception $ex)
        {
            $data = ['error' => $ex];
            $params = array_merge($params, $data);
            Cms::orderLogSave($_SESSION['order_id'], OrderLog::ACTION_ORDER_PAYMENT, $params);

            // zapisac wyjatek do logow
            Cms::$log->LogError('Paypal: ' . $ex);
        }
    }
	
}

$Creditcard = new Creditcard();
$Creditcard->init();
