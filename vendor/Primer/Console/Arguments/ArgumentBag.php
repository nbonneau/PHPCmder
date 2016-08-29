<?php
/**
 * @author Alex Phillips
 * @date   6/28/14
 * @time   2:22 PM
 */

namespace Primer\Console\Arguments;

use ArrayAccess;
use IteratorAggregate;
use Primer\Console\Exception\DefinedInputException;
use Primer\Console\Input\DefinedInput;
use Serializable;
use JsonSerializable;
use Countable;
use ArrayIterator;

/**
 * Class ParameterContainer
 */
class ArgumentBag implements ArrayAccess, IteratorAggregate, Serializable, JsonSerializable, Countable
{
    /**
     * Data structure holding the objects
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * The class type of the containing values
     *
     * @var null
     */
    protected $_type = null;

    /**
     * Set the class's parameters to an passed in array
     *
     * @param array $parameters
     */
    public function __construct($parameters = array())
    {
        $this->_arguments = $parameters;
    }

    /**
     * @param bool $full Return the full namespace classname
     *
     * @return null
     */
    public function getType($full = false)
    {
        if ($full) {
            return $this->_type;
        }

        $retval = explode('\\', $this->_type);

        return array_pop($retval);
    }

    /**
     * Returns true if the class's parameters contains a value
     * for a given key. The key can be a '.' delimited array path.
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        if ($this->get($key) !== null) {
            return true;
        }

        return false;
    }

    /**
     * Return a value from the class's parameters given a key.
     * The key can be a '.' delimited array path.
     *
     * @param $key
     *
     * @return array|null
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->_arguments)) {
            return $this->_arguments[$key];
        }

        foreach ($this->_arguments as $name => $argument) {
            if (in_array($key, $argument->getNames())) {
                return $argument;
            }
        }

        return $default;
    }

    public function getAll()
    {
        return $this->_arguments;
    }

    public function replace($params)
    {
        $this->_arguments = $params;
    }

    public function offsetExists($key)
    {
        return $this->get($key, false);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Set a value in the class's parameters given a key.
     * The key can be a '.' delimited array path.
     *
     * @param              $key
     * @param DefinedInput $value
     *
     * @throws DefinedInputException
     */
    public function set($key, DefinedInput $value)
    {
        if (!$this->_type) {
            $this->_type = get_class($value);
        }
        else if (get_class($value) !== $this->_type) {
            throw new DefinedInputException;
        }

        unset($this[$key]);

        if ($key instanceof DefinedInput) {
            $this->_arguments[$key->getName()] = $key;
        }
        else {
            $this->_arguments[$key] = $value;
        }
    }

    public function offsetUnset($key)
    {
        foreach ($this->_arguments as $name => $argument) {
            if (in_array($key, $argument->getNames())) {
                unset($this->_arguments[$name]);
                return;
            }
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_arguments);
    }

    public function clearAll()
    {
        $this->_arguments = array();
    }

    public function serialize()
    {
        return serialize($this->_arguments);
    }

    public function unserialize($data)
    {
        $this->_arguments = unserialize($data);
    }

    public function jsonSerialize()
    {
        return $this->_arguments;
    }

    public function count()
    {
        return count($this->_arguments);
    }
}