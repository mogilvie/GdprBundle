<?php

namespace SpecShaper\GdprBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EncryptEvent extends Event
{
    /**
     * @param string      $value  the string to be encrypted or decrypted
     * @param string|null $action The action of encrypt or decrypt. Encrypt by default.
     */
    public function __construct(
        protected string $value,
        protected ?string $action = EncryptEvents::ENCRYPT)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }
}
