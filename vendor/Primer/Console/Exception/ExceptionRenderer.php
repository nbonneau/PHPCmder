<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/11/15
 * Time: 1:24 PM
 */

namespace Primer\Console\Exception;

use Exception;
use Primer\Console\ConsoleObject;
use Primer\Console\Output\StyleFormatter;
use Primer\Console\Output\Writer;

class ExceptionRenderer extends ConsoleObject
{
    private $_exception;

    public function __construct(Exception $exception)
    {
        parent::__construct();

        $this->_exception = $exception;

        /*
         * Setup style for outputting exception to stderr
         */
        $info = new StyleFormatter('white', 'red');
        $this->setFormatter('exception', $info);
    }

    public function render()
    {
        $class = explode("\\", get_class($this->_exception));
        $exception = $class[count($class) - 1];

        $message = explode('{{BREAK}}', wordwrap($this->_exception->getMessage(), 40, "{{BREAK}}"));
        array_unshift($message, "[$exception]");
        $elen = max(array_map('strlen', $message));

        $this->err(Writer::LF);
        $this->err("<exception>  " . $this->safeStrPad("", $elen) . "  </exception>");
        foreach ($message as $line) {
            $line = $this->safeStrPad($line, $elen);
            $this->err("<exception>  $line  </exception>");
        }
        $this->err("<exception>  " . $this->safeStrPad("", $elen) . "  </exception>");
        $this->err(Writer::LF);
    }
}