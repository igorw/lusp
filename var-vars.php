<?php

namespace igorw\lusp;

require 'lusp.php';

$code = [
    ['assign-var', 'foo', '12'],
    ['read-var', 'foo'],
];

var_dump(evaluate_code($code)[0]);
