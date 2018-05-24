<?php

/**
 * SpecShaper\GdprBundle\Event\EncryptEvents.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
namespace SpecShaper\GdprBundle\Event;

/**
 * EncryptEvents.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
final class AccessEvents
{
    const LOAD = 'gdpr.personal_data.load';
    const UPDATE = 'gdpr.personal_data.update';
    const DELETE = 'gdpr.personal_data.delete';
}
