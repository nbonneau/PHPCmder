<?php
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

namespace Primer\Console;

use Primer\Console\Arguments\Arguments;
use Primer\Console\Arguments\ArgumentBag;
use Primer\Console\Command\BaseCommand;
use Primer\Console\Input\DefinedInput;
use Primer\Console\Input\InputArgument;
use Primer\Console\Input\InputCommand;
use Primer\Console\Input\InputOption;

/**
 * Arguments help screen renderer
 */
class HelpScreen
{
    /**
     * Available flags to output
     *
     * @var array
     */
    protected $_flags = array();

    /**
     * Max length needed for 'flags' to be displayed before descriptions
     *
     * @var int
     */
    protected $_flagsMax = 0;

    /**
     * Available options to output
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Max length needed for 'options' to be displayed before descriptions
     *
     * @var int
     */
    protected $_optionsMax = 0;

    /**
     * Available commands to output
     *
     * @var array
     */
    protected $_commands = array();

    /**
     * Max length needed for 'commands' to be displayed before descriptions
     *
     * @var int
     */
    protected $_commandsMax = 0;

    /**
     * Available arguments to output
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * Max length needed for 'arguments' to be displayed before descriptions
     *
     * @var int
     */
    protected $_argumentsMax = 0;

    /**
     * Current command the help screen is being rendered for. If none, then
     * display the general help screen for the framework with available commands.
     *
     * @var
     */
    protected $_command;

    /**
     * Generate a new HelpScreen object with given Arguments
     *
     * @param Arguments $arguments
     */
    public function __construct(Arguments $arguments = null)
    {
        if ($arguments) {
            $this->set($arguments);
        }
    }

    /**
     * Output the class to render the help screen
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Pass in Arguments object to set available flags, options, and commands
     *
     * @param Arguments $arguments
     */
    public function set(Arguments $arguments)
    {
        $this->consume($arguments->getArguments());
        $this->consume($arguments->getFlags());
        $this->consume($arguments->getOptions());
        $this->consume($arguments->getCommands());
    }

    /**
     * Consume the given arguments in the ArgumentBag based on the type contained
     * inside the Bag
     *
     * @param ArgumentBag $args
     */
    public function consume(ArgumentBag $args)
    {
        if (!($val = $args->getType())) {
            return;
        }

        // @TODO: really shitty... should use an inflector to make plural,
        // but I want this package to remain separate from the rest of Primer...
        $varName = strtolower(str_replace('Input', '', $val)) . 's';
        $max = 0;
        $out = array();

        foreach ($args as $name => $arg) {
            $names = $this->getFormattedNames($arg);
            $max = max(strlen($names), $max);
            $out[$names] = $arg;
        }

        $this->{"_$varName"} = $out;
        $this->{"_{$varName}Max"} = $max;

    }

    /**
     * Return output for the help screen given the provided flags, options,
     * and commands available
     *
     * @return string
     */
    public function render(BaseCommand $command = null)
    {
        if ($command) {
            $this->_command = $command;
        }
        $help = array();

        array_push($help, $this->_renderUsage());

        array_push($help, $this->_renderScreen('Arguments', $this->_arguments, $this->_argumentsMax));
        array_push($help, $this->_renderScreen('Flags', $this->_flags, $this->_flagsMax));
        array_push($help, $this->_renderScreen('Options', $this->_options, $this->_optionsMax));

        if (!$this->_command) {
            array_push($help, $this->_renderScreen('Commands', $this->_commands, $this->_commandsMax));
        }

        $help = array_filter($help, function($var) {
            return ($var);
        });

        return join($help, "\n\n") . "\n";
    }

    /**
     * Generate a single section of the help screen given the arguments passed in
     *
     * @param $header
     * @param $options
     * @param $max
     *
     * @return string
     */
    private function _renderScreen($header, $options, $max)
    {
        $help = array();
        foreach ($options as $name => $arg) {
            $formatted = '  <info>' . str_pad($name, $max) . '</info>';
            $formatted = str_replace(array(
                '(',
                ')',
            ), array(
                '</info>(',
                ')<info>',
            ), $formatted);

            $dlen = 80 - 4 - $max;

            $description = explode('{{BREAK}}', wordwrap($arg->getDescription(), $dlen, "{{BREAK}}"));

            $formatted .= '  ' . array_shift($description);

            if ($val = $arg->getDefault()) {
                $formatted .= ' [default: ' . $val . ']';
            }

            // Pad was originally 3, since I'm indenting the lines by default, increased
            // to 4.
            $pad = str_repeat(' ', $max + 4);
            while ($desc = array_shift($description)) {
                $formatted .= "\n${pad}${desc}";
            }

            $formatted = "$formatted";

            array_push($help, $formatted);
        }

        if ($help) {
            array_unshift($help, "<warning>$header</warning>");
        }

        return join($help, "\n");
    }

    /**
     * Generate the usage string including flags, options, and arguments
     *
     * @return string
     */
    private function _renderUsage()
    {
        $usage = array();
        $commands = array_keys($this->_commands);

        if ($this->_command) {
            $usage[] = "{$commands[0]}";
            if (count($this->_command->getUserDefinedFlags()) > 0) {
                foreach ($this->_command->getUserDefinedFlags() as $name => $flag) {
                    $usage[] = $this->formatArgUsage($flag);
                }
            }

            if (count($this->_command->getUserDefinedOptions()) > 0) {
                foreach ($this->_command->getUserDefinedOptions() as $name => $option) {
                    $usage[] = $this->formatArgUsage($option);
                }
            }

            foreach ($this->_arguments as $name => $settings) {
                $usage[] = $this->formatArgUsage($settings);
            }
        }
        else {
            if ($this->_options) {
                $usage[] = "[options]";
            }
            $usage[] = "command";
            if ($this->_arguments) {
                $usage[] = "[arguments]";
            }
            if ($this->_flags) {
                $usage[] = "[flags]";
            }
        }

        return "<warning>Usage:</warning>\n  " . join(' ', $usage) . "";
    }

    /**
     * Return a string of an argument for use in the coomands usage description
     *
     * @param DefinedInput $arg
     *
     * @return string
     */
    private function formatArgUsage(DefinedInput $arg)
    {
        $names = array_filter(array_reverse($arg->getNames()), function($var) {
            return ($var);
        });
        foreach ($names as &$name) {
            $name = $arg->getFormattedName($name);
        }
        $val = implode('|', $names);

        if ($arg instanceof InputOption) {
            if ($arg->getLongName()) {
                $val .= "=";
            }
            else {
                $val .= " ";
            }

            $val .= '"..."';
        }

        switch ($arg->getMode()) {
            case DefinedInput::VALUE_OPTIONAL:
                $val = "[$val]";
                break;
            case DefinedInput::VALUE_REQUIRED:
                break;
        }

        return $val;
    }

    /**
     * Return a string of formatted names of an argument
     *
     * @param DefinedInput $arg
     *
     * @return string
     */
    private function getFormattedNames(DefinedInput $arg)
    {
        $retval = array();
        $first = true;

        foreach ($arg->getNames() as $name) {
            if (!($arg instanceof InputArgument) && !($arg instanceof InputCommand)) {
                $name = $arg->getFormattedName($name);

                if (!$first) {
                    $name = "($name)";
                }
                else {
                    $first = false;
                }
            }

            $retval[] = $name;
        }

        return implode(' ', $retval);
    }
}