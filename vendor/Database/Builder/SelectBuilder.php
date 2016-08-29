<?php

namespace Database\Builder;

use Database\ConnectDB;

class SelectBuilder Extends BaseSelect {

    protected $selectArray = array();
    protected $fromArray = array();
    protected $limitValue = null;
    protected $conn;

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
    public function getResult() {
        return !is_null($this->query) ? $this->conn->execute($this->query, array(), "select") : null;
    }

    /**
     * 
     * @param string $query
     * @return string
     */
    public function getQuery() {
        $query = "";
        if (!empty($this->selectArray)) {
            $this->setSelect($query);
            $this->setFrom($query);
            $this->setWhere($query);
            $this->setParameters($query);
            $this->setOrderBy($query);
            $this->setLimit($query);
        }
        return $query;
    }

    /**
     * 
     * @param type $select
     * @param type $alias
     * @return \Database\Builder
     */
    public function select($select, $alias = null) {
        $this->selectArray[] = is_null($alias) ? $select : $select . " AS " . $alias;
        return $this;
    }

    /**
     * 
     * @param type $from
     * @param type $alias
     * @return \Database\Builder
     */
    public function from($from, $alias) {
        $this->fromArray[] = is_null($alias) ? $from : $from . " AS " . $alias;
        return $this;
    }

    /**
     * 
     * @param type $limit
     * @return \Database\Builder
     */
    public function limit($limit) {
        $this->limitValue = $limit;
        return $this;
    }

    protected function setSelect(&$query) {
        if (empty($this->selectArray)) {
            throw new \Exception("Oups, select section is missing for the query.");
        }
        $query .= "SELECT " . implode(',', $this->selectArray);
    }

    protected function setLimit(&$query) {
        if (!is_null($this->limitValue)) {
            $query .= " LIMIT " . $this->limitValue;
        }
    }

    protected function setFrom(&$query) {
        if (empty($this->fromArray)) {
            throw new \Exception("Oups, from section is missing for the query.");
        }
        $query .= " FROM " . implode(',', $this->fromArray);
    }

}
