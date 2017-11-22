<?php

namespace bearonahill\Command;

use Symfony\Component\Console\{
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface,
    Command\Command,
    Logger\ConsoleLogger
};

use bearonahill\{
    Database\PlaylistDatabase,
    Helper\Filesystem,
    Keyboard
};

class ConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config')
            ->addOption(
                'room',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sonos room name')
            ->addOption(
                'keyboard',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to keyboard device file')
        ;

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $logger = new ConsoleLogger($output);

        $path = Filesystem::getBasePath();

        $room = $input->getOption('room');
        $keyboard = $input->getOption('keyboard');

        $database = PlaylistDatabase::fromPath($path.'/database.sqlite');
        if ($room) {
            $database->setConfig('room', $room);
            $logger->info(sprintf('Successfully set "room" to "%s"', $room));
        }

        if ($keyboard) {
            $kb = Keyboard::withPath($keyboard);
            $database->setConfig('keyboard', $kb->getPath());
            $logger->info(sprintf('Successfully set "keyboard" to "%s"', $keyboard));
        }

        return 0;
    }
}
