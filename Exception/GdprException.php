<?php

namespace SpecShaper\GdprBundle\Exception;

/**
 * Encrypt Exception.
 **
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class GdprException extends \Exception
{
    /**
     * The value trying to be encrypted.
     */
    private ?string $value;

    /**
     * Constructor.
     *
     * Typically provide a custom message key when throwing the exception.
     * Set the text and html of the exception in the messages translation file.
     *
     * @since Available since Release 1.0.0
     *
     * @param string $message Optional message
     */
    public function __construct(?string $message, ?string $value)
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

    /**
     * Set Value.
     *
     * @param ?string $value
     *
     * @return GdprException
     */
    public function setValue(?string $value)
    {
        $this->value = $value;

        return $this;
    }
}
