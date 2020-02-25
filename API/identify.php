<?php

header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Methods:POST');
header(('Access-Control-Allow-Credentials:true'));
header('Content-Type:application/json; charset=utf-8');
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
require 'Lib/LibSMTP.php';
require 'Core/custom_functions.php';
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
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
        if (strstr($_POST['id'], '@') !== false) $sql .= 'WHERE user_email = ?';
        else $sql .= 'WHERE user_account = ?';
        $param = array(0=>$_POST['id']);
        $result = $mysql->bind_query($sql, $param);
        if (!$result) $return->retMsg('passErr');
        if ($result[0]['password'] != md5($_POST['password'] . PASSWORD_SALT))
            $return->retMsg('passErr');
        $return->retMsg('success');
    case 'register':
}