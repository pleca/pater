<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Order.php'); 

class SetOrder implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
	
    public function execute() {
		$entity = new Order;
		$order = $this->apiShop->getParams()['order'];
		
//		working example, remove checking order
//		$order = [];
//		$order['id'] = 55;
//		$order['status_id'] = 2; //1
//		$order['time_complete'] = '2016-01-01 23:45:45'; // ''
//		$order['tracking'] = 5;
		
		$id = $order['id'];
		unset($order['id']);		

		$count = 0;
		if ($count = $entity->updateById($id, $order)) {
			$status = ApiShop::RESPONSE_STATUS_SUCCESS;
		} else {
			$status = ApiShop::RESPONSE_STATUS_ERROR;
		}

		$response = array(
			'status' => $status,
			'method' => $this->apiShop->getRequestMethod(),
			'count' => $count,
			'id' => $id,
		);
		
		$this->apiShop->setResponse($response);		
		$this->apiShop->getResponse($this->apiShop->getResponseType());			
    }
}