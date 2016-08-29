<?php

namespace Database\Builder;

use Database\ConnectDB;

class InsertBuilder {

    protected $tablename = "";
    protected $columnsArray = array();
    protected $values = array();
    protected $conn = null;

    public function __construct(ConnectDB $conn) {
        $this->conn = $conn;
    }

    /**
     * 
     * @return \Database\Builder
     */
    public function createQuery() {
        $this->query = $this->getQuery();
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function insert() {
        return !is_null($this->query) ? $this->conn->execute($this->query, array(), "insert") : null;
    }

    /**
     * 
     * @param string $query
     * @return string
     */
    public function getQuery() {
        $query = "";
        if ($this->tablename != "" && !is_null($this->tablename)) {
            $query .= "INSERT INTO " . $this->tablename;
            $query .= " (" . implode(",", $this->columnsArray) . ")";
            $query .= " VALUES";
            foreach ($this->values as $index => $value) {
                $query .= $index != 0 ? "," : "" . "(" . implode(",", $value) . ")";
            }
        }
        return $query;
    }

    /**
     * 
     * @param type $tablename
     * @return \Database\Builder\InsertBuilder
     */
    public function insertInto($tablename) {
        $this->tablename = $tablename;
        return $this;
    }

    /**
     * 
     * @param array $columns
     * @return \Database\Builder\InsertBuilder
     */
    public function columns(array $columns) {
        $this->columnsArray = $columns;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return \Database\Builder\InsertBuilder
     */
    public function addValue(array $value) {
        $this->values[] = $value;
        return $this;
    }

    /**
     * 
     * @param type $values
     * @return \Database\Builder\InsertBuilder
     */
    public function setValues(array $values) {
        $this->values = $values;
        return $this;
    }

}
