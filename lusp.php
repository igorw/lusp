<?php

namespace igorw\lusp;

// all functions return a pair of [val, env]

function evaluate($expr, $env = []) {
    if (is_string($expr)) {
        if (is_numeric($expr)) {
            $val = (float) $expr;
            return [$val, $env];
        }

        if ('true' === $expr) {
            return [true, $env];
        }

        if ('false' === $expr) {
            return [false, $env];
        }

        if ('null' === $expr) {
            return [null, $env];
        }

        $var = $expr;
        return [$env[$var], $env];
    }

    $fn = array_shift($expr);
    $args = $expr;

    if ('define' === $fn) {
        list($var, $val) = $args;
        list($val, $env) = evaluate($val, $env);
        $env = array_merge($env, [$var => $val]);
        return [null, $env];
    }

    if ('lambda' === $fn) {
        list($lambda_args, $code) = $args;
        $closure = ['closure', $lambda_args, $code, $env];
        return [$closure, $env];
    }

    if ('assign-var' === $fn) {
        list($var, $code) = $args;
        $env[$var] = evaluate($code, $env)[0];
        return [null, $env];
    }

    if ('read-var' === $fn) {
        list($var) = $args;
        $val = $env[$var];
        return [$val, $env];
    }

    if ('if' === $fn) {
        list($cond, $then, $else) = $args;
        $cond = evaluate($cond, $env)[0];
        if ($cond === true) {
            $val = evaluate($then, $env)[0];
        } else {
            $val = evaluate($else, $env)[0];
        }
        return [$val, $env];
    }

    $fn = evaluate($fn, $env)[0];
    $args = array_map(function ($arg) use ($env) { return evaluate($arg, $env)[0]; }, $args);
    return apply($fn, $args, $env);
}

function apply($fn, $args, $env) {
    list($_, $lambda_args, $code, $closure_env) = $fn;

    // builtin
    if (is_callable($code)) {
        return $code($args, $env);
    }

    $env = array_merge($env, $closure_env);
    $env = array_merge($env, array_combine($lambda_args, $args));

    return evaluate_code($code, $env);
}

function evaluate_code($code, $env = []) {
    foreach ($code as $expr) {
        list($val, $env) = evaluate($expr, $env);
    }
    return [$val, $env];
}

function primitive($fn) {
    return [
        'closure',
        null,
        function ($args, $env) use ($fn) {
            $val = call_user_func_array($fn, $args);
            return [$val, $env];
        },
        [],
    ];
}
