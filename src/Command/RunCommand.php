<?php

namespace bearonahill\Command;

use Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface,
    Command\Command,
    Logger\ConsoleLogger
};

use bearonahill\{
    Keyboard,
    Listener\KeyboardListener,
    Database\PlaylistDatabase,
    TargetRoom,
    PlaylistManager,
    Helper\Filesystem
};

class RunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs the RFID listener to play music on Sonos')
        ;

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $logger = new ConsoleLogger($output);

        $path = Filesystem::getBasePath();
        $logger->debug('Using path '.$path);
        $database = PlaylistDatabase::fromPath($path.'/database.sqlite');

        $room = $database->getConfig('room');
        $keyboard = $database->getConfig('keyboard');

        $logger->debug('Using sonos room '.$room);
        $logger->debug('Using keyboard '.$keyboard);

        $targetRoom = TargetRoom::withName($room);
        $keyboardSource = Keyboard::withPath($keyboard);

        $playlistManager = new PlaylistManager($database, $targetRoom);
        $playlistManager->setResponseCallback(function($message) use ($logger) {
            $logger->debug($message);
        });

        $keyboardListener = new KeyboardListener($keyboardSource);
        $keyboardListener->setCallback(function($code) use ($playlistManager, $logger) {
            $logger->debug('Detected card '.$code);
            $playlistManager->useCard($code);
        });

        $keyboardListener->run();

        return 0;
    }
}
