<?php
declare(strict_types=1);

namespace bearonahill\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class RunCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new \bearonahill\Command\RunCommand());

        $command = $application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}