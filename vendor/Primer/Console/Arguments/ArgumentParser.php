<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/5/15
 * Time: 12:07 PM
 */

class ArgumentParser implements ArrayAccess
{
    public $args;
    private $_rawArguments;
    private $_availableArguments;

    public function __construct($arguments = null)
    {
        if (!$arguments) {
            $arguments = array_slice($_SERVER['argv'], 1);
        }

        $this->_rawArguments = $arguments;
    }

    public function addAvailableArguments($arguments)
    {
        $this->_availableArguments = $arguments;
    }

    /**
     * PARSE ARGUMENTS
     *
     * This command line option parser supports any combination of three types of options
     * [single character options (`-a -b` or `-ab` or `-c -d=dog` or `-cd dog`),
     * long options (`--foo` or `--bar=baz` or `--bar baz`)
     * and arguments (`arg1 arg2`)] and returns a simple array.
     *
     * [pfisher ~]$ php test.php --foo --bar=baz --spam eggs
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     *   ["spam"]  => "eggs"
     *
     * [pfisher ~]$ php test.php -abc foo
     *   ["a"]     => true
     *   ["b"]     => true
     *   ["c"]     => "foo"
     *
     * [pfisher ~]$ php test.php arg1 arg2 arg3
     *   [0]       => "arg1"
     *   [1]       => "arg2"
     *   [2]       => "arg3"
     *
     * [pfisher ~]$ php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
     * > 'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
     *   [0]       => "plain-arg"
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     *   ["funny"] => "spam=eggs"
     *   ["also-funny"]=> "spam=eggs"
     *   [1]       => "plain arg 2"
     *   ["a"]     => true
     *   ["b"]     => true
     *   ["c"]     => true
     *   ["k"]     => "value"
     *   [2]       => "plain arg 3"
     *   ["s"]     => "overwrite"
     *
     * Not supported: `-cd=dog`.
     *
     * @author              Patrick Fisher <patrick@pwfisher.com>
     * @since               August 21, 2009
     * @see                 https://github.com/pwfisher/CommandLine.php
     * @see                 http://www.php.net/manual/en/features.commandline.php
     *                      #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
     *                      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
     * @usage               $args = CommandLine::parseArgs($_SERVER['argv']);
     */
    public function parseArgs()
    {
        $argv = $this->_rawArguments;

        array_shift($argv);
        $out = array();

        for ($i = 0, $j = count($argv); $i < $j; $i++) {
            $arg = $argv[$i];

            // --foo --bar=baz
            if (substr($arg, 0, 2) === '--') {
                $eqPos = strpos($arg, '=');

                // --foo
                if ($eqPos === false) {
                    $key = substr($arg, 2);

                    // --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    }
                    else {
                        $value = isset($out[$key]) ? $out[$key] : true;
                    }
                    $out[$key] = $value;
                }

                // --bar=baz
                else {
                    $key = substr($arg, 2, $eqPos - 2);
                    $value = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            }

            // -k=value -abc
            else {
                if (substr($arg, 0, 1) === '-') {
                    // -k=value
                    if (substr($arg, 2, 1) === '=') {
                        $key = substr($arg, 1, 1);
                        $value = substr($arg, 3);
                        $out[$key] = $value;
                    }
                    // -abc
                    else {
                        $chars = str_split(substr($arg, 1));
                        foreach ($chars as $char) {
                            $key = $char;
                            $value = isset($out[$key]) ? $out[$key] : true;
                            $out[$key] = $value;
                        }
                        // -a value1 -abc value2
                        if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                            $out[$key] = $argv[$i + 1];
                            $i++;
                        }
                    }
                }

                // plain-arg
                else {
                    $value = $arg;
                    $out[$value] = true;
                }
            }
        }

        $this->args = $out;

        return $out;
    }

    /**
     * GET BOOLEAN
     */
    public function getBoolean($key, $default = false)
    {
        if (!isset($this->args[$key])) {
            return $default;
        }
        $value = $this->args[$key];

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (bool)$value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            $map = array(
                'y'     => true,
                'n'     => false,
                'yes'   => true,
                'no'    => false,
                'true'  => true,
                'false' => false,
                '1'     => true,
                '0'     => false,
                'on'    => true,
                'off'   => false,
            );
            if (isset($map[$value])) {
                return $map[$value];
            }
        }

        return $default;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->args[] = $value;
        }
        else {
            $this->args[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->args[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->args[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->args[$offset]) ? $this->args[$offset] : null;
    }
}