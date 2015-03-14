<?php

require __DIR__ . '/RangeAbstract.php';

function cmpRanges($a, $b)
{
    if ($a->lower == $b->lower)
        return 0;
    return ($a->lower < $b->lower) ? -1 : 1;
}

class Range
{
    public $upper = null;
    public $lower = null;

    public function __construct( $array )
    {
        if (count($array) == 2)
        {
            $this->lower = $array[0];
            $this->upper = $array[1];
        }
    }
}

class RangeModule extends RangeModuleAbstract
{
    protected $_range_collection_id = null;
    public function __construct()
    {
        parent::__construct();
        $this->_range_collection_id = uniqid();
    }

    public function AddRange($lower, $upper)
    {
        $range_id = $this->_range_collection_id . $this->_redis->hincrby($this->_range_collection_id, "rangecount", 1);
        $this->_redis->hmset($range_id, "lower", $lower, "upper", $upper);

        $range_set = $this->getAllRanges();
        $range = new Range($lower, $upper);
        array_push($range_set, $range);
        $range_set = $this->CollapseRanges($range_set);
    }

    public function  QueryRange($lower, $upper)
    {
        $range_count = $this->_redis->hget($this->_range_collection_id, "rangecount");
        for ($i = 1; $i <= $range_count; $i++)
        {
            $range = new Range( $this->_redis->hvals($this->_range_collection_id . $i));
            if ($lower <=  $range->lower
                && $lower <= $range->upper
                && $upper >= $range->lower
                && $upper >= $range->upper)
                return TRUE;
        }
        return FALSE;
    }

    public function  RemoveRange($lower, $upper)
    {
        //fill in...
    }

    public function dump()
    {
        print_r($this->_redis->hgetall($this->_range_collection_id));
        for ($i = 1; $i <= $this->getRangeCount(); $i++)
        {
            print_r($this->_redis->hgetall($this->_range_collection_id . $i));
        }
    }

    protected function setRanges( $range_set )
    {
        $this->_redis->hmset($this->_range_collection_id, "rangeset", serialize($range_set));
    }

    protected function getRangeCount()
    {
        return $this->_redis->hget($this->_range_collection_id, "rangecount");
    }

    protected function getAllRanges()
    {
        $range_set = array();
        for ($i = 1; $i <= $this->getRangeCount(); $i++)
        {
            $range = new Range( $this->_redis->hvals($this->_range_collection_id . $i));
            array_push( $range_set, $range );
        }
        return $range_set;
    }

    protected function CollapseRanges( $range_array )
    {
        if (count( $range_array ) <= 1) 
            return $range_array;

        $range_out = array();
        if (FALSE == usort($range_array, "cmpRanges"))
        {
            echo "Error with usort.\r\n";
        }

        $bottom_range = $range_array[0];
        for ($i = 1; $i < count( $range_array ); $i++)
        {
            $range_next = $range_array[$i];
            if ($bottom_range->upper > $range_next->lower)
            {
                if ($bottom_range->upper > $range_next->upper)
                {
                    // complete overlap
                    // ignore $range_next
                } 
                elseif ($bottom_range->upper < $range_next->upper)
                {
                    // partial overlap.  Extend bottom.
                    $bottom_range->upper = $range_next->upper;
                }
            }
            else
            {
                array_push($range_out, $bottom_range);
                $bottom_range = $range_next;
            }
        }
        array_push($range_out, $bottom_range);

        return $range_out;
    }
}
