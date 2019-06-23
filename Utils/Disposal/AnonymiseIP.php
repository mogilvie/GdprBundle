<?php
/**
 * SpecShaper/GdprBundle/Utils/Disposal/AnonymiseIp.php.
 *
 * @author      Mark Ogilvie <m.ogilvie@parolla.ie>
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * AnonymiseIP.
 *
 * Replace a set of strings in a text object with placeholder characters. 
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class AnonymiseIP implements DisposalInterface
{

    private $anonIPv4;
    private $anonIPv6;

    /**
     * AnonymiseIP constructor.
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->anonIPv4 = '255.255.255.0';
        $this->anonIPv6 = 'ffff:ffff:ffff::';

        if(array_key_exists('anonIPv4', $arguments)){
            $this->anonIPv4 = $arguments['anonIPv4'];
        }

        if(array_key_exists('anonIPv6', $arguments)){
            $this->anonIPv6 =  $arguments['anonIPv6'];
        }
    }

    /**
     * Convert an IPv4 or IPv6 string into a generic mask and return it.
     *
     * @param string $parameter
     * @return mixed|null|string
     */
    public function dispose($parameter){

        // Check IP Address Type
        if(filter_var($parameter, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            return $this->anonIPv4;
        }

        if(filter_var($parameter, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
            return $this->anonIPv6;
        }

        return null;
    }
}
