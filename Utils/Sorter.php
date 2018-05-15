<?php

namespace SpecShaper\GdprBundle\Utils;

class Sorter
{

    const ORDER_DESC = 'DESC';
    const ORDER_ASC = 'ASC';
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
     * @param        $a
     * @param        $b
     * @param string $order
     *
     * @return int
     */
    public function sortByTwoColumnsCallback($a, $b, $order = self::ORDER_DESC){

        $firstOrder = $this->firstSortOrder;

        $secondOrder= $this->secondSortOrder;

        $methodArray = array(self::ORDER_DESC => 'descSort', self::ORDER_ASC => 'ascSort');

        if ($a[$firstOrder] == $b[$firstOrder])
        {
            // employeeId is the same, sort by lastName
            if ($a[$secondOrder] > $b[$secondOrder]) return 1;
        }

        if($method = $methodArray[$order]) {
            return $this->$method($a[$firstOrder],$b[$firstOrder]);
        }
    }

    private function descSort($aField, $bField){
        return $aField < $bField ? 1 : -1;
    }

    private function ascSort($aField, $bField){
        return $aField > $bField ? 1 : -1;
    }
}