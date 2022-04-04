<?php

namespace SpecShaper\GdprBundle\Event;

final class AccessEvents
{
    public const LOAD = 'gdpr.personal_data.load';
    public const UPDATE = 'gdpr.personal_data.update';
    public const DELETE = 'gdpr.personal_data.delete';
}
