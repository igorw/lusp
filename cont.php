<?php

namespace igorw\lusp;

require 'lusp.php';

$env = [
    'call/cc' => [
        'closure',
        null,
        function ($args, $env, $k) {
            list($fn) = $args;
            $kont = [
                'closure',
                null,
                function ($args, $env, $k) use ($env, $k) {
                    list($val) = $args;
                    return $k([$val, $env]);
                },
                []
            ];
            return apply($fn, [$kont], $env, $k);
        },
        [],
    ],
];

$code = [
    ['define', 'f', ['lambda', ['return'],
        [['return', '2'],
         '3']]],
    // ['f', ['lambda', ['x'], ['x']]],
    ['call/cc', 'f'],
];

var_dump(evaluate_code($code, $env, function ($x) { return $x; })[0]);
