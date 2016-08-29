<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/5/15
 * Time: 12:36 PM
 */

namespace Primer\Console\Input;

class InputCommand extends DefinedInput
{
    public function __construct($name, $description = '')
    {
        $this->_name = $name;
        $this->_description = $description;
    }
}