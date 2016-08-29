<?php

namespace Primer\Console\Command\Demo;

use Primer\Console\Command\BaseCommand;
use ConfigReader\ConfigReader;

class ListServicesCommand extends BaseCommand {

    public function configure() {
        $this->setName('service:list')->setDescription("List all services.");
    }

    public function run() {
        $this->out("");
        $this->out("<info>Available services:</info>", 2);
        foreach (ConfigReader::getJsonServices() as $service_name => $service_data) {
            $this->out("     <info>" . $service_name . "</info>");
            $this->out("          <info>args: </info>" . (isset($service_data['arguments']) ? implode(",", $service_data['arguments']) : ""));
            $this->out("          <info>desc:</info> " . (isset($service_data['description']) ? $service_data['description'] : ""));
        }
    }
}
