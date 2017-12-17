<?php

namespace bearonahill\Command;

use Psr\Log\LogLevel;

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
    Keyboard,
    TargetRoom,
    PlaylistManager
};

class InfoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('info')
        ;

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $verbosityLevelMap = array(
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL
        );

        $logger = new ConsoleLogger($output, $verbosityLevelMap);

        $path = Filesystem::getBasePath();
        $logger->debug('Using path '.$path);
        $database = PlaylistDatabase::fromPath($path.'/database.sqlite');

        $room = $database->getConfig('room');
        $targetRoom = TargetRoom::withName($room);

        $playlistManager = new PlaylistManager($database, $targetRoom);
        $playlistManager->setResponseCallback(function($message) use ($logger) {
            $logger->info($message);
        });

        $playlistManager->whatIsRunning();

        return 0;
    }
}
