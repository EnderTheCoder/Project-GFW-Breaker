<?php

function fib($n)
{
    if ($n == 1 || $n == 2)
        return 1;
    $a = 1;
    $b = 1;
    $c = 0;
    for ($i = 3; $i <= $n; $i++) {
        $c = $a + $b;
        $a = $b;
        $b = $c;
    }
    return $c;
}

echo "第一百个fibonacii数为:";
echo fib(100);