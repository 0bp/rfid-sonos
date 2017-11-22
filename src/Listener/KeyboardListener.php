<?php 

namespace bearonahill\Listener;

use bearonahill\{
    Keyboard,
    Exception\InvalidKeyCodeException
};

class KeyboardListener
{
    const STRUCT_KEYBOARD_EVENT_SIZE = 8+2+2+4;
    const STRUCT_FORMAT = 'ltv_sec/ltv_usec/Stype/Scode/Ivalue';

    private $keyCodeTable = [
        '2' => 1,
        '3' => 2,
        '4' => 3,
        '5' => 4,
        '6' => 5,
        '7' => 6,
        '8' => 7,
        '9' => 8,
        '10' => 9,
        '11' => 0,
        '28' => PHP_EOL
    ];

    /** @var callable */
    private $callback;

    /** @var Keyboard */
    private $keyboard;

    public function __construct(Keyboard $keyboard)
    {
        $this->keyboard = $keyboard;
    }

    /**
     * Function to be called once a command has been completed
     *
     * @param callable $callback 
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    public function run()
    {
        $fp = fopen($this->keyboard->getPath(), 'rb');
        $code = [];
        while ($buffer = fread($fp, self::STRUCT_KEYBOARD_EVENT_SIZE)) {

            if ($this->bufferTooSmall($buffer)) {
                continue;
            }

            $data = unpack(self::STRUCT_FORMAT, $buffer);

            if (!$this->isValidKeyReleaseEvent($data)) {
                continue;
            }

            if ($this->codeIsComplete($data['code'])) {
                call_user_func($this->callback, implode(',', $code));
                $code = [];
                continue;
            }

            $code[] = $this->getKeyFromKeyCode($data['code']);
        }
    }

    /**
     * Checks if received event is from same size as requested struct size
     * 
     * @param  string $buffer 
     * @return bool 
     */
    private function bufferTooSmall(string $buffer) : bool
    {
        return !(strlen($buffer) === self::STRUCT_KEYBOARD_EVENT_SIZE);
    }

    /**
     * Check if it's a valid event from a key release 
     * 
     * @param  array $data unpack()'ed event data
     * @return boolean
     */
    private function isValidKeyReleaseEvent(array $data) : bool
    {
        // value 0 is key released event
        // type 1 is a key event
        return $data['type'] === 1 && $data['value'] === 0;
    }

    /**
     * Card reader sends ENTER after a completed card code. This function checks
     * against PHP_EOL.
     * 
     * @param  string $code 
     * @return bool
     */
    private function codeIsComplete(string $code) : bool
    {
        return $this->getKeyFromKeyCode($code) === PHP_EOL;
    }

    /**
     * Maps linux key codes to key values
     *
     * @throws InvalidKeyCodeException
     * @param  string $code A linux key code
     * @return int|string Mapped key value
     */
    private function getKeyFromKeyCode(string $code) 
    {
        if (!isset($this->keyCodeTable[$code])) {
            throw new InvalidKeyCodeException('Invalid KeyCode');
        }

        return $this->keyCodeTable[$code];
    }
}