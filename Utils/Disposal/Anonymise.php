<?php

namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * Anonymise.
 *
 * Replace a set of strings in a text object with placeholder characters.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class Anonymise implements DisposalInterface
{
    private string $anonCharacter;

    public function __construct(array $arguments = [])
    {
        $this->anonCharacter = '*';

        if (!empty($arguments) && array_key_exists('replaceWith', $arguments)) {
            $this->anonCharacter = $arguments['replaceWith'];
        }
    }

    public function dispose(mixed $parameter): mixed
    {
        if (empty($this->anonCharacter)) {
            return null;
        }

        if (strlen($this->anonCharacter) > 1) {
            return $this->anonCharacter;
        }

        $length = strlen($parameter);

        return str_repeat($this->anonCharacter, $length);
    }
}
