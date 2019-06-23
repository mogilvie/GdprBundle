<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 11:21
 */

namespace SpecShaper\GdprBundle\Utils\Disposal;

interface DisposalInterface
{

    /**
     * @param $parameter The parameter to be disposed of.
     *
     * @return mixed The converted/disposed of parameter after execution.
     */
    public function dispose($parameter);
}