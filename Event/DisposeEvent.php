<?php

namespace SpecShaper\GdprBundle\Event;

use SpecShaper\GdprBundle\Model\PersonalData;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DisposeEvent.
 *
 * An event thrown when an parameter is to be disposed of.
 *
 * Can be used to interecpt parameter disposal and implement a custom method.
 */
class DisposeEvent extends Event
{
    /**
     * The string / object to be encrypted or decrypted.
     *
     * @since Available since Release 1.0.0
     */
    protected string $parameter;

    protected string $method;

    protected array $args;

    /**
     * DisposeEvent constructor.
     *
     * @param        $parameter the parameter to be disposed of
     * @param string $method    The disposal method
     */
    public function __construct(string $parameter, ?string $method = PersonalData::DISPOSE_BY_SET_NULL, ?array $args = [])
    {
        $this->parameter = $parameter;
        $this->method = $method;
        $this->args = $args;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function setParameter(string $parameter): DisposeEvent
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): DisposeEvent
    {
        $this->method = $method;

        return $this;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): DisposeEvent
    {
        $this->args = $args;

        return $this;
    }
}
