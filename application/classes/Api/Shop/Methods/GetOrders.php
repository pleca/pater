<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Order.php'); 
require_once(MODEL_DIR . '/OrderProduct.php'); 
require_once(MODEL_DIR . '/OrderAddress.php'); 
require_once(MODEL_DIR . '/OrderTransport.php'); 

class GetOrders implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
		
    public function execute() {
		$order = new Order;
		$orderProduct = new OrderProduct();
		$orderProducts = $orderProduct->getAll();
		
		$sortedOrderProducts = [];
		foreach ($orderProducts as $orderProduct) {
			$sortedOrderProducts[$orderProduct['order_id']][] = $orderProduct;
		}
		
		$orderAddress = new OrderAddress();
		$orderAddresses = $orderAddress->getAllForGa();
		$orderAddresses = getArrayByKey($orderAddresses, 'order_id');
		
		$orderTransport = new OrderTransport();
		$orderTransports = $orderTransport->getAllForGa();
		$orderTransports = getArrayByKey($orderTransports, 'order_id');

		$orders = $order->getBy($this->apiShop->getParams(), $this->apiShop->getFields());
			dump($orders);
		$ids = [];
		if ($orders) {			
			foreach ($orders as &$order) {
				$order['products'] = $sortedOrderProducts[$order['id']];
				$order['address'] = $orderAddresses[$order['id']];
				$order['transport'] = $orderTransports[$order['id']];
				$ids[] = $order['id'];
			}
		}
		
		$response = array(
			'status' => ApiShop::RESPONSE_STATUS_SUCCESS,
			'method' => $this->apiShop->getRequestMethod(),
			'count' => count($ids),
			'ids' => implode(',', $ids),
		);
		
		$this->apiShop->setResponse($response);		
		$this->apiShop->getResponse($this->apiShop->getResponseType());	
    }
}