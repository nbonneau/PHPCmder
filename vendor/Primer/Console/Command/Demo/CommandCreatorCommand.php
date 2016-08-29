<?php

namespace Primer\Console\Command\Demo;

use Primer\Console\Command\BaseCommand;
use Primer\Console\Output\Menu;
use ConfigReader\ConfigReader;
use Primer\Console\Input\InputArgument;

class CommandCreatorCommand extends BaseCommand {

    const __CMD_DIR__ = "Command";

    protected $new_command_name = "";
    protected $new_command_description = "";
    protected $new_command_arguments = array();
    protected $new_command_options = array();
    protected $new_command_flags = array();
    protected $new_command_filename = "";
    protected $namespace;

    public function configure() {
        $this->setName('create:command')->setDescription("Create a new command.");
        $this->addArgument("name", InputArgument::VALUE_OPTIONAL, "Define the command name");
        $this->addArgument("namespace", InputArgument::VALUE_OPTIONAL, "Define the command namespace");
    }

    public function run() {

        $this->namespace = is_null($this->args->getArgument('namespace')) ? CommandCreatorCommand::__CMD_DIR__ : $this->args->getArgument('namespace');

        $this->out("");
        $this->out("    <info>Welcome to the command creator!</info>", 2);
        $this->out("    <info>This command help you to create a new command into Command directory.</info>");
        $this->out("    <info>Define the command name and description, add arguments, options and flags if necesary.</info>", 2);

        $this->setCommandName();
        $this->out("");
        $this->setCommandDescription();
        $this->setCommandArguments();
        $this->setCommandOptions();
        //$this->setCommandFlags();
        $this->createFileClass();
        $this->checkUpdateConfigFile();

        $this->out("");
        $this->out("<info>------</info> Command created successfully! <info>------</info>", 2);
    }

    private function setCommandName() {

        $commands_list = ConfigReader::getJsonConfig("commands");

        $this->new_command_name = $this->args->getArgument("name");
        while (in_array((!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__) . "/" . ucfirst($this->new_command_name) . "Command", $commands_list) || $this->new_command_name == "") {
            $this->new_command_name = $this->ask("<warning>Please enter the command name: </warning>", 0);
            if (in_array((!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__) . "/" . ucfirst($this->new_command_name), $commands_list)) {
                $this->out("");
                $this->out("    <error>Oups, a command with name $this->new_command_name already exists or is not available.</error>");
                $this->out("    <error>Please choose an other one.</error>", 2);
            }
        }
    }

    private function setCommandDescription() {
        $this->new_command_description = $this->ask("<warning>Please enter the command description (can be empty): </warning>", 0);
        $this->new_command_filename = (!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__) . "/" . ucfirst($this->new_command_name) . "Command.php";
    }

    private function setCommandArguments() {
        $this->out("");
        $this->out("    <info>Add arguments</info>");
        $this->out("    <info>Enter the argument command name to add a new one.</info>");
        $this->out("    <info>Press enter to stop add arguments command.</info>", 2);

        $this->new_command_arguments = array();
        $argument_name = "no_argument";
        while ($argument_name != "" || isset($this->new_command_arguments[$argument_name])) {
            $argument_name = $this->ask("<warning>Argument command name (enter to stop): </warning>", 0);
            if (isset($this->new_command_arguments[$argument_name])) {
                $this->out("    <error>Oups, the command has already an argument with the name \"$argument_name\".</error>");
            } else if ($argument_name != "") {
                $this->out("");
                $argument_mode = "";
                $menu = new Menu(array("optional" => "Optional", "require" => "Require"), "require", '    <warning>Choose argument mode</warning>');
                $choice = $menu->prompt();
                $this->line();

                switch ($choice) {
                    case 'optional':
                    case 'require':
                        $argument_mode = $choice;
                        break;
                    default:
                        $argument_mode = "require";
                }
                $argument_descr = $this->ask("    <warning>Argument description (can be empty): </warning>", 0);
                $this->out("");
                $this->out("    <info>Add argument \"$argument_name\" to the command...</info>", 2);

                $this->new_command_arguments[$argument_name] = array('mode' => $argument_mode, "descr" => $argument_descr);
            }
        }
    }

    private function setCommandOptions() {
        $this->out("");
        $this->out("    <info>Add options</info>");
        $this->out("    <info>Enter the option command name to add a new one.</info>");
        $this->out("    <info>Press enter to stop add option command.</info>", 2);

        $this->new_command_options = array();
        $option_name = "no_option";
        while ($option_name != "") {
            $option_name = $this->ask("<warning>Option command name (enter to stop): </warning>", 0);
            if (isset($this->new_command_options[$option_name])) {
                $this->out("    <error>Oups, the command has already an option with the name \"$option_name\".</error>");
            } else if ($option_name != "") {
                $alias = $this->ask("    <warning>Alias option: </warning>", 0);
                //$option, $alias = '', $mode = null, $description = '', $default = null
                $this->out("");
                $mode = "";
                $menu = new Menu(array("optional" => "Optional", "require" => "Require"), "optional", '    <warning>Choose option mode</warning>');
                $choice = $menu->prompt();
                $this->line();

                switch ($choice) {
                    case 'optional':
                    case 'require':
                        $mode = $choice;
                        break;
                    default:
                        $mode = "optional";
                }
                $descr = $this->ask("    <warning>Option description (can be empty): </warning>", 0);
                $default = $this->ask("    <warning>Option default value (can be empty): </warning>", 0);
                $this->out("");
                $this->out("    <info>Add option \"$option_name\" to the command...</info>", 2);

                $this->new_command_options[$option_name] = array('alias' => $alias, 'mode' => $mode, "descr" => $descr, 'default' => $default);
            }
        }
    }

    private function setCommandFlags() {
        $this->out("    <info>Add flags</info>");
        $this->out("    <info>Enter the flag command name to add a new one.</info>");
        $this->out("    <info>Press enter to stop add flag command.</info>", 2);

        $this->new_command_flags = array();
        $flag_name = "no_flag";
        while ($flag_name != "") {
            $flag_name = $this->ask("<warning>Flag command name (enter to stop):</warning>");
            if ($flag_name != "") {
                array_push($this->new_command_flags, $flag_name);
            }
        }
    }

    private function createFileClass() {
        $this->out("");
        $this->out("Create Command <info>$this->new_command_name</info> into the file <info>" . $this->new_command_filename . "</info>", 2);
        if (!is_dir(__DIR__ . "\\..\\..\\..\\..\\..\\" . (!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__))) {
            throw new \Exception("Oups, directory \"" . (!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__) . "\" does not exist.");
        }
        $handle = fopen($this->new_command_filename, "w+");
        fwrite($handle, "<?php\n");
        fwrite($handle, "namespace " . (!is_null($this->namespace) ? $this->namespace : CommandCreatorCommand::__CMD_DIR__) . ";\n");
        fwrite($handle, "\n");
        fwrite($handle, "use Primer\Console\Command\BaseCommand;\n");
        fwrite($handle, "use Primer\Console\Input\DefinedInput;\n");
        fwrite($handle, "\n");
        fwrite($handle, "class " . ucfirst($this->new_command_name) . "Command extends BaseCommand {\n");
        fwrite($handle, "\n");
        fwrite($handle, "    public function configure() {\n");
        fwrite($handle, "        \$this->setName('" . $this->new_command_name . "');\n");
        fwrite($handle, "        \$this->setDescription('" . $this->new_command_description . "');\n");
        if (!empty($this->new_command_arguments)) {
            $this->writeArguments($handle);
        }
        if (!empty($this->new_command_options)) {
            $this->writeOptions($handle);
        }
        fwrite($handle, "        \n");
        fwrite($handle, "    }\n");
        fwrite($handle, "    public function run() {\n");
        fwrite($handle, "        // do what you want...\n");
        fwrite($handle, "    }\n");
        fwrite($handle, "}\n");
        fclose($handle);
    }

    private function writeArguments($handle) {
        foreach ($this->new_command_arguments as $name => $data) {
            fwrite($handle, "        \$this->addArgument('" . $name . "', " . ($data['mode'] == "require" ? "DefinedInput::VALUE_REQUIRED" : "DefinedInput::VALUE_OPTIONAL") . ", '" . $data['descr'] . "');\n");
        }
    }

    private function writeOptions($handle) {
        foreach ($this->new_command_options as $name => $data) {
            fwrite($handle, "        \$this->addOption('" . $name . "', '" . $data['alias'] . "', " . ($data['mode'] == "require" ? "DefinedInput::VALUE_REQUIRED" : "DefinedInput::VALUE_OPTIONAL") . ", '" . $data['descr'] . "', " . ($data['default'] == "" ? "null" : "'" . $data['descr'] . "'") . ");\n");
        }
    }

    private function checkUpdateConfigFile() {
        $update_config = "y";
        do {
            $update_config = $this->ask("<warning>Do you want to update the config file (y/n)? [y]</warning> ", 0);
            if ($update_config == "") {
                $update_config = "y";
            }
        } while (strtoupper($update_config) != "Y" && strtoupper($update_config) != "N");
        if (strtoupper($update_config) == "Y") {
            $this->out("");
            $this->out("    <info>Update config file: add command</info>");

            $config = ConfigReader::getJsonConfig();
            $config['commands'][] = $this->namespace . "/" . ucfirst($this->new_command_name) . "Command";

            $newJsonString = json_encode($config);
            ConfigReader::setJsonConfig($newJsonString);

            exec("php app/cmd $this->new_command_name -h", $output);
            foreach ($output as $out) {
                $this->out($out);
            }
        }
    }

}
