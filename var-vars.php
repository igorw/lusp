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
    ['assign-var', ['quote', 'foo'], '12'],
    ['read-var', ['quote', 'foo']],
];

var_dump(evaluate_code($code, $env)[0]);
