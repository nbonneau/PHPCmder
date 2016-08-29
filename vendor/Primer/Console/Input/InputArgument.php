<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/6/15
 * Time: 6:42 PM
 */

namespace Primer\Console\Input;

class InputArgument extends DefinedInput
{
    public function __construct($name, $mode = null, $description = '', $default = null)
    {
        if (!$mode) {
            $mode = DefinedInput::VALUE_OPTIONAL;
        }

        $this->_name = $name;
        $this->_mode = $mode;
        $this->_description = $description;
        $this->_default = $default;
    }

    /**
     * Overridden function since we don't need '-' or '--' prepended
     * to arguments
     *
     * @param null $name
     *
     * @return mixed
     */
    public function getFormattedName($name = null)
    {
        return $this->_name;
    }
}