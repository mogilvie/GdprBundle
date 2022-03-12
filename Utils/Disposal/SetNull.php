<?php

namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * Class SetNull.
 */
class SetNull implements DisposalInterface
{
    public function dispose(mixed $parameter): mixed
    {
        return null;
    }
}
