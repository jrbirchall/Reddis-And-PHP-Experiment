<?php

require __DIR__ . '/RangeAbstract.php';

class RangeModule extends RangeModuleAbstract
{
    public function AddRange($lower, $upper)
    {
        $range_id = uniqid();
        $this->_redis->hmset($this->_range_id, $range_id . "_lower", $lower, $range_id . "_upper", $upper);
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
