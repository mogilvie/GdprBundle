<?php
/**
 * SpecShaper/GdprBundle/Utils/Disposal/Categorise.php
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;

/**
 * Categorise
 *
 * Change a value to a category for anonymisation.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 */
class Categorise implements DisposalInterface
{
    public function dispose($parameter){
       return null;
    }
}
