<?php

/**
 * Console
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 */

namespace Primer\Console;

use Primer\Console\Command\BaseCommand;
use Primer\Console\Arguments\Arguments;
use Primer\Console\Exception\ExceptionHandler;
use Primer\Console\Output\Writer;

class Console extends ConsoleObject {

    private $command;

    /**
     * Application instance used for content injection, exception handling, etc.
     *
     * @var
     */
    private $_app;

    /**
     * Name of the application.
     *
     * @var string
     */
    private $_applicationName = '';

    /**
     * Application version
     *
     * @var string
     */
    private $_version = '';

    /**
     * Application logo. This will be outputted on its own line before the
     * application information on the help screen.
     *
     * @var string
     */
    private $_logo = '';

    /**
     * Object that holds all possible arguments as well as parsed arguments
     *
     * @var Arguments
     */
    private $_arguments;

    /**
     * Data structure to hold all avaiable command instances
     *
     * @var array
     */
    private $_commands = array();

    /**
     * All available flags that can be used application-wide
     *
     * @var array
     */
    private $_flags = array(
        'help' => array(
            'description' => 'Display this help message',
            'alias' => 'h',
        ),
        'quiet' => array(
            'description' => 'Suppress output',
            'alias' => 'q',
        ),
        'verbose' => array(
            'description' => 'Set level of output verbosity',
            'alias' => 'v',
            'stackable' => true,
        ),
        'version' => array(
            'description' => "Display this application's version",
            'alias' => 'V',
        ),
        'profile' => array(
            'description' => 'Display timing and memory usage information',
        ),
        'database-usage' => array(
            'description' => 'Display informations about database utilisation',
        ),
    );

    /**
     * Application start time (set at construction) for profiling purposes
     *
     * @var mixed
     */
    private $_start_time;

    public function __construct($applicationName = '', $version = '1.0', $argv = null) {
        $this->_start_time = microtime(true);

        if (!$argv) {
            $argv = $_SERVER['argv'];
        }

        ExceptionHandler::setConsole($this);
        set_exception_handler('Primer\\Console\\Exception\\ExceptionHandler::handleException');

        $this->_applicationName = $applicationName;
        $this->_version = $version;
        $this->_userPassedArgv = $argv;

        $this->_arguments = new Arguments(array(
            'flags' => $this->_flags,
        ));

        parent::__construct();
    }

    public function addCommand(BaseCommand $instance) {
        $instance->configure();
        $instance->setup($this->_arguments);
        $this->_commands[$instance->getName()] = $instance;
        $this->_arguments->addCommand($instance->getName(), $instance->getDescription());
    }

    public function run() {
        $this->_arguments->parse();

        /*
         * Set application verbosity based on flags
         */
        self::$_stdout->setApplicationVerbosity(Writer::VERBOSITY_NORMAL);
        if ($this->_arguments->flags['q']->getValue()) {
            self::$_stdout->setApplicationVerbosity(Writer::VERBOSITY_QUIET);
        } else if ($this->_arguments->flags['v']->getValue()) {
            self::$_stdout->setApplicationVerbosity(Writer::VERBOSITY_VERBOSE);
        }

        $parsedCommands = $this->_arguments->getParsedCommands();
        if (count($parsedCommands) === 1) {
            $this->_callApplication($parsedCommands[0]);
        } else if ($this->_arguments->flags['V']->getExists()) {
            $this->_displayVersionInformation();
        } else {
            $this->_buildHelpScreen();
        }

        $this->shutdown();
    }

    public function setApp($app) {
        $this->_app = $app;
    }

    public function setLogo($logo) {
        $this->_logo = $logo;
    }

    private function _callApplication($applicationName) {
        if (!array_key_exists($applicationName, $this->_commands)) {
            $this->_buildHelpScreen();
        } else {
            $this->command = $this->_commands[$applicationName];

            if ($this->_arguments->flags['h']->getExists()) {
                $this->command->renderHelpScreen();
            } else if ($this->_arguments->flags['V']->getExists()) {
                $this->_displayVersionInformation();
            } else {
                $this->command->prepare();
                $this->command->run();
            }
        }
    }

    private function _buildHelpScreen() {
        $helpScreen = new HelpScreen($this->_arguments);

        if ($this->_logo) {
            $this->out($this->_logo);
        }

        $this->_displayVersionInformation();
        $this->line();
        $this->out($helpScreen->render());
    }

    private function _displayVersionInformation() {
        $app = "<info>{$this->_applicationName}</info>";

        if ($this->_version) {
            $app .= " version <warning>{$this->_version}</warning>";
        }

        $this->out($app);
    }

    private function shutdown() {
        if ($this->_arguments->getFlag('profile')) {
            $memoryUsageMb = round(memory_get_usage(true) / 1048576, 2);
            $memoryPeakMb = round(memory_get_peak_usage(true) / 1048576, 2);

            $this->out("");
            $this->out("<info>Memory usage: {$memoryUsageMb}MB (peak: {$memoryPeakMb}MB), time: " . number_format(((microtime(true) - $this->_start_time)), 4) . "s</info>", $this->_arguments->getFlag('database-usage') ? 1 : 2);
        }
        if ($this->_arguments->getFlag('database-usage')) {
            $this->out("");
            $this->out("<info>Database usage: </info>");
            foreach ($this->command->get('connection')->debug as $key => $value) {
                $str = "<info>    ";
                $str .= str_pad(str_pad($key,15) . sizeof($value), 20)." time = ";
                $time = 0;
                foreach($value as $val){
                    $time += $val['end'] - $val['start'];
                }
                $str .= number_format($time/1000, 3)."s";
                $str .= "</info>";
                $this->out($str);
            }
        }
    }

}
