<?php
/**
 * SpecShaper/GdprBundle/Utils/Portability/ExportFactory.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */

namespace SpecShaper\GdprBundle\Utils\Portability;

/**
 * ExportFactory.
 *
 * A factory class to return an export class for the configured data format.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class ExportFactory
{
    private string $dataFormat;

    public function __construct($dataFormat)
    {
        $this->dataFormat = $dataFormat;
    }

    public function createExportClass($exportClass)
    {
        return new $exportClass();
    }
}
