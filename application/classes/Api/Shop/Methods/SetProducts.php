<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Variation.php'); 

class SetProducts implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
	
    public function execute() {
        echo 'setProducts execute';
		
		$entity = new Variation();
		$variations = $this->apiShop->getParams()['products'];		
		
		$variations = array(
			array('id' => 148, 'variation_id' => 318, 'qty' => 4, 'price' => 25),	//0,0100
			array('id' => 148, 'variation_id' => 319, 'qty' => 5, 'price' => 266),	//0,0100
		);
		
		$generalStatus = 0;
		$count = 0;
		$ids = [];
		
		if ($variations) {
			$results = [];
			foreach ($variations as $variation) {
				
				$params = array(
					'product_id' => $variation['id'],
					'id2' => $variation['variation_id']
				);
				
				$params2 = array(
					'id' => $variation['id'],
					'variation_id' => $variation['variation_id']
				);
				
				unset($variation['id']);
				unset($variation['variation_id']);

				if ($entity->updateBy($params, $variation)) {
					$status = ApiShop::RESPONSE_STATUS_SUCCESS;
				} else {
					$status = ApiShop::RESPONSE_STATUS_ERROR;
				}		
								
				$results[] = array_merge($params2, ['status' => $status]);
			}
			
			foreach ($results as $result) {
				if ($result['status'] == 1) {
					$generalStatus = 1;
					break;
				}
			}
			
			foreach ($results as $result) {
				if ($result['status'] == 1) {
					$ids[] = $result['variation_id'];
					$count++;					
				}
			}			
		}
		
		$response = array(
			'status' => $generalStatus,
			'method' => $this->apiShop->getRequestMethod(),
			'count' => $count,
			'ids' => $ids,
			'data' => $results,
		);
		
		$this->apiShop->setResponse($response);		
		$this->apiShop->getResponse($this->apiShop->getResponseType());
    }
}