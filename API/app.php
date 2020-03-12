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
if ($token->judgeToken($_POST['token']['uid'],))
    if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'get-plan':
    {
        $sql = 'SELECT plan_id FROM main_users WHERE uid = ?';
        $params = array(1 => $token->getVal('uid'));
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
}