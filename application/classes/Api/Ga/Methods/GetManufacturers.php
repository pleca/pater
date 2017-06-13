<?php 
require_once(CLASS_DIR . '/Api/Ga/MethodInterface.php'); 

class GetManufacturers implements MethodInterface {
    
    public function execute() {
        echo 'getmanufacturers execute';
    }
}