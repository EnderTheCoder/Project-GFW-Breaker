<?php
//require 'config.php';
//require 'Core/DB/MySQL/mysql_core.php';
//require 'Core/DB/Redis/redis_core.php';
//require 'Core/custom_functions.php';
//echo strtoupper(md5('app_id1id用户名password密码timestamp1581952284typeloginssssssssss'));
function gcd($a, $b)
{
    if ($a % $b == 0) return $b;
    return gcd($b, $a % $b);
}

//
//function gcd2($a, $b)
//{
//    $temp = $a;
//    do {
//        $a = $b;
//        $b = $temp % $b;
//        $temp = $a;
//    } while ($a % $b != 0);
//    return $b;
//}
function gcd2($u, $v)
{
    do {
        $t = $u % $v;
        $u = $v;
        $v = $t;
    } while ($v);
    return $u;
}

echo gcd2(10, 5);