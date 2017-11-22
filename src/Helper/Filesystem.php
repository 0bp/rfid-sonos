<?php

namespace bearonahill\Helper;

use bearonahill\Exception\FilesystemException;

class Filesystem
{
    public static function getBasePath()
    {
        $path = $_SERVER['HOME'].'/rfid-sonos';

        if (!file_exists($path) && !@mkdir($path) && !is_dir($path)) {
            throw new FilesystemException('Path does not exists and unable to create: '.$path);
        }

        return $path;
    }
}