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
        $token->sessionDel();
        $return->retMsg('success');
    }
    case 'login-check':
    {
        $result = array('is_login' => false);
        if (!adminStateCheck()) $return->retMsg('success', $result);
        $result['is_login'] = true;
        $return->retMsg('success', $result);
    }
    case 'access-log-checkout':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT * FROM main_access_log ORDER BY id DESC LIMIT 500';
        $result = $mysql->bind_query($sql);
        $return->retMsg('success', $result);
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
    }
    case 'get-vmess-group':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT * FROM main_vmess_group';
        if (isEmpty($_POST['id'])) {
            $result = $mysql->bind_query($sql);
            $result['row'] = countX($result);
            $return->retMsg('success', $result);
        } else {
            $sql .= ' WHERE id = ?';
            $params = array(1 => $_POST['id']);
            $result = $mysql->bind_query($sql, $params);
            $return->retMsg('success', $result[0]);
        }
    }

    case 'edit-vmess-group':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['id']) || isEmpty($_POST['key']) || isEmpty($_POST['value'])) $return->retMsg('emptyParam');
        $sql = 'UPDATE main_vmess_group SET ? = ? WHERE id = ?';
        $params = array(
            1 => $_POST['key'],
            2 => $_POST['value'],
            3 => $_POST['id'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'delete-vmess-group':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['id'])) $return->retMsg('emptyParam');
        $sql = 'DELETE FROM main_vmess_group WHERE id = ?';
        $params = array(1 => $_POST['id']);
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'get-plan':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'SELECT id, `name`, price, flow_limit, buy_cnt FROM main_plan';
        if (!isEmpty($_POST['id'])) {
            $sql = 'SELECT id, `name`, price, flow_limit, buy_cnt, son, info FROM main_plan WHERE id = ?';
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
    }

    case 'add-plan':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['name']) || isEmpty($_POST['price']) || isEmpty($_POST['flow_limit']) || isEmpty($_POST['son']) || isEmpty($_POST['info'])) $return->retMsg('emptyParam');
        $sql = 'INSERT INTO main_plan (name, price, flow_limit, son, info) VALUES (?, ?, ?, ?, ?)';
        $params = array(
            1 => $_POST['name'],
            2 => $_POST['price'],
            3 => $_POST['flow_limit'],
            4 => $_POST['son'],
            5 => $_POST['info']
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'edit-plan':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['id']) || isEmpty($_POST['key']) || isEmpty($_POST['value'])) $return->retMsg('emptyParam');
        $sql = 'UPDATE main_plan SET ? = ? WHERE id = ?';
        $params = array(
            1 => $_POST['key'],
            2 => $_POST['value'],
            3 => $_POST['id'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'delete-plan':
    {
        if (!adminStateCheck()) $return->retMsg('passErr');
        if (isEmpty($_POST['id'])) $return->retMsg('emptyParam');
        $sql = 'DELETE FROM main_plan WHERE id = ?';
        $params = array(1 => $_POST['id']);
        $mysql->bind_query($sql, $params);
        $return->retMsg('success');
    }

    case 'get-access-log':
    {
        if (isEmpty($_POST['id'])) {
            $sql = 'SELECT id, ip_addr, is_login, timestamp, access_url FROM main_access_log WHERE is_login != 1 ORDER BY id DESC LIMIT 500';
            $mysql->bind_query($sql);
            $return->retMsg('success', $mysql->fetch(true));
        } else {
            $sql = 'SELECT * FROM main_access_log WHERE id = ?';
            $mysql->bind_query($sql, $_POST['id']);
            $return->retMsg('success', $mysql->fetchLine(null));
        }
    }

    default:
    {
        $return->retMsg('dbgMsg');
    }
}