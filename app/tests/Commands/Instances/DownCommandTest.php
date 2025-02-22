<?php declare(strict_types=1);

namespace CaT\Doil\Commands\Instances;

use CaT\Doil\Lib\Posix\Posix;
use PHPUnit\Framework\TestCase;
use CaT\Doil\Lib\Docker\Docker;
use CaT\Doil\Lib\ConsoleOutput\Writer;
use CaT\Doil\Lib\FileSystem\Filesystem;
use CaT\Doil\Lib\ConsoleOutput\CommandWriter;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommandWrapper extends DownCommand
{
    public function hasDockerComposeFile(string $path, OutputInterface $output) : bool
    {
        return true;
    }
}

class DownCommandTest extends TestCase
{
    public function test_execute_with_no_instance_no_docker_compose_file() : void
    {
        $docker = $this->createMock(Docker::class);
        $posix = $this->createMock(Posix::class);
        $filesystem = $this->createMock(Filesystem::class);
        $writer = $this->createMock(Writer::class);

        $command = new DownCommand($docker, $posix, $filesystem, $writer);
        $tester = new CommandTester($command);

        $filesystem
            ->expects($this->once())
            ->method("getCurrentWorkingDirectory")
            ->willReturn("/tmp/doil/.doil/instances/master")
        ;

        $execute_result = $tester->execute([]);
        $output = $tester->getDisplay(true);

        $result = "Error:\n";
        $result .= "\tCan't find a suitable docker-compose file in this directory '/tmp/doil/.doil/instances/master'.\n";
        $result .= "\tIs this the right directory?\n\tSupported filenames: docker-compose.yml\n";

        $this->assertEquals($result, $output);
        $this->assertEquals(1, $execute_result);
    }

    public function test_execute_with_no_instance_but_docker_compose_file() : void
    {
        $docker = $this->createMock(Docker::class);
        $posix = $this->createMock(Posix::class);
        $filesystem = $this->createMock(Filesystem::class);
        $writer = $this->createMock(Writer::class);

        $command = new DownCommandWrapper($docker, $posix, $filesystem, $writer);
        $tester = new CommandTester($command);

        $filesystem
            ->expects($this->once())
            ->method("getCurrentWorkingDirectory")
            ->willReturn("/tmp/doil/.doil/instances/master")
        ;

        $docker
            ->expects($this->once())
            ->method("stopContainerByDockerCompose")
            ->with("/tmp/doil/.doil/instances/master")
        ;

        $execute_result = $tester->execute([]);

        $this->assertEquals(0, $execute_result);
    }

    public function test_execute_with_instance_name_no_docker_compose_file() : void
    {
        $docker = $this->createMock(Docker::class);
        $posix = $this->createMock(Posix::class);
        $filesystem = $this->createMock(Filesystem::class);
        $writer = new CommandWriter();
        $instance = "foo_doil_test_98664";

        $command = new DownCommand($docker, $posix, $filesystem, $writer);
        $tester = new CommandTester($command);

        $posix
            ->expects($this->once())
            ->method("getUserId")
            ->willReturn(22)
        ;
        $posix
            ->expects($this->once())
            ->method("getHomeDirectory")
            ->with(22)
            ->willReturn("/home/doil")
        ;

        $execute_result = $tester->execute(["instance" => $instance]);
        $output = $tester->getDisplay(true);

        $result = "Error:\n";
        $result .= "\tCan't find a suitable docker-compose file in this directory '/home/doil/.doil/instances/foo_doil_test_98664'.\n";
        $result .= "\tIs this the right directory?\n\tSupported filenames: docker-compose.yml\n";
        $this->assertEquals($result, $output);
        $this->assertEquals(1, $execute_result);
    }

    public function test_execute_with_instance_name() : void
    {
        $docker = $this->createMock(Docker::class);
        $posix = $this->createMock(Posix::class);
        $filesystem = $this->createMock(Filesystem::class);
        $writer = $this->createMock(Writer::class);
        $instance = "foo_doil_test_98664";

        $command = new DownCommandWrapper($docker, $posix, $filesystem, $writer);
        $tester = new CommandTester($command);

        $posix
            ->expects($this->once())
            ->method("getUserId")
            ->willReturn(22)
        ;
        $posix
            ->expects($this->once())
            ->method("getHomeDirectory")
            ->with(22)
            ->willReturn("/home/doil")
        ;

        $docker
            ->expects($this->once())
            ->method("stopContainerByDockerCompose")
            ->with("/home/doil/.doil/instances/foo_doil_test_98664")
        ;

        $execute_result = $tester->execute(["instance" => $instance]);

        $this->assertEquals(0, $execute_result);
    }
}