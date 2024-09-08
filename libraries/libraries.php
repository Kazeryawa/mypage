<?php
function get_sql(){
    $servername = "p:localhost";
    $username = "splish_check";
    $password = "";
    $dbname = "splish_check";
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn;
}
function checkStringFormat($string) {
    $pattern = '/^[a-zA-Z0-9_\-@.]+$/'; // 正则表达式模式
    return preg_match($pattern, $string) === 1;
}
function getTimeStampMS() {
    list($msec, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}
?>