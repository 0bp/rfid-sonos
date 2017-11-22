<?php 

namespace bearonahill\Database;

use SQLite3;
use Generator;
use bearonahill\Exception\{
    PlaylistNotFoundException,
    MissingConfigException
};

class PlaylistDatabase
{
    private $db;
    private function __construct(SQLite3 $sqlite)
    {
        $this->db = $sqlite;
        $this->prepareDatabase();
    }

    private function prepareDatabase()
    {
        $this->db->exec('create table if not exists card (id text, playlist text)');
        $this->db->exec('CREATE UNIQUE INDEX IF NOT EXISTS cardid ON card (id)');

        $this->db->exec('create table if not exists config (key text, value text)');
        $this->db->exec('CREATE UNIQUE INDEX IF NOT EXISTS kv ON config (key)');
    }

    public function getCardsAndPlaylists() : Generator
    {
        $stmt = $this->db->prepare('select playlist, id from card order by playlist asc');
        $result = $stmt->execute();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            yield $row;
        }
    }

    public function assignCardToPlaylist(string $id, $playlist)
    {
        $stmt = $this->db->prepare("insert or replace into card (id, playlist) values (:id, :playlist)");
        $stmt->bindValue('id', $id);
        $stmt->bindValue('playlist', $playlist);
        $stmt->execute();
    }

    public function getPlaylistFromCard(string $id) : string
    {
        $stmt = $this->db->prepare('select playlist from card where id = :id');
        $stmt->bindValue(':id', $id);

        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            $this->assignCardToPlaylist($id, null);
            throw new PlaylistNotFoundException('Unkown card');
        }

        if (!$row['playlist']) {
            throw new PlaylistNotFoundException('Unconfigured card');
        }

        return $row['playlist'];
    }

    public static function fromPath(string $path) : PlaylistDatabase
    {
        $sqlite = new SQLite3($path);
        return new static($sqlite);
    }

    public function setConfig(string $key, string $value)
    {
        $stmt = $this->db->prepare('insert or replace into config (key, value) values (:key, :value)');
        $stmt->bindValue('key', $key);
        $stmt->bindValue('value', $value);
        $stmt->execute();
    }

    public function getConfig(string $key) : string
    {
        $stmt = $this->db->prepare('select value from config where key = :key');
        $stmt->bindValue('key', $key);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row === false) {
            throw new MissingConfigException('Missing configuration setting \''.$key.'\'. Please start the config command.');
        }
        return $row['value'];
    }

}