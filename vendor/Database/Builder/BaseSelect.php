<?php

namespace Database\Builder;

class BaseSelect {

    protected $sort = null;
    protected $order = null;
    protected $whereArray = array();
    protected $params = array();
    protected $query = null;

    /**
     * 
     * @param type $where
     * @return \Database\Builder
     */
    public function addWhere($where) {
        $this->whereArray[] = $where;
        return $this;
    }

    /**
     * 
     * @param type $sort
     * @param type $order
     * @return \Database\Builder
     */
    public function orderBy($sort, $order = "asc") {
        $this->sort = $sort;
        $this->order = $order;
        return $this;
    }

    /**
     * 
     * @param type $param_name
     * @param type $param_value
     * @return \Database\Builder
     */
    public function addParameter($param_name, $param_value) {
        $this->params[$param_name] = $param_value;
        return $this;
    }

    /**
     * 
     * @param array $params
     * @return \Database\Builder
     */
    public function addParameters(array $params) {
        foreach ($params as $key => $value) {
            $this->addParameter($key, $value);
        }
        return $this;
    }

    protected function setOrderBy(&$query) {
        if (!is_null($this->sort) && !is_null($this->order)) {
            $query .= " ORDER BY " . $this->sort . " " . strtoupper($this->order);
        }
    }

    protected function setWhere(&$query) {
        $query .= " WHERE ".implode(" AND ", $this->whereArray);
    }

    protected function setParameters(&$query) {
        foreach ($this->params as $key => $value) {
            $param = "{" . $key . "}";
            if (!preg_match($param, $query)) {
                throw new \Exception("Too many parameters, \"$key\" is not define in the query.");
            }
            $query = str_replace($param, $value, $query);
        }
    }

}
