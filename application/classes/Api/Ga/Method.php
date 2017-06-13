<?php

require_once(CLASS_DIR . '/Api/Ga/ApiGa.php'); 

class Method {
    
    private $responceFactory;
    
    public function __construct(ResponceFactory $factory) {
    $this->responceFactory = $factory;
    }

    public function execute() {
        $responce = $this->responceFactory->create();

        echo $method->execute();
    }
}