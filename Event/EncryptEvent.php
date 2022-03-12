<?php

namespace SpecShaper\GdprBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * EncryptEvent.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class EncryptEvent extends Event
{
    /**
     * The string to be encrypted or decrypted.
     *
     * @since Available since Release 1.0.0
     */
    protected string $value;

    protected string $action;

    public function __construct(string $value, ?string $action = EncryptEvents::ENCRYPT)
    {
        $this->value = $value;
        $this->action = $action;
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
