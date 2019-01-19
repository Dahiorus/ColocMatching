<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class CommandWithDryRun extends Command
{
    protected function configure()
    {
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Execute in simulation mode");
    }


    protected function isDryRunEnabled(InputInterface $input) : bool
    {
        return $input->getOption("dry-run") === true;
    }
}
