<?php
namespace Service;

use Database\ConnectDB;

class MyCustomService{
    
    protected $conn;
    
    public function __construct(ConnectDB $conn) {
        $this->conn = $conn;
    }
}
