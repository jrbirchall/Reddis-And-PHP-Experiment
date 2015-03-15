<?php
class IntervalTreeNode
{
    public $range = null;
    public $left = null;
    public $right = null;

    public function __construct( $range )
    {
        $this->range = $range;
    }
}
?>
