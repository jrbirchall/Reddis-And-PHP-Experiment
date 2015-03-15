<?php
class Range
{
    public $upper = null;
    public $lower = null;
    const CONTAINS_FULLY = 0;
    const PARTIAL_OVERLAP_LOWER = 1;
    const PARTIAL_OVERLAP_UPPER = -1;
    const DISJOINT_LOWER = 2;
    const DISJOINT_UPPER = -2;

    public static function cmpRanges( $a, $b )
    {
        return $a->cmp($b);
    }

    public function __construct( $lower, $upper )
    {
        if ($lower > $upper)
        {
            $this->upper = $lower;
            $this->lower = $upper;
        }
        else
        {
            $this->lower = $lower;
            $this->upper = $upper;
        }
    }

    public function cmp( $range )
    {
        // Full overlap
        if ($this->lower <= $range->lower && $this->upper >= $range->upper)
        {
            return self::CONTAINS_FULLY;
        }
        // Partial overlap, lower.
        elseif ($this->lower <= $range->upper && $this->lower >= $range->lower)
        {
            return self::PARTIAL_OVERLAP_LOWER;
        }
        // Partial overlap, upper.
        elseif ($this->upper <= $range->upper && $this->upper >= $range->lower)
        {
            return self::PARTIAL_OVERLAP_UPPER;
        }
        // Disjoint, lower.
        elseif ($this->lower >= $range->upper)
        {
            return self::DISJOINT_LOWER;
        }
        // Disjoint upper.
        elseif ($this->upper <= $range->lower)
        {
            return self::DISJOINT_UPPER;
        }
        return 777;
    }
}

?>
