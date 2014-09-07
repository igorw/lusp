<?php

namespace igorw\lusp;

// continuation k takes a pair of [val, env]

function evaluate($expr, $env, callable $k) {
    if (is_string($expr)) {
        if (is_numeric($expr)) {
            $val = (float) $expr;
            return $k([$val, $env]);
        }

        if ('true' === $expr) {
            return $k([true, $env]);
        }

        if ('false' === $expr) {
            return $k([false, $env]);
        }

        if ('null' === $expr) {
            return $k([null, $env]);
        }

        $var = $expr;
        return $k([$env[$var], $env]);
    }

    $fn = array_shift($expr);
    $args = $expr;

    if ('define' === $fn) {
        list($var, $val) = $args;
        return evaluate($val, $env, function ($tuple) use ($k, $env, $var) {
            list($val, $env) = $tuple;
            $env = array_merge($env, [$var => $val]);
            return $k([null, $env]);
        });
    }

    if ('lambda' === $fn) {
        list($lambda_args, $code) = $args;
        $closure = ['closure', $lambda_args, $code, $env];
        return $k([$closure, $env]);
    }

    if ('if' === $fn) {
        list($cond, $then, $else) = $args;
        return evaluate($cond, $env, function ($tuple) use ($k, $env, $then, $else) {
            $cond = $tuple[0];
            $branch = ($cond === true) ? $then : $else;
            return evaluate($branch, $env, function ($tuple) use ($k, $env) {
                $val = $tuple[0];
                return $k([$val, $env]);
            });
        });
    }

    return evaluate($fn, $env, function ($tuple) use ($env, $args, $k) {
        $fn = $tuple[0];
        return array_map_continuation(
            function ($arg, $k) use ($env) {
                return evaluate($arg, $env, function ($tuple) use ($k) {
                    $val = $tuple[0];
                    return $k($val);
                });
            },
            $args,
            function ($args) use ($fn, $env, $k) {
                return apply($fn, $args, $env, $k);
            }
        );
    });
}

function array_map_continuation($fn, $xs, $k) {
    if (count($xs) === 0) {
        return $k([]);
    }
    $x = array_shift($xs);
    return $fn($x, function ($x) use ($fn, $xs, $k) {
        return array_map_continuation($fn, $xs, function ($xs) use ($k, $x) {
            array_unshift($xs, $x);
            return $k($xs);
        });
    });
}

function apply($fn, $args, $env, callable $k) {
    list($_, $lambda_args, $code, $closure_env) = $fn;

    // builtin
    if (is_callable($code)) {
        return $code($args, $env, $k);
    }

    $env = array_merge($env, $closure_env);
    $env = array_merge($env, array_combine($lambda_args, $args));

    return evaluate_code($code, $env, $k);
}

function evaluate_code($code, $env, callable $k) {
    if (count($code) === 0) {
        return $k([null, $env]);
    }

    $expr = array_shift($code);
    return evaluate($expr, $env, function ($tuple) use ($k, $code) {
        list($val, $env) = $tuple;
        if (count($code) === 0) {
            return $k([$val, $env]);
        }
        return evaluate_code($code, $env, $k);
    });
}

function primitive($fn) {
    return [
        'closure',
        null,
        function ($args, $env, $k) use ($fn) {
            $val = call_user_func_array($fn, $args);
            return $k([$val, $env]);
        },
        [],
    ];
}
