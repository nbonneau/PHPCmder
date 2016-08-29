<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/3/15
 * Time: 10:22 AM
 */

namespace Primer\Console\Input;

use Primer\Console\Exception\DefinedInputException;

class InputFlag extends DefinedInput
{
    protected $_stackable = false;

    public function __construct($name, $alias = '', $mode = null, $description = '', $stackable = false)
    {
        if (!$name) {
            throw new DefinedInputException();
        }

        if (strlen($alias) > strlen($name)) {
            $tmp = $name;
            $name = $alias;
            $alias = $tmp;
        }

        $this->_name = $name;
        $this->_alias = $alias;

        if (!$mode) {
            $mode = DefinedInput::VALUE_OPTIONAL;
        }

        if ($stackable) {
            $description = trim($description . " (stackable)");
        }

        $this->_mode = $mode;
        $this->_description = $description;
        $this->_stackable = $stackable;
        $this->_default = false;
    }

    public function isStackable()
    {
        return $this->_stackable;
    }

    public function increaseValue()
    {
        if ($this->_stackable === true) {
            if ($this->_value === null) {
                $this->_value = 1;
            }
            else {
                $this->_value++;
            }
        }
    }
}