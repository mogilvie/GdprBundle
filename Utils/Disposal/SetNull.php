<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 11:22
 */

namespace SpecShaper\GdprBundle\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;

/**
 * Class SetNull
 *
 * @package SpecShaper\GdprBundle\Utils\Disposal
 */
class SetNull implements \SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface
{

    public function dispose($parameter)
    {
        return null;
    }
}