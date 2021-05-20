<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table></table>
</body>
</html>

<?php
require 'configs/config.php';
require 'libs/Dev.php';
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

$targetFunc2 = [1, -1, -3, 'min'];

$matrix2 = [
    [2, -1, 1, '<=', 1],
    [4, -2, 1, '>=', -2],
    [3, 0, 1, '<=', 5]
];

$targetFunc3 = [-2, -1, 2, 'max'];

$matrix3 = [
    [1, 1, -1, '>=', 8],
    [1, -1, 2, '>=', 2],
    [-2, -8, 3, '>=', 1]
];

$targetFunc4 = [2, 3, 0, -1, 0, 0, 'max'];

$matrix4 = [
    [2, -1, 0, -2, 1, 0, '=', 16],
    [3, 2, 1, -3, 0, 0, '=', 18],
    [-1, 3, 0, 4, 0, 1, '=', 24]
];

$simplex = new Simplex($targetFunc4, $matrix4);

$simplex->run();
