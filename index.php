<?php
require 'configs/config.php';
require 'libs/Dev.php';
require 'core/Table.php';
require 'core/Simplex.php';

/**
 * f(x) = x1 + x2 + x3 -> max
 * 
 * -3 x1 + 4 x2 + 2 x3 <= 6
 * -2 x1 - 2 x2 + 2 x3 >= -6
 * -2 x1 + x2 + x3 = 2
 */

$targetFunc = [1, 1, 1, 'max'];

$matrix = [
    [-3, 4, 2, '<=', 6],
    [-2, -2, 2, '>=', -6],
    [-2, 1, 1, '=', 2]
];

$matrix2 = [
    [2, -1, 1, '<=', 1],
    [4, -2, 1, '>=', -2],
    [3, 0, 1, '<=', 5]
];

$simplex = new Simplex($targetFunc, $matrix);

$simplex->run();
