<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Content-Type:application/json; charset=utf-8');

require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
require 'Lib/LibSMTP.php';
require 'Core/custom_functions.php';
require 'Core/Security/token_core.php';
require 'Core/DB/Redis/redis_core.php';

session_start();
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
$redis = new redis_core();

$sign->initParams($_POST);
if ($sign->checkSign() !== true) $return->retMsg($sign->checkSign());

if (isEmpty($_POST['name'])) $return->retMsg('emptyParam');

$sql = 'SELECT version FROM main_apps WHERE name = ?';
$mysql->bind_query($sql, $_POST['name']);
$return->retMsg('success', $mysql->fetchLine('version'));