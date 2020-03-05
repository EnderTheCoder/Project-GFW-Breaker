<?php
$n = 10;
for ($i = 1; $i <= $n; $i++) {
    for ($j = $n - $i; $j > 0; $j--)
        echo ' ';
    for ($j = 1; $j <= $i * 2 - 1; $j++)
        echo '*';
    echo "\n";
}