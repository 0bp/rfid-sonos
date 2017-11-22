<?php 

namespace bearonahill;

class TargetRoom
{
    private $name;
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function withName(string $name) : TargetRoom
    {
        return new static($name);
    }

    public function getName() : string
    {
        return $this->name;
    }
}
