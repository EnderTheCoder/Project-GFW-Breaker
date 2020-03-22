<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Content-Type:application/json; charset=utf-8');
require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
require 'Core/custom_functions.php';
require 'Core/Security/token_core.php';
require 'Core/DB/Redis/redis_core.php';
session_start();
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
$redis = new redis_core();
if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'get-plan':
    {
        $sql = 'SELECT id, `name`, price, flow_limit, info FROM main_plan';
        $mysql->bind_query($sql);
        $return->retMsg('success', $mysql->fetch(true));
    }
    case 'buy':
    {
        if (!$token->sessionJudge()) $return->setMsg('tokenFailed');
        if (isEmpty($_POST['id']) || isEmpty($_POST['month'])) $return->retMsg('emptyParam');
        if ($_POST['month'] < 3) $discount = 1;
        if ($_POST['month'] == 12) $discount = 0.7;
        if ($_POST['month'] >= 3 && $_POST['month'] < 6) $discount = 0.8;
        if ($_POST['month'] >= 6 && $_POST['month'] < 12) $discount = 0.9;
        $sql = 'SELECT * FROM main_plan WHERE id = ?';
        $params = array(1 => $_POST['id']);
        $plan = $mysql->bind_query($sql, $params);
        $sql = 'INSERT INTO main_user_billing (uid, type, money, timestamp, payment_method) VALUE (?, ?, ?, ?, ?)';
        $params = array(
            1 => $_SESSION['uid'],
            2 => 'buy',
            3 => round($mysql->fetchLine('price') * $discount, 2),
            4 => time(),
            5 => 'local'
        );
        $mysql->bind_query($sql, $params);
    }
    default:
    {
        $return->retMsg('dbgMsg');
    }
}