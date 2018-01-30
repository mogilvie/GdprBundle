<?php

/**
 * SpecShaper\GdprBundle\Event\EncryptEvent.php.
 *
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2015, SpecShaper - All rights reserved
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 *
 * @since       Available since Release 1.0.0
 */

namespace SpecShaper\GdprBundle\Event;

/**
 * EncryptEvent.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 *
 * @version     Release: 1.0.0
 */
class EncryptEvent
{


    /**
     * The string / object to be encrypted or decrypted
     *
     * @since Available since Release 1.0.0
     *
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $action;

    /**
     * EncryptEvent constructor.
     *
     * @param        $value
     * @param string $action
     */
    public function __construct($value, $action = EncryptEvents::ENCRYPT)
    {
        $this->value= $value;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }


}
