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

    public function __construct( $lower, $upper )
    {
        $this->lower = $lower;
        $this->upper = $upper;
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
        $range_set = $this->getRanges();
        $range = new Range($lower, $upper);
        array_push($range_set, $range);
        $range_set = $this->CollapseRanges($range_set);
        $this->putRanges($range_set);
    }

    public function  QueryRange($lower, $upper)
    {
        return TRUE;
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

    protected function putRanges( $range_set )
    {
        $this->_redis->hset($this->_range_collection_id, "rangeset", serialize($range_set));
    }

    protected function getRanges()
    {
        $ranges =  $this->_redis->hget($this->_range_collection_id, "rangeset");
        if ($ranges == null)
            return array();
        return unserialize($ranges);
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
