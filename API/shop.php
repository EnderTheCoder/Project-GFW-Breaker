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
$sign->initParams($_POST);
if ($sign->checkSign() !== true) $return->retMsg($sign->checkSign());
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
        $mysql->bind_query($sql, $params);
        $costs = round($mysql->fetchLine('price') * $discount * $_POST['month'], 2);
        $plan = $mysql->fetchLine(null);
        $sql = 'SELECT money FROM main_users WHERE uid = ?';
        $params = array(1 => $_SESSION['uid']);
        $mysql->bind_query($sql, $params);
        if($costs > $mysql->fetchLine('money')) $return->retMsg('success', array(
            'is_successful' => false,
            'msg' => '余额不足，请充值',
        ));
        $sql = 'UPDATE main_users SET money = money - ?, money_out = money_out + ? WHERE uid = ?';
        $params = array(
            1 => $costs,
            2 => $costs,
            3 => $_SESSION['uid']
        );
        $mysql->bind_query($sql, $params);
        $sql = 'UPDATE main_plan SET buy_cnt = buy_cnt + 1';
        $mysql->bind_query($sql);
        $sql = 'INSERT INTO main_user_billing (uid, type, money, timestamp, payment_method) VALUE (?, ?, ?, ?, ?)';
        $params = array(
            1 => $_SESSION['uid'],
            2 => 'subscription',
            3 => $costs,
            4 => time(),
            5 => 'local'
        );
        $mysql->bind_query($sql, $params);
        $sql = 'INSERT INTO main_user_plan (uid, name, lim_time, flow, charge, info, parent, lim_flow) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $params = array(
            1 => $_SESSION['uid'],
            2 => $plan['name'],
            3 => time() + $_POST['month'] * 2592000,
            4 => 0,
            5 => $costs,
            6 => $plan['info'],
            7 => $plan['id'],
            8 => $plan['flow_limit'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', array('is_successful' => true));
    }
    default:
    {
        $return->retMsg('dbgMsg');
    }
}