<?php

require __DIR__ . '/redisent/Redis.php';

// A Range Module is a module that tracks ranges of numbers.

abstract class RangeModuleAbstract
{
    protected $_redis = null;

    public function __construct()
    {
        // creates redis connection localhost on port 6379
        $this->_redis = new redisent\Redis('localhost', 6379);
    }

    public function __destruct()
    {
        // clears redis storage
        $this->_redis->flushdb();
    }

    // AddRange: Given an input range it starts tracking the range.
    // Eg: AddRange(10, 200) . starts tracking range 10 . 200
    // AddRange(150, 180) . starts tracking range 150 . 180.
    // AddRange(250, 500) . starts tracking range 250 . 500.
    // Make sure that you efficiently track overlapping ranges.
    abstract public function AddRange($lower, $upper);

    // QueryRange: Given an input range, this returns whether the range
    // is being tracked or not. Eg: QueryRange(50, 100) .
    // Returns TRUE as this is being tracked
    // QueryRange(180, 300) . Returns False as only a partial of this range
    // is being tracked QueryRange(600, 1000) . Returns False as this range is not tracked
    abstract public function  QueryRange($lower, $upper);

    // DeleteRange: Given input range is untracked after this call has been made.
    // If the range does not exists then it is a no­op.
    // Eg: DeleteRange(50, 150) . stops tracking range 50 . 150
    abstract public function  RemoveRange($lower, $upper);
}
