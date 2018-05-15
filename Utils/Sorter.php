<?php

namespace SpecShaper\GdprBundle\Utils;

class Sorter
{

    const ORDER_DESC = 'DESC';
    const ORDER_ASC = 'ASC';

    protected $firstSortOrder;

    protected $secondSortOrder;

    protected $order;

    public function __construct($firstSortOrder, $secondSortOrder, $order = self::ORDER_DESC)
    {
        $this->firstSortOrder = $firstSortOrder;
        $this->secondSortOrder = $secondSortOrder;
        $this->order = $order;
    }

    /**
     * Order an array by two fields,
     *
     * @param        $a
     * @param        $b
     *
     * @return int
     */
    public function sortByTwoColumnsCallback($a, $b){

        $firstOrder = $this->firstSortOrder;

        $secondOrder= $this->secondSortOrder;

        $methodArray = array(self::ORDER_DESC => 'descSort', self::ORDER_ASC => 'ascSort');

        if ($a[$firstOrder] == $b[$firstOrder])
        {
            // employeeId is the same, sort by lastName
            if ($a[$secondOrder] > $b[$secondOrder]) return 1;
        }

        if($method = $methodArray[$this->order]) {
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