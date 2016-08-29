<?php
namespace Command;

use Primer\Console\Command\BaseCommand;
use Primer\Console\Input\DefinedInput;

class TestCommand extends BaseCommand {

    public function configure() {
        $this->setName('test');
        $this->setDescription('this is a test command, it can be remove.');
        $this->addArgument('arg_test', DefinedInput::VALUE_REQUIRED, 'this is a test argument');
        $this->addOption('opt_test', 'o', DefinedInput::VALUE_OPTIONAL, 'this is a test option', 'this is a test option');
        
    }
    public function run() {
        // do what you want...
    }
}
