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
//require 'Core/DB/Redis/redis_core.php';
session_start();
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
//$redis = new redis_core();
if (!$token->sessionJudge()) $return->setMsg('tokenFailed');
if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'plan-all':
    {
        $sql = 'SELECT id, name, lim_time, flow, lim_flow, speed_rank, charge, info FROM main_user_plan WHERE uid = ?';
        $params = array(1 => $_SESSION['uid']);
        $result = $mysql->bind_query($sql, $params);
        $result['row'] = countX($result);
        $sql = 'SELECT plan_id FROM main_users WHERE uid = ?';
        $mysql->bind_query($sql, $_SESSION['uid']);
        $result['chosen'] = $mysql->fetchLine('plan_id');
        $return->retMsg('success', $result);
    }

    case 'billing-all':
    {
        $sql = 'SELECT type, money, timestamp, id FROM main_user_billing WHERE uid = ?';
        $params = array(1 => $_SESSION['uid']);
        $result = $mysql->bind_query($sql, $params);
        $result['row'] = count($result);
        $return->retMsg('success', $result);
    }

    case 'billing-top':
    {
        $sql = 'SELECT money, invite_tot, invite_token FROM main_users WHERE uid = ?';
        $params = array(1 => $_SESSION['uid']);
        $return->retMsg('success', $mysql->bind_query($sql, $params));
    }

    case 'chose-plan':
    {
        if (isEmpty($_POST['id'])) $return->retMsg('emptyParam');
        $sql = 'UPDATE main_users SET plan_id = ? WHERE uid = ?';
        $params = array(
            1 => $_POST['id'],
            2 => $_SESSION['uid'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'get-feedback':
    {
        $array = array(
            'invite_feedback_rating' => getSetting('invite_feedback_rating'),
            'invite_recharge_rating' => getSetting('invite_recharge_rating'),
            'invite_daily_limit' => getSetting('invite_daily_limit'),
        );
        $return->retMsg('success', $array);
    }
}