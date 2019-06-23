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

    private $anonCharacter;

    /**
     * @param string $anonIPv4 A default anon IPv4
     * @param string $anonIPv6 TA default anon  IPv6
     */
    public function __construct(array $arguments = [])
    {
        $this->anonCharacter =  '*';

        if(!empty($arguments) && array_key_exists('replaceWith', $arguments)){
            $this->anonCharacter = $arguments['replaceWith'];
        }
    }

    public function dispose($parameter){

        if(empty($this->anonCharacter)){
            return null;
        }

        if(strlen($this->anonCharacter) > 1){
            return $this->anonCharacter;
        }

        $length = strlen($parameter);

        $result = str_repeat($this->anonCharacter, $length);

        return $result;
    }
 
}
