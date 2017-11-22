<?php

namespace bearonahill\Command;

use Symfony\Component\Console\{
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface,
    Command\Command
};

use bearonahill\{
    Database\PlaylistDatabase,
    Exception\FilesystemException,
    Helper\Filesystem
};

class AssignCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('assign')
            ->addOption(
                'card',
                null,
                InputOption::VALUE_REQUIRED,
                'Card ID string')
            ->addOption(
                'playlist',
                null,
                InputOption::VALUE_REQUIRED,
                'Sonos Playlist to start playing when scanning the card')
        ;

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $path = Filesystem::getBasePath();

        $card = $input->getOption('card');
        $playlist = $input->getOption('playlist');

        $database = PlaylistDatabase::fromPath($path.'/database.sqlite');
        $database->assignCardToPlaylist($card, $playlist);

        return 0;
    }
}
