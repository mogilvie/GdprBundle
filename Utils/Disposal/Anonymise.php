<?php
/**
 * SpecShaper/GdprBundle/Utils/Disposal/Anonymise.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;

/**
 * Anonymise.
 *
 * Replace a set of strings in a text object with placeholder characters. 
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class Anonymise implements DisposalInterface
{
 
    public function dispose($parameter){
       return null;
    }
 
}
