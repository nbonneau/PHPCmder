<?php

namespace Service;

use Logger\Logger;
use Database\ConnectDB;
use ConfigReader\ConfigReader;

class ServiceManager {

    protected $services_list = array();
    protected $_name;

    public function __construct($name) {
        $this->services_list = ConfigReader::getJsonServices();
        $this->_name = $name;
    }

    public function getServices_list() {
        return $this->services_list;
    }

    /**
     * Return the property value or the corresponding service.
     * Throws an exception if no property or service found
     * 
     * @param string $param
     * @return mixed
     * @throws \Exception
     */
    public function search($param, $class) {
        // check each properties
        if (!is_null($val = $this->searchProperty($param, $class))) {
            return $val;
        }
        // check each services
        if (!is_null($val = $this->searchService($param))) {
            return $val;
        }
        // no property or service found
        throw new \Exception("No property or service \"$param\" found.");
    }

    protected function searchProperty($param, $class) {
        foreach (get_object_vars($class) as $property_name => $property) {
            // if the property name equals to the param value, return the property value
            if ($property_name == $param) {
                return $property;
            }
        }
        return null;
    }

    protected function searchService($param) {
        // ---------------------------------------------------------------------
        // defaults services
        if (!is_null($service = $this->getDefaultService($param))) {
            return $service;
        }
        // ---------------------------------------------------------------------
        // custom services
        foreach ($this->services_list as $service_name => $service_data) {
            if ($service_name == $param) {
                return $this->getServiceInstance($service_name, $service_data);
            }
        }
        return null;
    }

    private function getDefaultService($param) {
        if ($param == "connection") {
            if(!isset($this->services_list[$param])){
                $this->services_list[$param] = array();
            }
            // check if the service is loaded
            if (isset($this->services_list[$param]['instance'])) {
                // return the loaded service
                return $this->services_list[$param]['instance'];
            }
            // get command config
            $command_config = ConfigReader::getJsonConfig($this->_name);
            // check if database command config is set
            if (isset($command_config['database'])) {
                // check if each require database parameters is set
                foreach (array('host', 'user', 'pwd', 'name') as $value) {
                    if (!isset($command_config['database'][$value])) {
                        throw new \Exception("Oups, $value missing into command database configuration.");
                    }
                }
                $pwd = $command_config['database']['pwd'] == "" ? null : $command_config['database']['pwd'];
                // create new ConnectDB instance with database command config
                $instance = new ConnectDB($command_config['database']['host'], $command_config['database']['user'], $pwd, $command_config['database']['name']);
            } else {
                // create new ConnectDB instance with general database config
                $instance = new ConnectDB();
            }
            // put instance into loaded services instances
            $this->services_list[$param]['instance'] = $instance;
            // return the instance
            return $instance;
        }
        if ($param == "logger") {
            if (isset($this->services_list[$param]['instance'])) {
                return $this->services_list[$param]['instance'];
            }
            $instance = new Logger($this->_name, true, "/debug");
            $this->services_list[$param]['instance'] = $instance;
            return $instance;
        }
        return null;
    }

    private function getServiceInstance($service_name, $service_data) {
        if (!file_exists(__DIR__ . "\\..\\..\\" . $service_data['class'] . ".php")) {
            throw new \Exception("File \"" . $service_data['class'] . ".php\" not found.");
        }
        $class_name = str_replace("/", "\\", $service_data['class']);
        if (!class_exists($class_name)) {
            throw new \Exception("Class \"" . $class_name . "\" not found, the file was found but the class was not in it.");
        }
        if (isset($this->services_list[$service_name]['instance'])) {
            return $this->services_list[$service_name]['instance'];
        }
        $args = isset($service_data['arguments']) ? $service_data['arguments'] : array();
        if (!empty($args)) {
            $re_args = $this->getServiceConstructParameters($class_name, $args);
            $refClass = new \ReflectionClass($class_name);
            $instance = $refClass->newInstanceArgs((array) $re_args);
        } else {
            $instance = new $class_name();
        }
        $this->services_list[$service_name]['instance'] = $instance;
        return $instance;
    }

    private function getServiceConstructParameters($class_name, $args) {
        if (method_exists($class_name, '__construct') === false) {
            throw new \Exception("Constructor for the class <strong>$class_name</strong> does not exist, you should not pass arguments to the constructor of this class.");
        }
        $refMethod = new \ReflectionMethod($class_name, '__construct');
        $re_args = array();
        foreach ($refMethod->getParameters() as $key => $param) {
            $this->getServiceParam($args[$key], $param, $key, $re_args);
        }
        return $re_args;
    }

    private function getServiceParam($param_name, $param, $key, array &$re_args = array()) {
        if (substr($param_name, 0, 1) == "@") {
            $service_arg = $this->searchService(substr($param_name, 1));
            if (is_null($service_arg)) {
                throw new \Exception("Service with name \"$param_name\" not found.");
            }
            $param_name = $service_arg;
        }
        if ($param->isPassedByReference()) {
            $re_args[$key] = &$param_name;
        } else {
            $re_args[$key] = $param_name;
        }
        return $re_args;
    }

}
