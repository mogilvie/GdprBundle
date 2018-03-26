<?php
/**
 * SpecShaper/GdprBundle/Utils/Disposal/Aggregate.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;

/**
 * Aggregate
 *
 * Aggregate a value to a range.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class Aggregate implements DisposalInterface
{
 
    public function dispose($parameter){
       return null;
    }
 
}
