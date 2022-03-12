<?php

namespace SpecShaper\GdprBundle\Utils\Portability;

/**
 * ExportServiceInterface.
 *
 * A interface for services that export database information.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 *
 * @version     Release: 1.0.0
 */
interface ExportServiceInterface
{    
    public function export();
}
