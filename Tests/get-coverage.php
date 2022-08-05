<?php

list($util, $percentage, $mode) = $argv + array(null,90,null);

exec('php -f Tests/coverage-checker.php -- Tests/clover.xml ' . ($percentage??90), $output, $resultCode);
if($mode){
    echo $output[0] . "\n";
} else {
    preg_match('/\d{1,3}\.\d{2}/', $output[0], $matches);
    $coverage = $matches[0] ?? '0.00';
    echo $coverage . '%';
}



exit($resultCode);

