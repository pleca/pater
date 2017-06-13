<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Product.php'); 

class GetProducts implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
	
    public function execute() {		
		$product = new Product;
		$entities = $product->getAll($this->apiShop->getParams(), $this->apiShop->getFields());

		$ids = [];
		if ($entities) {

			if (isset($this->apiShop->getParams()['locale'])) {
				foreach ($entities as $entity) {
					$ids[] = $entity['id'];
				}
			} else {
                foreach ($entities[Cms::$defaultLocale] as $entity) {
                    $ids[] = $entity['id'];
                }
			}
		}
		
		$response = array(
			'status' => ApiShop::RESPONSE_STATUS_SUCCESS,
			'type' => $this->apiShop->getRequestMethod(),
			'count' => count($ids),
			'ids' => implode(',', $ids),
		);
		
		$this->apiShop->setResponse($response);		
		$this->apiShop->getResponse($this->apiShop->getResponseType());	
    }
}