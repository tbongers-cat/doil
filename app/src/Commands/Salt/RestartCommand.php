<?php declare(strict_types=1);

/* Copyright (c) 2022 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace CaT\Doil\Commands\Salt;

use CaT\Doil\Lib\Docker\Docker;
use CaT\Doil\Lib\ConsoleOutput\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends Command
{
    protected const SALT_PATH = "/usr/local/lib/doil/server/salt";

    protected static $defaultName = "salt:restart";
    protected static $defaultDescription = "Restarts the salt main server";

    protected Docker $docker;
    protected Writer $writer;

    public function __construct(Docker $docker, Writer $writer)
    {
        parent::__construct();

        $this->docker = $docker;
        $this->writer = $writer;
    }


    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        if (! $this->docker->isInstanceUp(self::SALT_PATH)) {
            $this->docker->startContainerByDockerCompose(self::SALT_PATH);
            return Command::SUCCESS;
        }

        $this->writer->beginBlock($output, "Restart salt main");
        $this->docker->stopContainerByDockerCompose(self::SALT_PATH);
        $this->docker->startContainerByDockerCompose(self::SALT_PATH);
        sleep(3);
        $instances = array_filter($this->docker->getRunningInstanceNames());
        foreach ($instances as $instance) {
            if ($instance == "doil_saltmain" || $instance == "doil_postfix") {
                continue;
            }
            $this->docker->executeDockerCommand(
                $instance,
                "supervisorctl start startup"
            );
        }
        $this->writer->endBlock();

        return Command::SUCCESS;
    }
}