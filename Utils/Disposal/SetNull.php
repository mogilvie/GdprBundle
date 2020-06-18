<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 11:22
 */

namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * Class SetNull
 *
 * @package SpecShaper\GdprBundle\Utils\Disposal
 */
class SetNull implements DisposalInterface
{

    public function dispose($parameter)
    {
        return null;
    }
}