<?php
/**
 * DefinedInput
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 */

namespace Primer\Console\Input;

abstract class DefinedInput
{
    const VALUE_REQUIRED = 2;
    const VALUE_OPTIONAL = 3;

    protected $_name;
    protected $_alias;
    protected $_mode;
    protected $_description = '';
    protected $_default = false;
    protected $_value = null;
    protected $_exists = false;

    public function getName()
    {
        return $this->_name;
    }

    public function getShortName()
    {
        if (strlen($this->_name) === 1) {
            return $this->_name;
        }

        if (strlen($this->_alias) === 1) {
            return $this->_alias;
        }

        return null;
    }

    public function getLongName()
    {
        if (strlen($this->_name) > 1) {
            return $this->_name;
        }

        if (strlen($this->_alias) > 1) {
            return $this->_alias;
        }

        return null;
    }

    public function getNames()
    {
        $retval = array($this->_name);

        if ($this->_alias) {
            $retval[] = $this->_alias;
        }

        return $retval;
    }

    public function getFormattedName($name = null)
    {
        if (!$name) {
            return $name;
        }

        switch (strlen($name)) {
            case 1:
                $name = "-$name";
                break;
            default:
                $name = "--$name";
                break;
        }

        return $name;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getDefault()
    {
        return $this->_default;
    }

    public function getSettings()
    {
        $settings = array();
        foreach ($this as $k => $v) {
            $k = ltrim($k, '_');
            $settings[$k] = $v;
        }

        return $settings;
    }

    public function getValue()
    {
        if ($this->_exists) {
            return $this->_value;
        }

        return $this->_default;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getMode()
    {
        return $this->_mode;
    }

    public function getExists()
    {
        return $this->_exists;
    }

    public function setExists($exists)
    {
        $this->_exists = $exists;
    }
}