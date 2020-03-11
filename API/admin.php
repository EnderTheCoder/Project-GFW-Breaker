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
    case 'login-check':
        $result = array('is_login' => false);
        if (isEmpty($_SESSION['admin_session']) || $_SESSION['admin_session']['ip_addr'] != getIP() || $_SESSION['admin_session']['user_agent'] != $_SERVER['HTTP_USER_AGENT'])
            $return->retMsg('success', $result);
        if (!adminStateCheck()) $return->retMsg('success', $result);
        $result['is_login'] = true;
        $return->retMsg('success', $result);
        break;
    case 'access-log-checkout':

}