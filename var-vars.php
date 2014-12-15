<?php

namespace igorw\lusp;

require 'lusp.php';

$env = [
    'assign-var' => [
        'closure',
        null,
        function ($args, $env) {
            list($var, $val) = $args;
            $env[$var] = $val;
            return [$val, $env];
        },
        [],
    ],
    'read-var' => [
        'closure',
        null,
        function ($args, $env) {
            list($var) = $args;
            $val = $env[$var];
            return [$val, $env];
        },
        [],
    ],
];

$code = [
    ['define', 'foo', ['quote', 'this value is going to be the dynamic variable name']],
    ['assign-var', 'foo', '12'],
    ['read-var', 'foo'],
];

list($val, $env) = evaluate_code($code, $env);

var_dump($val, array_keys($env));
