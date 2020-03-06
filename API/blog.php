<?php
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Methods:POST');
header(('Access-Control-Allow-Credentials:true'));
header('Content-Type:application/json; charset=utf-8');
require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
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
if (isEmpty($_POST['type']))
    $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'all':
        $sql = 'SELECT id, title, summary FROM main_blog';
        $result = $mysql->bind_query($sql);
        if ($result)
            $result['row'] = count($result);
        else $result['row'] = 0;
        $return->retMsg('success', $result);
        break;
    case 'single':
        $sql = 'SELECT id, title, content, author, timestamp FROM main_blog WHERE id = ?';
        $params = array(
            1 => $_POST['id'],
        );
        $result = $mysql->bind_query($sql, $params);
        $return->retMsg('success', $result);
        break;
    default:
        $return->retMsg('paramErr');
}