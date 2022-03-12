<?php

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
    private string $anonIPv4;
    private string $anonIPv6;

    /**
     * AnonymiseIP constructor.
     */
    public function __construct(array $arguments = [])
    {
        $this->anonIPv4 = '255.255.255.0';
        $this->anonIPv6 = 'ffff:ffff:ffff::';

        if (array_key_exists('anonIPv4', $arguments)) {
            $this->anonIPv4 = $arguments['anonIPv4'];
        }

        if (array_key_exists('anonIPv6', $arguments)) {
            $this->anonIPv6 = $arguments['anonIPv6'];
        }
    }

    /**
     * Convert an IPv4 or IPv6 string into a generic mask and return it.
     */
    public function dispose(mixed $parameter): mixed
    {
        // Check IP Address Type
        if (filter_var($parameter, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->anonIPv4;
        }

        if (filter_var($parameter, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->anonIPv6;
        }

        return null;
    }
}
