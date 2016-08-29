<?php

namespace Logger;

use Logger\Log;

class Logger {
    
    const __LOG_DIR__ = "/../../app";

    protected $name;
    protected $prefix = "log_";
    protected $extension = "txt";
    protected $directory = "/logs";
    protected $handler = null;
    protected $replace = false;
    protected $new_log;

    function __construct($name, $replace = false, $directory = "/logs") {
        $this->name = $name;
        $this->directory = __DIR__ . Logger::__LOG_DIR__ . $directory;
        if (!$this->dirIsValid() && __ENV__ == "DEV") {
            mkdir($this->directory);
        }
        $this->replace = $replace;
        $this->new_log = new Log();
    }

    /**
     * Add log data, can be an array of string
     * 
     * @param string|array $data
     * @param string $key = null
     */
    public function addData($data, $key = null) {
        if (is_array($data)) {
            foreach ($data as $value) {
                $this->addData($value);
            }
        } else {
            $this->new_log->addData(is_null($key) ? sizeof($this->new_log->getData()) : $key, $data);
        }
    }

    /**
     * Remove the $key data
     * 
     * @param string $key
     */
    public function removeData($key) {
        if (!is_null($this->getDataByKey($key))) {
            unset($this->new_log->getData()[$key]);
        }
    }

    /**
     * Get the data by its key
     * 
     * @param string $key
     * @return string
     */
    public function getDataByKey($key) {
        return isset($this->new_log->getData()[$key]) ? $this->new_log->getData()[$key] : null;
    }

    /**
     * Log data and close the file handler
     */
    public function log($data = null, $prefix = null) {
        if (__ENV__ == "DEV") {
            $log = null;
            if (is_null($data)) {
                $log = $this->new_log;
            } else {
                $log = new Log();
                $date = new \DateTime();
                $log->setData(is_null($prefix) ? "[" . $date->format("D M d H:i:s Y") . "] ". $data : "[" . $date->format("D M d H:i:s Y") . "] ". "[" . $prefix . "] " . $data);
            }
            if (is_null($this->handler)) {
                $this->handler = $this->getFileHandler();
            }
            // write formatted string log into log file
            fwrite($this->handler, $log->getFormattedString($prefix));
            // close the file handler
            $this->closeHandler();
        }
    }

    /**
     * Close the handler
     * 
     * @return type
     */
    public function closeHandler() {
        if (fclose($this->handler)) {
            $this->handler = null;
        }
    }

    /**
     * Check if the directory exist
     * 
     * @return boolean
     */
    private function dirIsValid() {
        return is_dir($this->directory);
    }

    /**
     * Get the file handler, try to create the file if not exist
     * 
     * @return handler
     */
    private function getFileHandler() {
        $mode = $this->replace ? "w+" : "a+";
        return fopen($this->pathFilename(), $mode);
    }

    /**
     * Return the path filename
     * 
     * @return string
     */
    private function pathFilename() {
        return $this->directory . "/" . $this->formatFilename();
    }

    /**
     * Return the formatter filename like '$prefix.$name.".".$extension'
     * 
     * @return string
     */
    private function formatFilename() {
        return $this->prefix . $this->name . "." . $this->extension;
    }

}
