<?php 

interface StockUpdateInterface {
	
	public function update();
	
	public function notifyAdminNotUpdated();
	
	public function init();
	
	public function setEanColumn($eanColumn);
	
	public function getEanColumn();
    
}