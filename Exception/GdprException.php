<?php

namespace SpecShaper\GdprBundle\Exception;

class GdprException extends \Exception
{
    /**
     * The value trying to be encrypted.
     */
    private ?string $value;

    /**
     * Constructor.
     *
     * Typically, provide a custom message key when throwing the exception.
     * Set the text and html of the exception in the messages' translation file.
     */
    public function __construct(?string $message, ?string $value = null)
    {
        if (null === $message) {
            $message = 'ssg.exception.gdprException';
        }

        $this->value = $value;

        parent::__construct($message);
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): GdprException
    {
        $this->value = $value;

        return $this;
    }
}
