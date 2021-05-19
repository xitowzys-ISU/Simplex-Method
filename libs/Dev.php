<?php

ini_set('display_errors', 'On');
ini_set("error_reporting", E_ALL);

function debug($str = ''){
    echo '<pre>';
    var_dump($str);
    echo '</pre>';
    exit;
}
