<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header(('Access-Control-Allow-Credentials:true'));
header('Content-Type:application/json; charset=utf-8');
require 'config.php';
require 'Core/DB/MySQL/mysql_core.php';
require 'Core/Security/sign_core.php';
require 'Core/Data/return_core.php';
require 'Core/custom_functions.php';
require 'Core/Security/token_core.php';
//require 'Core/DB/Redis/redis_core.php';
session_start();
$sign = new sign_core();
$mysql = new mysql_core();
$return = new return_core();
$token = new token_core();
//$redis = new redis_core();
$sign->initParams($_POST);
if ($sign->checkSign() !== true) $return->retMsg($sign->checkSign());
if (isEmpty($_POST['type']))
    $return->retMsg('emptyParam');
switch ($_POST['type']) {
    case 'all':
    {
        $sql = 'SELECT id, title, summary FROM main_blog WHERE visible = true ORDER BY id DESC ';
        $result = $mysql->bind_query($sql);
        if ($result)
            $result['row'] = count($result);
        else $result['row'] = 0;
        $return->retMsg('success', $result);
        break;
    }
    case 'single':
    {
        $sql = 'SELECT id, title, content, author, timestamp FROM main_blog WHERE id = ?';
        $params = array(1 => $_POST['id']);
        $result = $mysql->bind_query($sql, $params);
        $sql = 'UPDATE main_blog SET view = view + 1 WHERE id = ?';
        $params = array(1 => $_POST['id']);
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', $result);
        break;
    }
    case 'publish-new':
    {
        if (isEmpty($_POST['blog_content']) || isEmpty($_POST['blog_title']) || isEmpty($_POST['blog_summary']) || isEmpty($_POST['blog_visibility'])) $return->retMsg('emptyParam');
//        if ($_FILES['blog_content']['error'] > 0) $return->retMsg('fileErr');
        if ($_POST['app_id'] != 3) $return->retMsg('signErr');
        if (!adminStateCheck()) $return->retMsg('passErr');
        $sql = 'INSERT INTO main_blog(title, summary, content, author, timestamp, visible) VALUES (?, ?, ?, ?, ?, ?)';
        $params = array(
            1 => $_POST['blog_title'],
            2 => $_POST['blog_summary'],
            3 => $_POST['blog_content'],
            4 => $_SESSION['admin_session']['username'],
            5 => time(),
            6 => $_POST['blog_visibility'],
        );
        $mysql->bind_query($sql, $params);
        $return->retMsg('success', $mysql->getId());
    }
    default:
        $return->retMsg('paramErr');
}