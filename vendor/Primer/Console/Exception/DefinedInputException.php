<?php
/**
 * DefinedInputException
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 */

namespace Primer\Console\Exception;

use Exception;

class DefinedInputException extends Exception
{
    public function __construct($message = '', $code = 500)
    {
        if (!$message) {
            $message = "Invalid input";
        }

        parent::__construct($message, $code);
    }
}