<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Content-Type:application/json; charset=utf-8');
require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Data/return_core.php';
require 'Core/custom_functions.php';
require 'Core/Security/token_core.php';
require 'Core/DB/Redis/redis_core.php';
session_start();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
$redis = new redis_core();
$sql = 'INSERT INTO main_access_log (ip_addr, user_agent, is_login, timestamp, access_url, referer, language) VALUES (?, ?, ?, ?, ?, ?, ?)';
$params = array(
    1 => getIP(),
    2 => $_SERVER['HTTP_USER_AGENT'],
    3 => null,
    4 => time(),
    5 => $_SERVER['HTTP_REFERER'],
    6 => $_POST['referer'],
    7 => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
);
if ($token->sessionJudge()) {
    $params[3] = $_SESSION['uid'];
}
$mysql->bind_query($sql, $params);
$sql = 'SELECT * FROM main_ip_log WHERE ip_addr = ?';
$params = array(1 => getIP());
$result = $mysql->bind_query($sql, $params);
if (!$result) {
    $sql = 'INSERT INTO main_ip_log(ip_addr, last_update) VALUES (?, ?)';
    $params = array(
        1 => getIP(),
        2 => time(),
    );
    $mysql->bind_query($sql, $params);
} else {
    $sql = 'UPDATE main_ip_log SET last_update = ?, cnt = cnt + 1 WHERE ip_addr = ?';
    $params = array(
        1 => time(),
        2 => getIP(),
    );
    $mysql->bind_query($sql, $params);
}
$return->retMsg('success');