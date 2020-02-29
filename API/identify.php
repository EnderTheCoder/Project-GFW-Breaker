<?php

header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Methods:POST');
header(('Access-Control-Allow-Credentials:true'));
header('Content-Type:application/json; charset=utf-8');
require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
require 'Lib/LibSMTP.php';
require 'Core/custom_functions.php';
require 'Core/Security/token_core.php';
session_start();
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
$sign->initParams($_POST);
if ($sign->checkSign() !== true) {
    $return->retMsg($sign->checkSign());
}
if (isEmpty($_POST['type']))
    $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'login':
        if (isEmpty($_POST['id']) || isEmpty($_POST['password']))
            $return->retMsg('emptyParam');
        $sql = 'SELECT *  FROM main_users';
        if (strstr($_POST['id'], '@') !== false) $sql .= ' WHERE email = ?';
        else $sql .= ' WHERE username = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
        if (!$result[0]) $return->retMsg('passErr');
        if ($result[0]['state'] != null) $return->retMsg('stateUnavailable', $result[0]['state']);
        if ($result[0]['password'] != md5($_POST['password'] . PASSWORD_SALT))
            $return->retMsg('passErr');
        $token->setToken($result[0]['uid'], $result[0]['username']);
        $return->retMsg('success');
        break;
    case 'register':
        if (isEmpty($_POST['username']) || isEmpty($_POST['password']) || isEmpty($_POST['email']))
            $return->retMsg('emptyParam');
        $sql = 'INSERT INTO main_users(username, password, reg_ip, email, state, reg_time) VALUES (?, ?, ?, ?, ?, ?)';
        $params = array(
            1 => $_POST['username'],
            2 => md5($_POST['password'] . PASSWORD_SALT),
            3 => $_SERVER['REMOTE_ADDR'],
            4 => $_POST['email'],
            5 => '您的邮箱尚未验证,请前往邮箱查收验证链接',
            6 => time()
        );
        $mysql->bind_query($sql, $params);
        $email_token = md5($_POST['username'] . rand() . time());
        $sql = 'INSERT INTO main_email_token (uid, token_value, timestamp, action) VALUES (?, ?, ?, ?)';
        $params = array(
            1 => $mysql->getId(),
            2 => $email_token,
            3 => time(),
            4 => 'register'
        );
        $mysql->bind_query($sql, $params);
        $url = URL . '/email.html?email_token=' . $email_token;
        $msg = '<a href="' . URL . '">[GFW-BREAKER]</a>尊敬的新用户' . $_POST['username'] . '您好,感谢您在本站的注册,请打开下方链接完成邮箱验证.<br><a href="' . $url . '">' . $url . '</a>';
        sendMail($_POST['email'], '完成您在[GFW-BREAKER]的注册', $msg);
        if ($mysql->isError()) $return->retMsg('dbErr', $mysql->getError());
//        if ($_POST['do_login']) $token->setToken($mysql->getId(), $_POST['username']);
        $return->retMsg('success');
        break;
    case 'emailV':
        if (isEmpty($_POST['email_token'])) $return->retMsg('emptyParam');
        $sql = 'SELECT * FROM main_email_token WHERE token_value = ?';
        $params = array(1 => $_POST['email_token']);
        $result = $mysql->bind_query($sql, $params);
        if (!$result[0]['token_value']) $return->retMsg('passErr');
        $sql = 'UPDATE main_users SET state = ? WHERE uid = ?';
        $params = array(
            1 => null,
            2 => $result[0]['uid']
        );
        $mysql->bind_query($sql, $params);
        $sql = 'DELETE FROM main_email_token WHERE uid = ?';
        $params = array(1 => $result[0]['uid']);
        $mysql->bind_query($sql, $params);
        $return->setType('jump');
        $return->setVal('location', URL);
        $return->run();
        break;
    case  'dupCheck':
        if (isEmpty($_POST['key']) || isEmpty($_POST['value'])) $return->retMsg('emptyParam');
        $sql = 'SELECT ? FROM main_users WHERE ? = ?';
        $params = array(1 => $_POST['key'], 2 => $_POST['key'], 3 => $_POST['value']);
        $result = $mysql->bind_query($sql, $params);
        if ($result[0][$_POST['key']]) $return->retMsg('dupVal');
        $return->retMsg('success');
        break;
    default:
        $return->retMsg('paramErr');
}