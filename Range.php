<?php

require __DIR__ . '/RangeAbstract.php';

class RangeModule extends RangeModuleAbstract
{
    public function AddRange($lower, $upper)
    {
        $this->_redis->hmset($this->_range_id, "lower", $lower, "upper", $upper);
    }

    public function  QueryRange($lower, $upper)
    {
    }

    public function  RemoveRange($lower, $upper)
    {
        //fill in...
    }

    public function dump()
    {
        print_r($this->_redis->hgetall($this->_range_id));
    }
}
