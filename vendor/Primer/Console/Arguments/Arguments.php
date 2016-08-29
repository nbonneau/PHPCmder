<?php

/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 12/22/14
 * Time: 2:27 PM
 */
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Primer\Console\Arguments;

use ArrayAccess;
use Primer\Console\HelpScreen;
use Primer\Console\Input\DefinedInput;
use Primer\Console\Input\InputArgument;
use Primer\Console\Input\InputCommand;
use Primer\Console\Input\InputFlag;
use Primer\Console\Input\InputOption;

/**
 * Parses command line arguments.
 */
class Arguments implements ArrayAccess {

    public $flags = array();
    public $options = array();
    public $commands = array();
    public $arguments = array();
    protected $_strict = false;
    protected $_input = array();
    protected $_invalid = array();
    protected $_parsed;
    protected $_parsedCommands;
    protected $_lexer;

    /**
     * Initializes the argument parser. If you wish to change the default behaviour
     * you may pass an array of options as the first argument. Valid options are
     * `'help'` and `'strict'`, each a boolean.
     *
     * `'help'` is `true` by default, `'strict'` is false by default.
     *
     * @param  array $options An array of options for this parser.
     */
    public function __construct($options = array()) {
        $options += array(
            'strict' => false,
            'input' => array_slice($_SERVER['argv'], 1)
        );

        $this->_input = $options['input'];
        $this->setStrict($options['strict']);

        $this->options = new ArgumentBag();
        $this->flags = new ArgumentBag();
        $this->commands = new ArgumentBag();
        $this->arguments = new ArgumentBag();

        if (isset($options['flags'])) {
            $this->addFlags($options['flags']);
        }
        if (isset($options['options'])) {
            $this->addOptions($options['options']);
        }
    }

    /**
     * Get the list of arguments found by the defined definitions.
     *
     * @return array
     */
    public function getParsed() {
        if (!isset($this->_parsed)) {
            $this->parse();
        }

        return $this->_parsed;
    }

    /**
     * Encodes the parsed arguments as JSON.
     *
     * @return string
     */
    public function asJSON() {
        return json_encode($this->_parsed);
    }

    /**
     * Adds a flag (boolean) to the argument list
     *
     * @param        $flag String representation of the flag
     * @param string $alias Optional array of alternate ways to specify the flag
     * @param null   $mode The flag's mode
     * @param string $description Text description used in the help screen
     * @param bool   $stackable Boolean if you can increase the value of the flag by repeating it
     *
     * @return $this
     */
    public function addFlag($flag, $alias = '', $mode = null, $description = '', $stackable = false) {
        if (!($flag instanceof InputFlag)) {
            if (isset($this->flags[$flag])) {
                $this->_warn('flag already exists: ' . $flag);

                return $this;
            }

            $flag = new InputFlag($flag, $alias, $mode, $description, $stackable);
        }

        $this->flags[$flag->getName()] = $flag;

        return $this;
    }

    /**
     * Add multiple flags at once. The input array should be keyed with the
     * primary flag character, and the values should be the settings array
     * used by {addFlag}.
     *
     * @param array $flags An array of flags to add
     *
     * @return $this
     */
    public function addFlags($flags) {
        foreach ($flags as $flag => $settings) {
            if (is_numeric($flag)) {
                $this->_warn('No flag character given');
                continue;
            }

            if ($settings instanceof DefinedInput) {
                $this->addFlag($settings);
            } else {
                $settings += array(
                    'alias' => '',
                    'mode' => null,
                    'description' => '',
                    'stackable' => false,
                );
                $this->addFlag($flag, $settings['alias'], $settings['mode'], $settings['description'], $settings['stackable']);
            }
        }

        return $this;
    }

    /**
     * @param        $option String representation of the option
     * @param string $alias Optional array of aliases you can call the option by
     * @param null   $mode The option's mode
     * @param string $description A text description of the option used in the help screen
     * @param null   $default The default value of the option if not provided
     *
     * @return $this
     */
    public function addOption($option, $alias = '', $mode = null, $description = '', $default = null) {
        if (!($option instanceof InputOption)) {
            if (isset($this->options[$option])) {
                $this->_warn('option already exists: ' . $option);
                return $this;
            }

            $option = new InputOption($option, $alias, $mode, $description, $default);
        }

        $this->options[$option->getName()] = $option;

        return $this;
    }

    /**
     * Add multiple options at once. The input array should be keyed with the
     * primary option string, and the values should be the settings array
     * used by {addOption}.
     *
     * @param array $options An array of options to add
     *
     * @return $this
     */
    public function addOptions($options) {
        foreach ($options as $option => $settings) {
            if (is_numeric($option)) {
                $this->_warn('No option string given');
                continue;
            }

            if ($settings instanceof DefinedInput) {
                $this->addOption($settings);
            } else {
                $settings += array(
                    'alias' => '',
                    'description' => '',
                    'default' => false,
                    'stackable' => false,
                );
                $this->addOption($option, $settings['alias'], $settings['default'], $settings['description']);
            }
        }

        return $this;
    }

    public function addCommand($command, $description = '') {
        if (isset($this->commands[$command])) {
            $this->_warn('command already exists: ' . $command);
            return $this;
        }

        $this->commands[$command] = new InputCommand($command, $description);

        return $this;
    }

    public function addArgument($name, $mode = DefinedInput::VALUE_REQUIRED, $description = '') {
        if (isset($this->arguments[$name])) {
            $this->_warn('argument already exists: ' . $name);
            return $this;
        }

        $this->arguments[$name] = new InputArgument($name, $mode, $description);

        return $this;
    }

    /**
     * Enable or disable strict mode. If strict mode is active any invalid
     * arguments found by the parser will throw `cli\arguments\InvalidArguments`.
     *
     * Even if strict is disabled, invalid arguments are logged and can be
     * retrieved with `cli\Arguments::getInvalidArguments()`.
     *
     * @param bool $strict True to enable, false to disable.
     *
     * @return $this
     */
    public function setStrict($strict) {
        $this->_strict = (bool) $strict;
        return $this;
    }

    /**
     * Get the list of invalid arguments the parser found.
     *
     * @return array
     */
    public function getInvalidArguments() {
        return $this->_invalid;
    }

    /**
     * Get a flag by primary matcher or any defined aliases.
     *
     * @param mixed $flag   Either a string representing the flag or an
     *                      cli\arguments\Argument object.
     *
     * @return array
     */
    public function getFlag($flag) {
        if ($val = $this->flags[$flag]) {
            return $val->getValue();
        }

        return null;
    }

    public function getFlags() {
        return $this->flags;
    }

    public function hasFlags() {
        return !empty($this->flags);
    }

    /**
     * Returns true if the given argument is defined as a flag.
     *
     * @param mixed $argument   Either a string representing the flag or an
     *                          cli\arguments\Argument object.
     *
     * @return bool
     */
    public function isFlag($argument) {
        return isset($this->flags[$argument]);
    }

    /**
     * Get an option by primary matcher or any defined aliases.
     *
     * @param mixed $option  Either a string representing the option or an
     *                       cli\arguments\Argument object.
     *
     * @return array
     */
    public function getOption($option) {
        if ($val = $this->options[$option]) {
            return $val->getValue();
        }

        return null;
    }

    public function getOptions() {
        return $this->options;
    }

    public function hasOptions() {
        return !empty($this->options);
    }

    /**
     * Returns true if the given argument is defined as an option.
     *
     * @param mixed $argument   Either a string representing the option or an
     *                          cli\arguments\Argument object.
     *
     * @return bool
     */
    public function isOption($argument) {
        return isset($this->options[$argument]);
    }

    public function getCommands() {
        return $this->commands;
    }

    public function hasCommands() {
        return !empty($this->commands);
    }

    /**
     * Returns true if the given argument is defined as an option.
     *
     * @param mixed $argument   Either a string representing the option or an
     *                          cli\arguments\Argument object.
     *
     * @return bool
     */
    public function isCommand($argument) {
        return isset($this->commands[$argument]);
    }

    public function getParsedCommands() {
        return $this->_parsedCommands;
    }

    public function getArgument($argument) {
        if ($val = $this->arguments[$argument]) {
            return $val->getValue();
        }

        return null;
    }

    public function getArguments() {
        return $this->arguments;
    }

    public function hasArguments() {
        return !empty($this->arguments);
    }

    /**
     * Parses the argument list with the given options. The returned argument list
     * will use either the first long name given or the first name in the list
     * if a long name is not given.
     *
     * @return array
     * @throws InvalidArguments
     */
    public function parse() {
        $this->_invalid = array();
        $this->_parsed = array();
        $this->_parsedCommands = array();
        $this->_lexer = new Lexer($this->_input);

        foreach ($this->_lexer as $argument) {
            if ($this->_parseFlag($argument)) {
                continue;
            }
            if ($this->_parseOption($argument)) {
                continue;
            }
            if ($this->_parseCommand($argument)) {
                continue;
            }
            if ($this->_parseArgument($argument)) {
                continue;
            }
            array_push($this->_invalid, $argument->raw);
        }

        if ($this->_strict && !empty($this->_invalid)) {
            throw new InvalidArguments($this->_invalid);
        }
    }

    private function _warn($message) {
        trigger_error('[' . __CLASS__ . '] ' . $message, E_USER_WARNING);
    }

    private function _parseFlag($argument) {
        $key = $argument->key;
        if (!$this->isFlag($key)) {
            return false;
        }

        $argument = $this->flags[$key];

        if ($argument->isStackable()) {
            if (!isset($this[$key])) {
                $this[$key] = 0;
            }

            $this[$key] += 1;
            $this->flags[$key]->increaseValue();
        } else {
            $this[$key] = $this->flags[$key]->getValue();
            $this->flags[$key]->setValue(true);
        }

        $this->flags[$key]->setExists(true);

        return true;
    }

    private function _parseOption($option) {
        $key = $option->key;
        if (!$this->isOption($key)) {
            return false;
        }

        // Peak ahead to make sure we get a value.
        if (!$this->_lexer->end() && !$this->_lexer->peek->isValue) {
            // Oops! Got no value, throw a warning and continue.
            $this->_warn('no value given for ' . $option->raw);
            $this[$key] = null;
            $this->options->setExists(true);
            return true;
        }

        // Store as array and join to string after looping for values
        $values = array();

        // Loop until we find a flag in peak-ahead
        foreach ($this->_lexer as $value) {
            array_push($values, $value->raw);

            if (!$this->_lexer->end() && !$this->_lexer->peek->isValue) {
                break;
            }
        }

        $value = join($values, ' ');
        if (!$value && $value !== false) {
            $value = true;
        }

        $this[$key] = $value;
        $this->options[$key]->setExists($value);
        $this->options[$key]->setValue($value);

        return true;
    }

    private function _parseCommand($argument) {
        $key = $argument->key;
        if (!$this->isCommand($key)) {
            return false;
        }

        $this->_parsedCommands[] = $argument->key;
        $this->commands[$key]->setExists(true);
        $this->commands[$key]->setValue(true);
        $this[$key] = $this->commands[$key]->getValue();

        return true;
    }

    private function _parseArgument(&$argument) {
        if (!$argument->is_set) {
            $argument->is_set = true;
            foreach ($this->arguments as $name => $arg) {
                if (!$arg->getExists()) {
                    $arg->setExists(true);
                    $arg->setValue($argument->key);
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * Returns true if a given argument was parsed.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     *
     * @return bool
     */
    public function offsetExists($offset) {
        if ($offset instanceOf Argument) {
            $offset = $offset->key;
        }

        return array_key_exists($offset, $this->_parsed);
    }

    /**
     * Get the parsed argument's value.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     *
     * @return mixed
     */
    public function offsetGet($offset) {
        if ($offset instanceOf Argument) {
            $offset = $offset->key;
        }

        if (isset($this->_parsed[$offset])) {
            return $this->_parsed[$offset];
        }
    }

    /**
     * Sets the value of a parsed argument.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     * @param mixed $value  The value to set
     */
    public function offsetSet($offset, $value) {
        if ($offset instanceOf Argument) {
            $offset = $offset->key;
        }

        $this->_parsed[$offset] = $value;
    }

    /**
     * Unset a parsed argument.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     */
    public function offsetUnset($offset) {
        if ($offset instanceOf Argument) {
            $offset = $offset->key;
        }

        unset($this->_parsed[$offset]);
    }

}
