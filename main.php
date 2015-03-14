<?php

//
//  main.php
//  codescreen

require __DIR__ . '/Range.php';

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
    $range->QueryRange(500, 800000);
    
    // create a hole in the range
    $range->RemoveRange(550, 700000);

    // returns false
    $range->QueryRange(500, 800000);

    // restore hole with overlapping ranges
    $range->AddRange(400, 100000);
    $range->AddRange(72000, 900010);

    // returns true
    $range->QueryRange(500, 800000);
    
}

main();
