<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/3/15
 * Time: 11:35 AM
 */

namespace Primer\Console\Input;

use Primer\Console\Exception\DefinedInputException;

class InputOption extends DefinedInput
{
    public function __construct($name, $alias = '', $mode = null, $description = '', $default = null)
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

        $this->_mode = $mode;
        $this->_description = $description;
        $this->_default = $default;
    }
}