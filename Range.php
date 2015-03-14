<?php

require __DIR__ . '/RangeAbstract.php';

/**
    RangeModule contains a collection of Ranges stored in 
    a redis backend as a serialised sorted array of Range elements.

    The elements are serialised sorted so that we optimise reads.
*/
class RangeModule extends RangeModuleAbstract
{
    protected $_range_collection_id = null;
    public function __construct()
    {
        parent::__construct();
        $this->_range_collection_id = uniqid();
    }

    /**
        Executes in O(N lg N) where N is the number of 
        range elements previously stored.

        Takes a network hop to get the data from the datastore 
        and another ot put it back.

        Data is stored in redis sorted, so that operation dominates.
    */
    public function AddRange($lower, $upper)
    {
        $range_set = $this->getRanges();
        $range_set[] = new Range($lower, $upper);
        $range_set = $this->UnionRanges($range_set);
        $this->putRanges($range_set);
    }

    /**
        Executes in O(N) where N is the number of range
        elements previously stored.

        Takes a network hop to get the data from the datastore.
    */
    public function QueryRange($lower, $upper)
    {
        $range_set = $this->getRanges();
        for ($i=0; $i < count($range_set); $i++)
        {
            $range = $range_set[$i];
            if ($range->lower <= $lower && $range->upper > $lower
                && $range->upper >= $upper && $range->lower < $upper)
                return TRUE;
        }
        return FALSE;
    }

    /**
        Executes in O(N lg N) where N is the number of range elements previously stored.
        
        Takes a network hop to get the data from the datastore and another
        to put it back.

        Data is stored in redis sorted, so that operation dominates.
    */
    public function RemoveRange($lower, $upper)
    {
        $range_set = $this->getRanges();
        $range_set = $this->excludeRange($range_set, $lower, $upper);
        usort($range_set, "cmpRanges");
        $this->putRanges($range_set);
    }

    /**
        Executes in O(C + N) where C is the constant for redis RTT and
        N is the number of Range elements returned.

        Uses N*sizeof(Range)and N*sizeof(serialize(Range)) memory.
    */
    public function getRanges()
    {
        $ranges =  $this->_redis->hget($this->_range_collection_id, "rangeset");
        if ($ranges == null)
            return array();
        return unserialize($ranges);
    }

    /**
        Executes in O(N lg N) where N is the size of $range_set.

        Data is stored sorted to optimise reads which usually predominate.
    */
    protected function putRanges( $range_set )
    {
        $this->_redis->hset($this->_range_collection_id, "rangeset", serialize($range_set));
    }

    /**
        Executes in O(n lg n) where n is the size of the passed in array.
    */
    protected function UnionRanges( $range_array )
    {
        $range_out = array();

        if (count( $range_array ) == 0)
            return $range_out;

        usort($range_array, "cmpRanges");

        // O(n) traversal
        $bottom_range = $range_array[0];
        for ($i = 1; $i < count( $range_array ); $i++)
        {
            $range_next = $range_array[$i];
            if ($bottom_range->upper > $range_next->lower)
            {
                // Complete overlaps we simply don't add to the 
                // results.
                if ($bottom_range->upper < $range_next->upper)
                {
                    // partial overlap.  Extend bottom.
                    $bottom_range->upper = $range_next->upper;
                }
            }
            else
            {
                $range_out[] = $bottom_range;
                $bottom_range = $range_next;
            }
        }
        $range_out[] = $bottom_range;

        return $range_out;
    }

    /**
        Executes in O(N) where N is the size of $range_set.

        Takes 2(N) memory where N is the size of $range_set and
        returns a copy of $range_set.
    */
    protected function excludeRange($range_set, $lower, $upper)
    {
        $range_out = array();
        for ($i=0; $i < count($range_set); $i++)
        {
            $range = $range_set[$i];
            // full overlap
            if ($range->lower <= $lower 
                && $range->upper > $lower
                && $range->lower <= $upper 
                && $range->upper > $upper)
            {
                // Split the range into two.
                $range_lower = new Range($range->lower, $lower);
                $range_upper = new Range($upper, $range->upper);
                $range = $range_lower;
                $range_out[] = $range_lower;
                $range_out[] = $range_upper;
                continue;
            }

            // Overlap on the lower side.
            if ($range->lower <= $lower && $range->upper > $lower)
            {
                $range->upper = $lower;
            }

            // Overlap on the upper side.
            if ($range->lower <= $upper && $range->upper > $upper)
            {
                $range->lower = $upper;
            }
            $range_out[] = $range;
        }
        return $range_out;
    }

}

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

