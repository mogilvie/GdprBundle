<?php

namespace SpecShaper\GdprBundle\Event;

use SpecShaper\GdprBundle\Model\PersonalData;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DisposeEvent.
 *
 * An event thrown when a parameter is to be disposed of.
 *
 * Can be used to intercept parameter disposal and implement a custom method.
 */
class DisposeEvent extends Event
{
    /**
     * DisposeEvent constructor.
     *
     * @param string $parameter The parameter to be disposed of
     * @param string $method    The disposal method
     */
    public function __construct(
        protected string $parameter,
        protected ?string $method = PersonalData::DISPOSE_BY_SET_NULL,
        protected ?array $args = [])
    {}

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
