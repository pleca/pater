<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/api/orderLog.php');
require_once(MODEL_DIR . '/shopOrders.php');

class ApiOrders {

	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'order';
		$this->order = new Orders();
	}

	public function __destruct() {
		
	}

	public function get_all($status_id = 2) {
		$q = "SELECT a.* FROM `" . $this->table . "` a WHERE a.status_id='" . (int) $status_id . "' ";
		$array = Cms::$db->getAll($q);

		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['products'] = array();

			$q = "SELECT a.* FROM `" . $this->table . "_product` a WHERE a.order_id='" . (int) $v['id'] . "' ";
			$array2 = Cms::$db->getAll($q);
			foreach ($array2 as $p) {
				$p = mstripslashes($p);
				$v['products'][] = $p;
			}
			
			$q = "SELECT a.*, b.code as `shipping_country_code` FROM `" . $this->table . "_address` a LEFT JOIN `" . DB_PREFIX . "transport_country` b ON a.shipping_country=b.id ";
			$q.= "WHERE a.order_id='" . (int) $v['id'] . "' AND a.model='billing' LIMIT 1 ";
			if($t = Cms::$db->getRow($q)) {
				$t = mstripslashes($t);
				$v['address'] = $t;
			}

			$q = "SELECT a.*, b.name_online FROM `" . $this->table . "_transport` a LEFT JOIN `" . DB_PREFIX . "transport_service` b ON a.service_id=b.id ";
			$q.= "WHERE a.order_id='" . (int) $v['id'] . "' LIMIT 1 ";
			if($t = Cms::$db->getRow($q)) {
				$t = mstripslashes($t);
				$t['price_gross'] = formatPrice($t['price'], $t['tax']);
				$v['transport'] = $t;
			}
			
			$items[] = $v;
		}
		return $items;
	}

	public function get_by_id($id = 0) {
		if ($id > 0) {
			$q = "SELECT a.* FROM `" . $this->table . "` a WHERE a.id='" . (int) $id . "' ";
			if ($item = Cms::$db->getRow($q)) {
				$item = mstripslashes($item);
				return $item;
			}
		}
		return false;
	}

	public function set_status($id = 0, $data = '') 
    {
		$data = maddslashes($data);

		$q = "UPDATE `" . $this->table . "` SET `status_id`='" . (int) $data['status_id'] . "' ";
//		$q.= ", `comment`='" . $data['comment'] . "' ";
		$q.= ", `time_complete`='" . $data['date_complete'] . "' ";
		$q.= ", `tracking`='".$data['tracking']."' ";
		$q.= "WHERE `id`='" . (int) $data['online_id'] . "' ";
        
        $entity = $this->get_by_id($data['online_id']);
        
        $result = Cms::$db->update($q);                
        
        $paramsStatus = array(
            'result' => (bool) $result,
            'tracking' => $data['tracking'],
            'time_complete' => $data['date_complete'],
            'before' => $entity['status_id'],
            'after' => $data['status_id']
        );

        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');
        
        $orderData = array(
            'orderId' => $data['online_id'],
            'action' => OrderLog::ACTION_ORDER_UPDATE_STATUS_BY_GA,
            'params' => json_encode($paramsStatus),
            'date'  =>  $date
        );      
        
        $orderLog = new OrderLog();
        $orderLog->insert($this->table . '_logs', $orderData);   
        
        if (isset($data['tracking']) && $data['tracking']) {
            $paramsTracking = array(
                'result' => (bool) $result,            
                'time_complete' => $data['date_complete'],
                'before' => $entity['tracking'],
                'after' => $data['tracking']
            );
            
            $orderData['action'] = OrderLog::ACTION_ORDER_UPDATE_TRACKING_BY_GA;
            $orderData['params'] = json_encode($paramsTracking);                

            $orderLog->insert($this->table . '_logs', $orderData);              
        }
        
        switch ($data['status_id']) {
            case 2:
                $resultEmail = $this->order->sendEmailOrderPaymentCustomer(['order_id' => $data['online_id']]);
                break;
            case 3:
                $resultEmail = $this->order->sendEmailOrderDispatchedCustomer(['order_id' => $data['online_id']]);
                break;    
            default:                        
                break;
        }
        
        if (in_array($data['status_id'], [2,3])) {

            $params = array(
                'result' => $resultEmail ? $resultEmail : $this->mailer->getError(),
                'sender' => 'api ga',
                'email' => $entity['email']
            );

            Cms::orderLogSave($data['online_id'], OrderLog::ACTION_ORDER_STATUS_EMAIL_NOTIFICATION_SEND, $params);  
        }
        
        return (bool) $result;
	} 

}
