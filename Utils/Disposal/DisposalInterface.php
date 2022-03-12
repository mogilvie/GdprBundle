<?php

namespace SpecShaper\GdprBundle\Utils\Disposal;

interface DisposalInterface
{
    /**
     * @param mixed $parameter The parameter to be disposed of
     *
     * @return mixed the converted/disposed of parameter after execution
     */
    public function dispose(mixed $parameter): mixed;
}
