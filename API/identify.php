<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Content-Type:application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/Core/DB/MySQL/mysql_core.php';
require __DIR__ . '/Core/Security/sign_core.php';
require __DIR__ . '/Core/Data/return_core.php';
require __DIR__ . '/Lib/LibSMTP.php';
require __DIR__ . '/Core/custom_functions.php';
require __DIR__ . '/Core/Security/token_core.php';
require __DIR__ . '/Core/DB/Redis/redis_core.php';
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
        if (isEmpty($_POST['id']) || isEmpty($_POST['password'])) $return->retMsg('emptyParam');
        $sql = 'SELECT *  FROM main_users';
        if (strstr($_POST['id'], '@') !== false) $sql .= ' WHERE email = ?';
        else $sql .= ' WHERE username = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
//        if (!$result[0]) $return->retMsg('passErr');
        if ($result[0]['state'] != null) $return->retMsg('stateUnavailable', $result[0]['state']);
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
        } else $mysql->bind_query($sql, $params);
        $token->session($result[0]['uid'], $result[0]['username']);
        $_SESSION['admin_session'] = null;
        $return->retMsg('success');
        break;
    case 'register':
        if (isEmpty($_POST['username']) || isEmpty($_POST['password']) || isEmpty($_POST['email']))
            $return->retMsg('emptyParam');
        $sql = 'SELECT username, email FROM main_users WHERE username = ? OR email = ?';
        $params = array(
            1 => $_POST['username'],
            2 => $_POST['email']
        );
        $result = $mysql->bind_query($sql, $params);
        if (count($result)) {
            $return->setType('dupVal');
            for ($i = 0; $i < count($result); $i++) {
                if ($result[$i]['username'] == $_POST['username']) {
                    $return->setVal('key', 'username');
                    $return->run();
                }
                if ($result[$i]['email'] == $_POST['email']) {
                    $return->setVal('key', 'email');
                    $return->run();
                }
            }
        }
        $_COOKIE = null;
        if ($result[0][$_POST['key']]) $return->retMsg('dupVal');
        $sql = 'INSERT INTO main_users(username, password, reg_ip, email, state, reg_time, money, multi_device) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $params = array(
            1 => $_POST['username'],
            2 => md5($_POST['password'] . PASSWORD_SALT),
            3 => $_SERVER['REMOTE_ADDR'],
            4 => $_POST['email'],
//          5 => '您的邮箱尚未验证,请前往邮箱查收验证链接',
            5 => null,
            6 => time(),
            7 => getSetting('default_money'),
            8 => getSetting('default_device_limit'),
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
//        sendMail($_POST['email'], '完成您在[GFW-BREAKER]的注册', $msg);
        $email_task = array(
            'email' => $_POST['email'],
            'title' => '完成您在[GFW-BREAKER]的注册',
            'content' => '<a href="' . URL . '">[GFW-BREAKER]</a>尊敬的新用户' . $_POST['username'] . '您好,感谢您在本站的注册,请打开下方链接完成邮箱验证.<br><a href="' . $url . '">' . $url . '</a>',
        );
        $redis->queue_insert('email', $email_task);
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