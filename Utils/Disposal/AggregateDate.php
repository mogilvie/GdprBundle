<?php
/**
 * GdprBundle/Utils/Disposal/AggregateDate.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace GdprBundle/Utils/Disposal;

use GdprBundle/Utils/Disposal/DisposalInterface;

/**
 * AggregateDate.
 *
 * A class to convert a date to an aggregated value;
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 */
class AggregateDate implements DisposalInterface
{
    const AGGREGATE_BY_DAY = 'DAY';
    const AGGREGATE_BY_WEEK = 'WEEK';
    const AGGREGATE_BY_MONTH = 'MONTH';
    const AGGREGATE_BY_QUARTER = 'QUARTER';
    const AGGREGATE_BY_YEAR = 'YEAR';
    const AGGREGATE_BY_HALF_DECADE = 'HALF_DECADE';
    const AGGREGATE_BY_DECADE = 'DECADE';
    const AGGREGATE_BY_HALF_CENTURAY = 'HALF_CENTURARY';
    const AGGREGATE_BY_CENTURARY = 'CENTURARY';
 
    private $aggregateBy;
 
    public function __construct($aggregateBy){
       $this->aggregateBy = $aggregateBy;
    }
 
    public function aggregate(\DateTimeInterface $dateTime){
       return;
    }
 
}
