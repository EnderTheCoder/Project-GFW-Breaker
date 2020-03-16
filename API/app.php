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
if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'login':
    {
        if (isEmpty($_POST['id']) || isEmpty($_POST['password'])) $return->retMsg('emptyParam');
        $sql = 'SELECT *  FROM main_users';
        if (strstr($_POST['id'], '@') !== false) $sql .= ' WHERE email = ?';
        else $sql .= ' WHERE username = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
        if ($mysql->fetchLine('state') != null) $return->retMsg('stateUnavailable', $mysql->fetchLine('state'));
        $sql = 'INSERT INTO main_login_log (ip_addr, uid, timestamp, is_successful, device) VALUES (?, ?, ?, ?, ?)';
        $params = array(
            1 => getIP(),
            2 => $result[0]['uid'],
            3 => time(),
            4 => 1,
            5 => $sign->getDevice(),
        );
        if (!$result[0] || $result[0]['password'] != md5($_POST['password'] . PASSWORD_SALT)) {
            $params[4] = 0;
            $params[2] = 0;
            $mysql->bind_query($sql, $params);
            $return->retMsg('passErr');
        }
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', $token->set($result[0]['uid'], $sign->getDevice(), getIP()));
        break;
    }
    case 'get-plan':
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        $sql = 'SELECT plan_id FROM main_users WHERE uid = ?';
        $tokens = $token->getByValue($_POST['token']);
        $params = array(1 => $tokens['uid']);
        $mysql->bind_query($sql, $params);
        if (!$mysql->fetchLine('plan_id')) $return->retMsg('noResult', '没有选择有效套餐');
        $sql = 'SELECT parent, lim_time, lim_flow, flow FROM main_user_plan WHERE id = ?';
        $params = array(1 => $mysql->fetchLine('plan_id'));
        $mysql->bind_query($sql, $params);
        if ($mysql->fetchLine('lim_time') < time()) $return->retMsg('noResult', '套餐已过期');
        if ($mysql->fetchLine('lim_flow') < $mysql->fetchLine('flow')) $return->retMsg('noResult', '套餐流量不足');
        $sql = 'SELECT * FROM main_vmess WHERE vmess_group = ?';
        $params = array(1 => $mysql->fetchLine('parent'));
        $return->retMsg('success', $mysql->bind_query($sql, $params));
    }
    case 'logout':
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        $token->del($_POST['token']);
        $return->setMsg('success');
    }
}