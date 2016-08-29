<?php

namespace ConfigReader;

class ConfigReader {

    // the config path
    const __CONFIG_PATH__ = "/../../app/config/";
    // the config file
    const __CONFIG__ = "config.json";
    // the services file
    const __SERVICES__ = "services.json";

    /**
     * Read Config/config.json 
     * 
     * @param string $config_name
     * @return array
     */
    public static function getJsonConfig($config_name = null) {
        $config = ConfigReader::getJsonFileContent(ConfigReader::__CONFIG__);
        if (!is_null($config_name)) {
            return isset($config[$config_name]) ? $config[$config_name] : array();
        }
        return $config;
    }

    public static function setJsonConfig($config) {
        file_put_contents(__DIR__ . ConfigReader::__CONFIG_PATH__ . ConfigReader::__CONFIG__, $config);
    }

    /**
     * Read Config/services.json 
     * 
     * @param string $service_name
     * @return array
     */
    public static function getJsonServices($service_name = null) {
        $services = ConfigReader::getJsonFileContent(ConfigReader::__SERVICES__);
        if (!is_null($service_name)) {
            return isset($services[$service_name]) ? $services[$service_name] : null;
        }
        return $services;
    }

    /**
     * 
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    public static function getJsonFileContent($filename) {
        $path = __DIR__ . ConfigReader::__CONFIG_PATH__;
        if (!file_exists($path . $filename)) {
            throw new \Exception("Json file \"$filename\" not found into \"$path\" directory.");
        }
        return json_decode(file_get_contents($path . $filename), TRUE);
    }

}
