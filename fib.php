<?php

namespace igorw\lusp;

require 'lusp.php';

$env = [
    '<' => primitive(function ($a, $b) { return $a < $b; }),
    '+' => primitive(function ($a, $b) { return $a + $b; }),
    '-' => primitive(function ($a, $b) { return $a - $b; }),
];

$code = [
    ['define', 'fib', ['lambda', ['n'],
        [['if', ['<', 'n', '2'],
                'n',
                ['+', ['fib', ['-', 'n', '1']],
                      ['fib', ['-', 'n', '2']]]]]]],
    ['fib', '12'],
];

var_dump(evaluate_code($code, $env)[0]);
