<?php

namespace Database;

use Database\Builder\SelectBuilder;
use Database\Builder\InsertBuilder;
use Database\Builder\UpdateBuilder;
use ConfigReader\ConfigReader;

class ConnectDB {

    public $debug = array("select" => array(), "insert" => array(), "update" => array(), "proc stock" => array());
    protected $host;
    protected $user;
    protected $pwd;
    protected $name;
    protected $conn;
    protected static $config_require = array("host", "user", "pwd", "name");

    /**
     * 
     * @param string $host
     * @param string $user
     * @param string $pwd
     * @param string $name
     */
    public function __construct($host = "", $user = "", $pwd = "", $name = "") {
        // read config to get database config
        if (!empty($args = func_get_args())) {
            $this->host = $host;
            $this->user = $user;
            $this->pwd = $pwd;
            $this->name = $name;
        } else if (is_array($config = ConfigReader::getJsonConfig("database"))) {
            // check if all database configuration data are define
            if ($this->isDatabaseConfigValid($config)) {
                // set database config
                $this->host = $config['host'];
                $this->user = $config['user'];
                $this->name = $config['name'];
                $this->pwd = $config['pwd'];
            }
        }
        $this->conn = null;
    }

    /**
     * Open the database connection
     */
    public function openConnection() {
        // Create connection
        if (!is_null($this->pwd)) {
            $this->conn = new \PDO("mysql:host=$this->host;dbname=$this->name", $this->user, $this->pwd);
        } else {
            $this->conn = new \PDO("mysql:host=$this->host;dbname=$this->name", $this->user);
        }
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Call a stored procedure
     * Throws an exception if database connection is not open
     * 
     * @param string $procedure_name
     * @param array $arguments
     * @return array
     * @throws \Exception
     */
    public function call($procedure_name, $arguments = array()) {
        return $this->execute("CALL " . $procedure_name, $arguments, "proc stock");
    }

    /**
     * Execute the query
     * Throws an exception if database connection is not open
     * 
     * @param string $query
     * @param array $arguments
     * @return array
     */
    public function execute($query, array $arguments = array(), $type = "select", $close = true) {
        if (is_null($this->conn)) {
            // open the connection
            $this->openConnection();
        }

        if (__ENV__ == "DEV") {
            isset($this->debug[$type]) ? array_push($this->debug[$type], array('query' => $query, 'start' => round(microtime(true) * 1000))) : $this->debug[$type] = array(array('query' => $query, 'start' => round(microtime(true) * 1000)));
        }

        $stmt = $this->conn->prepare($query);
        $result = array();
        if ($type == "select" ||$type == "proc stock") {
            if ($stmt->execute($arguments)) {
                while ($row = $stmt->fetch()) {
                    $row_length = sizeof($row);
                    for ($i = 0; $i < (int) ($row_length / 2); $i++) {
                        unset($row[$i]);
                    }
                    array_push($result, $row);
                }
            }
        }elseif($type == "update" || $type == "insert"){
            $stmt->execute();
            $result = $stmt->rowCount();
        }else{
            throw new \Exception("Oups, query type error: \"$type\" is not available.");
        }
        
        if (__ENV__ == "DEV") {
            $this->debug[$type][sizeof($this->debug[$type]) - 1]['end'] = round(microtime(true) * 1000);
        }

        // close the connection
        if ($close) {
            $this->closeConnection();
        }
        return $result;
    }

    public function createQueryBuilder($type = "select") {
        if ($type == "select") {
            return new SelectBuilder($this);
        }
        if ($type == "insert") {
            return new InsertBuilder($this);
        }
        if ($type == "update") {
            return new UpdateBuilder($this);
        }
    }

    /**
     * Check if database configurations is set
     * 
     * @param array $config
     * @return boolean
     * @throws \Exception
     */
    private function isDatabaseConfigValid($config) {
        foreach (self::$config_require as $value) {
            if (!isset($config[$value])) {
                throw new \Exception("Oups, $value missing into database configuration.");
            }
        }
        return true;
    }

    public function getConn() {
        return $this->conn;
    }

    public function getName() {
        return $this->name;
    }

}
