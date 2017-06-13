<?php 
require_once(CLASS_DIR . '/Stock/StockUpdateInterface.php'); 

class StockUpdate {
	private $strategy;
	
	public function setStrategy(StockUpdateInterface $obj) {
		$this->strategy = $obj;
	}
	
	public function getStrategy() {
		return $this->strategy;
	}
	
}