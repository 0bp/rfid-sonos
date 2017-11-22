<?php 

namespace bearonahill;

use bearonahill\Exception\KeyboardException;

class Keyboard
{
    private $path;
    private function __construct($path)
    {
        $this->path = $path;
    }

    public static function withPath(string $path)
    {
        if (!file_exists($path)) {
            throw new KeyboardException('Keyboard '.$path.' not found');
        }

        return new static($path);
    }

    public function getPath() : string
    {
        return $this->path;
    }
}
