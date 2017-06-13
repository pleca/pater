<?php 
require_once(CLASS_DIR . '/Api/Shop/MethodInterface.php'); 
require_once(MODEL_DIR . '/Category.php'); 

class GetCategories implements MethodInterface {
    protected $apiShop;
	
	public function __construct(ApiShop $apiShop) {
		$this->apiShop = $apiShop;
	}
	
    public function execute() {		
		$category = new Category;

		$entities = $category->getAll($this->apiShop->getParams(), $this->apiShop->getFields());
//		$categories = $category->getAll(['locale' => CMS::$defaultLocale, 'parent_id' => 0]);
		dump($entities);
//		die;

		$ids = [];
		if ($entities) {
			foreach ($entities as $entity) {
				$ids[] = $entity['id'];
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