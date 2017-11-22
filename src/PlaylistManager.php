<?php 

namespace bearonahill;

use bearonahill\Exception\MissingConfigException;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Tracks\TextToSpeech;
use duncan3dc\Sonos\Directory;
use Doctrine\Common\Cache\ArrayCache;
use bearonahill\Database\PlaylistDatabase;
use bearonahill\Exception\PlaylistNotFoundException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class PlaylistManager
{
    /** @var callable */
    private $responseCallback;

    private $targetRoom;
    private $database;

    private $sonos;
    private $controller;

    public function __construct(PlaylistDatabase $database, TargetRoom $targetRoom)
    {
        $this->database = $database;
        $this->targetRoom = $targetRoom;

        $this->sonos = new Network(new ArrayCache);
        $this->controller = $this->sonos->getControllerByRoom($targetRoom->getName());
    }

    public function setResponseCallback(callable $callback)
    {
        $this->responseCallback = $callback;
    }

    public function useCard(string $code)
    {
        $playlist = null;

        try {
            $playlist = $this->database->getPlaylistFromCard($code);
        } catch (PlaylistNotFoundException $e) {
            $this->respondWithMessage($e->getMessage());
            $this->playNotification($e->getMessage());
            return;
        }

        if ($playlist !== null) {
            $this->replacePlaylist($playlist);
        }
    }

    private function replacePlaylist(string $playlistName)
    {
        $playlist = $this->sonos->getPlaylistByName($playlistName);
        $tracks = $playlist->getTracks();

        if (!is_array($tracks) || count($tracks) === 0) {
            $this->respondWithMessage('Added '.count($tracks).' to the playlist');
            $this->playNotification('The selected playlist is empty or does not exist.');
            return;
        }

        $this->controller->getQueue()->clear()->addTracks($tracks);
        $this->respondWithMessage('Added '.count($tracks).' to the playlist');
        $this->controller->play();
    }

    private function respondWithMessage(string $message)
    {
        call_user_func($this->responseCallback, $message);
    }

    private function playNotification(string $message)
    {
        $smbUrl = null;
        $smbFolder = null;

        try {
            $smbFolder = $this->database->getConfig('smb.folder');
            $smbUrl = $this->database->getConfig('smb.url');
        } catch (MissingConfigException $e) {
            $this->respondWithMessage($e->getMessage());
        }

        if (empty($smbFolder) || empty($smbUrl)) {
            $this->respondWithMessage('Audio notification not configured.');
            return;
        }

        $adapter = new Local($smbFolder);
        $filesystem = new Filesystem($adapter);

        $directory = new Directory($filesystem, $smbUrl, "tts");

        $track = new TextToSpeech($message, $directory);
        $this->controller->interrupt($track);
    }
}
