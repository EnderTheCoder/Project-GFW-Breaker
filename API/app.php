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

/*
    Fix the bug that only form data can be processed.
*/

if (count($_POST) == 1) { 
    $Json = json_decode($_POST[0], true)
    foreach($Json as $key => $value) {
        $_POST[$key] = $value
    }
}


$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
$redis = new redis_core();
$sign->initParams($_POST);
if ($sign->checkSign() !== true) $return->retMsg($sign->checkSign());
if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'login'://登录接口
    {
        if (isEmpty($_POST['id']) || isEmpty($_POST['password'])) $return->retMsg('emptyParam');
        $sql = 'SELECT *  FROM main_users';
        if (strstr($_POST['id'], '@') !== false) $sql .= ' WHERE email = ?';
        else $sql .= ' WHERE username = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
        if ($mysql->fetchLine('state') != null) $return->retMsg('stateUnavailable', $mysql->fetchLine('state'));
        $sql = 'INSERT INTO main_login_log (ip_addr, uid, `timestamp`, is_successful, device) VALUES (?, ?, ?, ?, ?)';
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
        $tokens = $token->set($result[0]['uid'], $sign->getDevice(), getIP());
        if ($tokens) $return->retMsg('success', $tokens);
        else $return->retMsg('tokenFailed');
        break;
    }
    case 'get-plan'://获取订阅线路列表
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        $sql = 'SELECT plan_id FROM main_users WHERE uid = ?';
        $tokens = $token->getByValue($_POST['token']);
        $params = array(1 => $tokens['uid']);
        $mysql->bind_query($sql, $params);
        if (!$mysql->fetchLine('plan_id')) $return->retMsg('noResult', array('msg' => '没有选择有效套餐'));
        $sql = 'SELECT parent, lim_time, lim_flow, flow FROM main_user_plan WHERE id = ?';
        $params = array(1 => $mysql->fetchLine('plan_id'));
        $mysql->bind_query($sql, $params);
        if ($mysql->fetchLine('lim_time') < time()) $return->retMsg('noResult', array('msg' => '套餐已过期'));
        if ($mysql->fetchLine('lim_flow') < $mysql->fetchLine('flow')) $return->retMsg('noResult', array('msg' => '套餐流量不足'));
        $sql = 'SELECT `name`, son FROM main_plan WHERE id = ?';
        $params = array(1 => $mysql->fetchLine('parent'));
        $mysql->bind_query($sql, $params);
        $son = json_decode($mysql->fetchLine('son'), true);
        $cnt = 0;
        $result = array();
        for ($i = 0; $i < countX($son); $i++) {
            $sql = 'SELECT `name` FROM main_vmess_group WHERE id = ?';
            $mysql->bind_query($sql, $son[$i]);
            $name = $mysql->fetchLine('name');
            $sql = 'SELECT id, area, config FROM main_vmess WHERE vmess_group = ?';
            $vmess_group = $mysql->bind_query($sql, $son[$i]);
            for ($j = 0; $j < $mysql->getRowNum(); $j++) {
                $result[$cnt] = $vmess_group[$j];
                $result[$cnt]['parent'] = $name;
                $result[$cnt][3] = $name;
                $cnt++;
            }
        }
        $return->retMsg('success', $result);
    }
    case 'handshake'://握手更新token
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        $token->update($_POST['token']);
        $return->retMsg('success');
        break;
    }
    case 'logout'://退出登录
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        $token->del($_POST['token']);
        $return->retMsg('success');
        break;
    }
    case 'version-check'://检查当前客户端是否为最新版本
    {
        $sql = 'SELECT version FROM main_apps WHERE app_id = ?';
        $params = array(1 => $_POST['app_id']);
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', array('version' => $mysql->fetchLine('version')));
        break;
    }

    case 'flow-update'://轮询更新流量信息
    {
        if (!$token->judge($_POST['token'])) $return->retMsg('tokenFailed');
        if (isEmpty($_POST['flow']) || isEmpty($_POST['vmess_id'])) $return->retMsg('emptyParam');
        $flow = round($_POST['flow'] / 1073741824, 2);
        $uid = $token->fetchKey($_POST['token'], 'uid');
        $sql = 'SELECT plan_id FROM main_users WHERE uid = ?';
        $mysql->bind_query($sql, $uid);
        $user_plan_id = $mysql->fetchLine('plan_id');
        $sql = 'SELECT flow, lim_flow FROM main_user_plan WHERE id = ?';
        $mysql->bind_query($sql, $user_plan_id);
        if ($mysql->fetchLine('flow') + $flow >= $mysql->fetchLine('lim_flow')) $new_flow = $mysql->fetchLine('lim_flow');
        else $new_flow = $mysql->fetchLine('flow') + $flow;
        $mysql->update('main_user_plan', 'flow', $new_flow, 'id', $user_plan_id);
        $mysql->change('main_vmess', 'flow', '+', $_POST['flow'], 'id', $_POST['vmess_id']);
        $sql = 'SELECT vmess_group FROM main_vmess WHERE id = ?';
        $mysql->bind_query($sql, $_POST['vmess_id']);
        $mysql->change('main_vmess_group', 'flow', '+', $_POST['flow'], $new_flow, 'id', $mysql->fetchLine('vmess_group'));
        $return->retMsg('success');
    }

    default:
    {
        $return->retMsg('dbgMsg');
    }
}