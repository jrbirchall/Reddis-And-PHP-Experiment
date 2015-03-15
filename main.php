<?php

//
//  main.php
//  codescreen

require __DIR__ . '/RangeModule.php';

function main()
{
    // insert code here...
    echo "Hello World!\n";

    //
    // sample test cases
    //
    $range = new RangeModule();
    
    // add a large range
    $range->AddRange(100, 1000000);
    
    // returns true;
    if (FALSE == $range->QueryRange(500, 800000))
    {
        echo "Failed AddRange test.\r\n";
        print_r($range->getRanges());
    }
    
    // create a hole in the range
    $range->RemoveRange(550, 700000);

    // returns false
    if (TRUE == $range->QueryRange(500, 800000))
    {
        echo "Failed RemoveRange test.\r\n";
        print_r($range->getRanges());
    }

    // restore hole with overlapping ranges
    $range->AddRange(400, 100000);
    $range->AddRange(72000, 900010);

    // returns true
    if (FALSE == $range->QueryRange(500, 800000))
    {
        echo "Failed Restore range test.\r\n";
        print_r($range->getRanges());
    }
}

main();
