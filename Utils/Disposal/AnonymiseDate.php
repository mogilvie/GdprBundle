<?php
/**
 * GdprBundle/Utils/Disposal/AnonymiseDate.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2018, SpecShaper - All rights reserved
 *
 */
namespace SpecShaper\GdprBundle\Utils\Disposal;

use Faker\Provider\DateTime;

/**
 * AggregateDate.
 *
 * A class to convert a date to an aggregated value;
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class AnonymiseDate implements DisposalInterface
{
    const CLOSEST_DAY = 'DAY';
    const CLOSEST_SUNDAY = 'SUNDAY';
    const CURRENT_MONTH = 'CURRENT_MONTH';
    const CURRENT_QUARTER = 'QUARTER';
    const CURRENT_YEAR = 'YEAR';
    const CURRENT_DECADE = 'DECADE';
    const CURRENT_CENTURY = 'CENTURY';
 
    private $anonymiseBy;
 
    public function __construct(array $arguments = []){
        $this->anonymiseBy = self::CURRENT_MONTH;

        if(!empty($arguments) && array_key_exists('type', $arguments)){
            $this->anonymiseBy = $arguments['type'];
        }

    }
 
    public function dispose($dateTime){
       return $this->convert($dateTime);
    }
    
    private function convert(\DateTimeInterface $dateTime){
        switch($this->anonymiseBy){
            case self::CLOSEST_DAY:

                // Get the hour in 24 hour terms.
                $hour = $dateTime->format('H');

                // Set time to 0.
                $dateTime->setTime(0,0);

                // If before noon return same day.
                if($hour < 12){
                    $result =  $dateTime;
                } else {
                    $result = $dateTime->modify('+1 day');
                }
                break;
            case self::CLOSEST_SUNDAY:

                // Set time to 0.
                $dateTime->setTime(0,0);

                // 0=sun -> 6=Sat
                $dayOfWeek = $dateTime->format('w');

                if($dayOfWeek < 4){
                    $result = $dateTime->modify("-$dayOfWeek days");
                } else {
                    $modifier = 7-$dayOfWeek;
                    $result = $dateTime->modify("+$modifier days");
                }

                break;
            case self::CURRENT_MONTH:
                $result = date_create_from_format('Y-m-d', $dateTime->format('Y-m-01'));
                $result->setTime(0,0);
                break;
            case self::CURRENT_QUARTER:
                $month = (int)$dateTime->format('n');
                $year = $dateTime->format('Y');

                switch(true){
                    case $month <= 3;
                        $quarter = 1;
                        break;
                    case $month <= 6;
                        $quarter = 4;
                        break;
                    case $month <= 9;
                        $quarter = 7;
                        break;
                    default:
                        $quarter = 10;
                }

                $result = new \DateTime();
                $result->setDate($year, $quarter, 01);
                $result->setTime(0,0);
                break;
            case self::CURRENT_YEAR:

                $year = $dateTime->format('Y');

                $result = new \DateTime();
                $result->setDate($year, 1, 1);
                $result->setTime(0,0);

                break;
            case self::CURRENT_DECADE:

                $year = $dateTime->format('Y');

                $century = substr($year, 0,2);
                $decade = substr($year, -2, 1);

                $year = $century*100 + $decade*10 ;

                $result = new \DateTime();
                $result->setDate($year, 1, 1);
                $result->setTime(0,0);

                break;
            case self::CURRENT_CENTURY:
                $year = $dateTime->format('Y');

                $century = substr($year, 0,2);

                $year = $century*100;

                $result = new \DateTime();
                $result->setDate($year, 1, 1);
                $result->setTime(0,0);

                break;
        }

        return $result;
    }
}
