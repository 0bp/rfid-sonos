#!/usr/bin/env php
<?php

date_default_timezone_set('UTC');
ini_set('memory_limit', '-1');
define('APPLICATION_DIR', __DIR__);

require __DIR__ . '/vendor/autoload.php';

use bearonahill\{
    Command\RunCommand,
    Command\ConfigCommand,
    Command\AssignCommand
};

use Symfony\Component\Console\Application;

$console = new Application('rfid-sonos');
$console->add(new RunCommand());
$console->add(new ConfigCommand());
$console->add(new AssignCommand());

$console->run();
