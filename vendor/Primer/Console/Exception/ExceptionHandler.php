<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/11/15
 * Time: 1:23 PM
 */

namespace Primer\Console\Exception;

use Exception;
use Primer\Console\Console;

class ExceptionHandler
{
    private static $_console;

    public static function setConsole(Console $console)
    {
        static::$_console = $console;
    }

    public static function handleException(Exception $exception)
    {
        try {
            $error = new ExceptionRenderer($exception);
            $error->render();
        } catch (Exception $e) {
            $message = sprintf(
                "[%s] %s\n%s", // Keeping same message format
                get_class($e),
                $e->getMessage(),
                $e->getTraceAsString()
            );
            trigger_error($message, E_USER_ERROR);
        }
    }
}