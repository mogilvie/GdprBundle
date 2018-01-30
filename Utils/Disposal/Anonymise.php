<?php
/**
 * GdprBundle/Utils/Disposal/Anonymise.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace GdprBundle/Utils/Disposal;

use GdprBundle/Utils/Disposal/DisposalInterface;

/**
 * Anonymise.
 *
 * Replace a set of strings in a text object with placeholder characters. 
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 */
class Anonymise implements DisposalInterface
{
 
    public function dispose($parameter){
       return null;
    }
 
}
