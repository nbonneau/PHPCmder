<?php

namespace Logger;

class Log {

    /**
     * The log string separator
     */
    const LOG_SEPARATOR = ";";

    /**
     * the date log format
     */
    const LOG_DATE_FORMAT = "Y-m-d H:i:s";

    /**
     * the log date start, initialize when the log object is created
     * @var string
     */
    protected $date_start;

    /**
     * the log date end, set when the log string is generate to write
     * @var string
     */
    protected $date_end;

    /**
     * the log data to write
     * @var array
     */
    protected $data = array();

    public function __construct() {
        $this->date_start = new \DateTime();
        $this->date_end = null;
    }

    /**
     * Add a string data to the data log array
     * 
     * @param mixed $key
     * @param string $data
     */
    public function addData($key, $data) {
        $this->data[$key] = $data;
    }

    /**
     * Get the formatted string log
     * 
     * @return string
     */
    public function getFormattedString($prefix = null) {
        $this->date_end = new \DateTime();
        if (is_null($prefix)) {
            $str = "";
        }else{
            $str = $this->date_start->format(Log::LOG_DATE_FORMAT) . Log::LOG_SEPARATOR . $this->date_end->format(Log::LOG_DATE_FORMAT);
        }
        if (is_array($this->data)) {
            foreach ($this->data as $value) {
                $str .= Log::LOG_SEPARATOR . $value;
            }
        } else {
            $str .= (!is_null($prefix) ? Log::LOG_SEPARATOR : "") . $this->data;
        }
        return $str . "\n";
    }

    function getDate_start() {
        return $this->date_start;
    }

    function getDate_end() {
        return $this->date_end;
    }

    function getData() {
        return $this->data;
    }

    function setDate_start($date_start) {
        $this->date_start = $date_start;
    }

    function setDate_end($date_end) {
        $this->date_end = $date_end;
    }

    function setData($data) {
        $this->data = $data;
    }

}
