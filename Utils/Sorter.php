<?php

namespace SpecShaper\GdprBundle\Utils;

class Sorter
{
    public const ORDER_DESC = 'DESC';
    public const ORDER_ASC = 'ASC';

    public function __construct(protected $firstSortOrder, protected $secondSortOrder, protected ?string $order = self::ORDER_DESC)
    {
    }

    public function sortByTwoColumnsCallback($a, $b): int
    {
        $firstOrder = $this->firstSortOrder;

        $secondOrder = $this->secondSortOrder;

        $methodArray = [self::ORDER_DESC => 'descSort', self::ORDER_ASC => 'ascSort'];

        if ($a[$firstOrder] == $b[$firstOrder]) {
            // employeeId is the same, sort by lastName
            if ($a[$secondOrder] > $b[$secondOrder]) {
                return 1;
            }
        }

        if ($method = $methodArray[$this->order]) {
            return $this->$method($a[$firstOrder], $b[$firstOrder]);
        }

        return 1;
    }
}
