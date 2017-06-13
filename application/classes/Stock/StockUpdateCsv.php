<?php 

require_once(CLASS_DIR . '/Stock/StockUpdateInterface.php'); 

class StockUpdateCsv implements StockUpdateInterface {
	
	private $eanColumn;
		
	public function setEanColumn($eanColumn) {
		$this->eanColumn = $eanColumn;
	}
	
	public function getEanColumn() {
		return $this->eanColumn;
	}

	public function init() {
		
	}

	public function update() {
		echo 'strategia csv';
		dump($_POST);		
		return ;
	}
	
	public function notifyAdminNotUpdated() {
		echo 'strategia csv';
		dump($_POST);		
		return ;
	}	
}