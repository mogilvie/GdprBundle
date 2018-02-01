<?php
/**
 * GdprBundle/Utils/Disposal/AggregateDate.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\Disposalnterface;

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
    const AGGREGATE_BY_HALF_CENTURY = 'HALF_CENTURY';
    const AGGREGATE_BY_CENTURY = 'CENTURY';
 
    private $aggregateBy;
 
    public function __construct($aggregateBy){
       $this->aggregateBy = $aggregateBy;
    }
 
    public function dispose($dateTime){
       return $this->convert($dateTime);
    }
    
    private function convert(\DateTimeInterface $dateTime){
        switch($this->aggregateBy){
            case self::AGGREGATE_BY_DAY:
                break;
            case self::AGGREGATE_BY_WEEK:
                break;
            case self::AGGREGATE_BY_MONTH:
                break;
            case self::AGGREGATE_BY_QUARTER:
                break;
            case self::AGGREGATE_BY_YEAR:
                break;
            case self::AGGREGATE_BY_HALF_DECADE:
                break;
            case self::AGGREGATE_BY_DECADE:
                break;
            case self::AGGREGATE_BY_HALF_CENTURY:
                break;
            case self::AGGREGATE_BY_CENTURY:
                break;
        }
    }
 
}
