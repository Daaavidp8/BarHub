<?php

namespace App\Models;

use \PDO;

class DB
{
    private $host = '172.17.0.2';
    private $user = 'david';
    private $pass = '1234';
    private $dbname = 'BarHub';

    public function connect(): PDO
    {
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}




