<?php

namespace SpecShaper\GdprBundle\Utils;

class Sorter
{
    public $firstSortOrder;

    protected $secondSortOrder;

    public function __construct($firstSortOrder, $secondSortOrder)
    {
        $this->firstSortOrder = $firstSortOrder;
        $this->secondSortOrder = $secondSortOrder;
    }

    /**
     * Order an array by two fields,
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function sortByTwoColumnsCallback($a, $b){

        $firstOrder = $this->firstSortOrder;

        $secondOrder= $this->secondSortOrder;

        if ($a[$firstOrder] == $b[$firstOrder])
        {
            // employeeId is the same, sort by lastName
            if ($a[$secondOrder] > $b[$secondOrder]) return 1;
        }
        // sort the higher employeeId first:
        return $a[$firstOrder] < $b[$firstOrder] ? 1 : -1;
    }
}