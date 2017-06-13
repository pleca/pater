<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Producers.php'); 

class GetManufacturers implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
	
    public function execute() {
		$producer = new Producers;
		$producers = $producer->getBy($this->apiShop->getParams(), array_merge($this->apiShop->getFields(), ['id']));

		$ids = [];
		if ($producers) {
			foreach ($producers as $producer) {
				$ids[] = $producer['id'];
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