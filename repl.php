<?php

namespace igorw\lusp;

require 'lusp.php';

// terrible example
//
// > ["define", "a", "1"]
// NULL
// > "a"
// float(1)
// >

function read() {
    echo '> ';
    $line = trim(fgets(STDIN));
    return json_decode($line, true);
}

function print_val($expr) {
    var_dump($expr);
}

$env = [
    '<' => primitive(function ($a, $b) { return $a < $b; }),
    '+' => primitive(function ($a, $b) { return $a + $b; }),
    '-' => primitive(function ($a, $b) { return $a - $b; }),
];

while (null !== ($expr = read())) {
    list($val, $env) = evaluate($expr, $env);
    print_val($val);
}

var_dump($expr);
