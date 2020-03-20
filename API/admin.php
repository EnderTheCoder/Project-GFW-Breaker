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
if ($_POST['app_id'] != 3) $return->retMsg('signErr');
$sign->initParams($_POST);
if ($sign->checkSign() !== true) $return->retMsg($sign->checkSign());
if (isEmpty($_POST['type'])) $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'login':
    {
        if (isEmpty($_POST['id']) || isEmpty($_POST['password']) || isEmpty($_POST['captcha'])) $return->retMsg('emptyParam');
        if (!captchaCheck()) $return->retMsg('captchaErr');
        $sql = 'SELECT id, username, password FROM main_admin WHERE username = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
        if ($result[0]['username'] !== $_POST['id'] || $result[0]['password'] !== md5($_POST['password'] . ADMIN_SALT)) $return->retMsg('passErr');
        $_SESSION['admin_session']['id'] = $result[0]['id'];
        $_SESSION['admin_session']['username'] = $result[0]['username'];
        $_SESSION['admin_session']['ip_addr'] = getIP();
        $_SESSION['admin_session']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $return->retMsg('success');
        break;
    }
    case 'login-check':
    {
        $result = array('is_login' => false);
        if (!adminStateCheck()) $return->retMsg('success', $result);
        $result['is_login'] = true;
        $return->retMsg('success', $result);
        break;
    }
    case 'access-log-checkout':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT * FROM main_access_log ORDER BY id DESC LIMIT 500';
        $result = $mysql->bind_query($sql);
        $return->retMsg('success', $result);
        break;
    }
    case 'add-vmess-group':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['name']) || isEmpty($_POST['speed_rank']) ||
            isEmpty($_POST['flow_limit']) || isEmpty($_POST['price']) ||
            isEmpty($_POST['cnt']) || isEmpty(['rows'])) $return->retMsg('emptyParam');
        $sql = 'INSERT INTO main_vmess_group (name, speed_rank, flow_limit) VALUES (?, ?, ?)';
        $params = array(0, $_POST['name'], $_POST['speed_rank'], $_POST['flow_limit']);
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
        $group_id = $mysql->getId();
        $sql = 'INSERT INTO main_vmess(area, config, speed_rank, vmess_group) VALUES (?, ?, ?, ?)';
        for ($i = 0; $i < $_POST['cnt']; $i++) {
            $params = array(
                1 => $_POST['rows'][$i]['area'],
                2 => $_POST['rows'][$i]['config'],
                3 => $_POST['rows'][$i]['speed_rank'],
                4 => $_POST['rows'][$i]['vmess_group'],
            );
            $mysql->bind_query($sql, $params);
        }
        $return->retMsg('success');
        break;
    }
    case 'get-vmess-group-all':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT * FROM main_vmess_group';
        $result = $mysql->bind_query($sql);
        $result['rows'] = count($result);
        $return->retMsg('success', $result);
        break;
    }
    case 'edit-vmess-group':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['id']) || isEmpty($_POST['data'])) $return->retMsg('emptyParam');
        $sql = 'UPDATE main_vmess_group SET name = ?, speed_rank = ?, flow = ?, flow_limit = ? WHERE id = ?';
        $params = array(
            1 => $_POST['data']['name'],
            2 => $_POST['data']['speed_rank'],
            3 => $_POST['data']['flow'],
            4 => $_POST['data']['flow_limit'],
            5 => $_POST['id'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', $result);
        break;
    }
    case 'get-plan':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT id, `name`, price, flow_limit, buy_cnt FROM main_plan';
        if (!isEmpty($_POST['id'])) {
            $sql = 'SELECT id, `name`, price, flow_limit, buy_cnt, son FROM main_plan WHERE id = ?';
            $params = array(1 => $_POST['id']);
            $result = $mysql->bind_query($sql, $params);
            $result[0]['son'] = json_decode($result[0]['son'], true);
            $sql = 'SELECT id, name, speed_rank, flow, flow_limit FROM main_vmess_group WHERE id = ?';
            $result[0]['son']['row'] = countX($result[0]['son']);
            for ($i = 0; $i < $result[0]['son']['row']; $i++) {
                $params = array(1 => $result[0]['son'][$i]);
                $row = $mysql->bind_query($sql, $params);
                $result[0]['son'][$i] = $row[0];
            }
            $return->retMsg('success', $result[0]);
        } else {
            $mysql->bind_query($sql);
            $return->retMsg('success', $mysql->fetch(true));
        }
        break;
    }

}