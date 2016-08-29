<?php
namespace Database\Builder;

use Database\ConnectDB;

class UpdateBuilder extends BaseSelect {
    
    protected $tablename = "";
    protected $values = array();
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
    public function update() {
        return !is_null($this->query) ? $this->conn->execute($this->query, array(), "update") : null;
    }

    /**
     * 
     * @param string $query
     * @return string
     */
    public function getQuery() {
        $query = "";
        if($this->tablename != "" && !is_null($this->tablename)){
            $query .= "UPDATE ".$this->tablename;
            $query .= " SET ".implode(" AND ", $this->values);
            $this->setWhere($query);
            $this->setParameters($query);
            $this->setOrderBy($query);
        }
        return $query;
    }
    
    /**
     * 
     * @param type $tablename
     * @return \Database\Builder\UpdateBuilder
     */
    public function updateTable($tablename) {
        $this->tablename =$tablename;
        return $this;
    }
    
    /**
     * 
     * @param type $value
     * @return \Database\Builder\UpdateBuilder
     */
    public function addValue($value){
        $this->values[] = $value;
        return $this;
    }
}
