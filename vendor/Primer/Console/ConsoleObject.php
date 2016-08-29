<?php

/**
 * ConsoleObject
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 * @date   9/27/14
 * @time   11:57 AM
 */

namespace Primer\Console;

use Primer\Console\Input\Reader;
use Primer\Console\Output\Writer;
use Primer\Console\Output\StyleFormatter;

abstract class ConsoleObject {

    const DATE_OUTPUT_FORMAT = "Y-m-d H:i:s";

    /**
     * Object to handle writing output to std out
     *
     * @var Writer
     */
    protected static $_stdout;

    /**
     * Object to handle writing to std error
     *
     * @var
     */
    protected static $_stderr;

    /**
     * Object to handle standard input from user
     *
     * @var
     */
    protected static $_stdin;
    protected static $_init = false;

    public function __construct() {
        if (!self::$_init) {
            self::$_stdout = new Writer(Writer::STREAM_STDOUT);
            self::$_stderr = new Writer(Writer::STREAM_STDERR);
            self::$_stdin = new Reader(Reader::STREAM_READ);

            $this->setupStyles();
        }
    }

    protected function setupStyles() {
        $comment = new StyleFormatter('blue');
        $this->setFormatter('comment', $comment);

        $info = new StyleFormatter('green');
        $this->setFormatter('info', $info);

        $warning = new StyleFormatter('yellow');
        $this->setFormatter('warning', $warning);

        $error = new StyleFormatter('red');
        $this->setFormatter('error', $error);

        $exception = new StyleFormatter('white', 'red');
        self::$_stderr->setFormatter('exception', $exception);
    }

    public function setFormatter($xmlTag, StyleFormatter $displayFormat) {
        self::$_stdout->setFormatter($xmlTag, $displayFormat);
    }

    /**
     * Return the object meant to handle output to standard output
     *
     * @return \Primer\Console\Output\Writer
     */
    public function getStdout() {
        return self::$_stdout;
    }

    public function out($message, $numberOfNewLines = 1, $prefix = "", $datePrefixed = false, $verbosityLevel = Writer::VERBOSITY_NORMAL, $eol = Writer::LF) {
        self::$_stdout->setVerbosityForOutput($verbosityLevel);
        self::$_stdout->writeMessage(($datePrefixed ? $this->getDatePrefixOutput() : "") . $prefix . $message, $numberOfNewLines, $eol);
    }

    public function out_r($message, $numberOfNewLines = 0, $prefix = "", $datePrefixed = false, $verbosityLevel = Writer::VERBOSITY_NORMAL, $eol = Writer::LF) {
        self::$_stdout->setVerbosityForOutput($verbosityLevel);
        self::$_stdout->writeMessage(($datePrefixed ? $this->getDatePrefixOutput() : "") . $prefix . $message, $numberOfNewLines, $eol);
        echo "\r";
    }

    public function outPadded($message, $numberOfNewLines = 1, $prefix = "", $datePrefixed = false, $verbosityLevel = Writer::VERBOSITY_NORMAL, $eol = Writer::LF) {
        self::$_stdout->setVerbosityForOutput($verbosityLevel);
        self::$_stdout->writeMessage(($datePrefixed ? $this->getDatePrefixOutput() : "") . $prefix .
                str_pad($message, $this->columns()), $numberOfNewLines, $eol
        );
    }

    public function outPadded_r($message, $numberOfNewLines = 0, $prefix = "", $datePrefixed = false, $verbosityLevel = Writer::VERBOSITY_NORMAL, $eol = Writer::LF) {
        self::$_stdout->setVerbosityForOutput($verbosityLevel);
        self::$_stdout->writeMessage(($datePrefixed ? $this->getDatePrefixOutput() : "") . $prefix .
                str_pad($message, $this->columns()), $numberOfNewLines, $eol
        );
        echo "\r";
    }

    public function line($eol = Writer::LF) {
        self::$_stdout->writeMessage('', 1, $eol);
    }

    public function format($message) {
        $args = func_get_args();

        // No string replacement is needed
        if (count($args) == 1) {
            return $message;
        }

        // If the first argument is not an array just pass to sprintf
        if (!is_array($args[1])) {

            // Escape percent characters for sprintf
            $args[0] = preg_replace('/(%([^\w]|$))/', "%$1", $args[0]);

            $format = array_shift($args);

            return vsprintf($format, $args);
        }

        // Here we do named replacement so formatting strings are more understandable
        foreach ($args[1] as $key => $value) {
            $message = str_replace('{:' . $key . '}', $value, $message);
        }

        return $message;
    }

    public function err($message, $numberOfNewLines = 1, $verbosityLevel = Writer::VERBOSITY_NORMAL, $eol = Writer::LF) {
        self::$_stdout->setVerbosityForOutput($verbosityLevel);
        self::$_stderr->writeMessage(
                $message, $numberOfNewLines, $eol
        );
    }

    public function ask($message = '', $numberOfNewLines = 1) {
        if ($message) {
            $this->out($message, $numberOfNewLines);
        }

        return self::$_stdin->getReadedValue();
    }

    public function json($data) {
        $this->out(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Returns the number of columns the current shell has for display.
     *
     * @return int  The number of columns.
     * @todo Test on more systems.
     */
    public function columns() {
        static $columns;

        if (null === $columns) {
            if (!$this->isWindows()) {
                $columns = (int) exec('/usr/bin/env tput cols');
            }

            if (!$columns) {
                // default width of cmd window on Windows OS, maybe force using MODE CON COLS=XXX?
                $columns = 80;
            }
        }

        return $columns;
    }

    /**
     * Uses `stty` to hide input/output completely.
     *
     * @param boolean $hidden Will hide/show the next data. Defaults to true.
     */
    public function hide($hidden = true) {
        system('stty ' . ($hidden ? '-echo' : 'echo'));
    }

    /**
     * Is this shell in Windows?
     *
     * @return bool
     */
    protected function isWindows() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Checks whether the output of the current script is a TTY or a pipe / redirect
     *
     * Returns true if STDOUT output is being redirected to a pipe or a file; false is
     * output is being sent directly to the terminal.
     *
     * If an env variable SHELL_PIPE exists, returned result depends it's
     * value. Strings like 1, 0, yes, no, that validate to booleans are accepted.
     *
     * To enable ASCII formatting even when shell is piped, use the
     * ENV variable SHELL_PIPE=0
     *
     * @return bool
     */
    public function isPiped() {
        $shellPipe = getenv('SHELL_PIPE');

        if ($shellPipe !== false) {
            return filter_var($shellPipe, FILTER_VALIDATE_BOOLEAN);
        } else {
            return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
        }
    }

    /**
     * Attempts an encoding-safe way of getting string length. If mb_string extensions aren't
     * installed, falls back to basic strlen if no encoding is present
     *
     * @param string The string to check
     *
     * @return int Numeric value that represents the string's length
     */
    public static function safeStrLen($str) {
        if (function_exists('mb_strlen') && function_exists(
                        'mb_detect_encoding'
                )
        ) {
            $length = mb_strlen($str, mb_detect_encoding($str));
        } else {
            // iconv will return PHP notice if non-ascii characters are present in input string
            $str = iconv('ASCII', 'ASCII', $str);

            $length = strlen($str);
        }

        return $length;
    }

    /**
     * Attempts an encoding-safe way of getting a substring. If mb_string extensions aren't
     * installed, falls back to ascii substring if no encoding is present
     *
     * @param  string  $str    The input string
     * @param  int     $start  The starting position of the substring
     * @param  boolean $length Maximum length of the substring
     *
     * @return string           Substring of string specified by start and length parameters
     */
    public static function safeSubStr($str, $start, $length = false) {
        if (function_exists('mb_substr') && function_exists(
                        'mb_detect_encoding'
                )
        ) {
            $substr = mb_substr(
                    $str, $start, $length, mb_detect_encoding($str)
            );
        } else {
            // iconv will return PHP notice if non-ascii characters are present in input string
            $str = iconv('ASCII', 'ASCII', $str);

            $substr = substr($str, $start, $length);
        }

        return $substr;
    }

    /**
     * An encoding-safe way of padding string length for display
     *
     * @param string $string The string to pad
     * @param int    $length The length to pad it to
     *
     * @return string
     */
    public static function safeStrPad($string, $length) {
        // Hebrew vowel characters
        $cleaned_string = preg_replace('#[\x{591}-\x{5C7}]+#u', '', Colors::decolorize($string));
        if (function_exists('mb_strwidth') && function_exists('mb_detect_encoding')) {
            $real_length = mb_strwidth($cleaned_string, is_string(mb_detect_encoding($string)) ? mb_detect_encoding($string) : "UTF-8");
        } else {
            $real_length = self::safeStrLen($cleaned_string);
        }
        $diff = strlen($string) - $real_length;
        $length += $diff;

        return str_pad($string, $length);
    }

    /**
     * 
     * @return type
     */
    public function getDatePrefixOutput() {
        $date = new \DateTime();
        return "<warning>[" . $date->format(ConsoleObject::DATE_OUTPUT_FORMAT) . "]</warning> ";
    }

}
