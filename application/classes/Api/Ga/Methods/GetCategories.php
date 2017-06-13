<?php 
require_once(CLASS_DIR . '/Api/Ga/MethodInterface.php'); 

class GetCategories implements MethodInterface {
    
    public function execute() {
        echo 'getCategories execute';
    }
}