<?php
function isEmpty($str)
{
    return !(!empty($str) && isset($str));
}

function captchaCheck()
{
    $compare = $_SESSION['captcha'];
    $_SESSION['captcha'] = rand();
    return $_POST['captcha'] == $compare;
}

function sendMail($remoteEmail, $title, $body)
{
    $mail = new LibSMTP();
    $mail->setServer(SMTP_SERVER_ADDR, SMTP_USERNAME, SMTP_PASSWORD, SMTP_SERVER_PORT, true); //参数1（qq邮箱使用smtp.qq.com，qq企业邮箱使用smtp.exmail.qq.com），参数2（邮箱登陆账号），参数3（邮箱登陆密码，也有可能是独立密码，就是开启pop3/smtp时的授权码），参数4（默认25，腾云服务器屏蔽25端口，所以用的465），参数5（是否开启ssl，用465就得开启）//$mail->setServer("XXXXX", "joffe@XXXXX", "XXXXX", 465, true);
    $mail->setFrom(SMTP_USER_EMAIL); //发送者邮箱
    $mail->setReceiver($remoteEmail); //接收者邮箱
//$mail->addAttachment(""); //Attachment 附件，不用可注释
    $mail->setMail($title, $body); //标题和内容
    return $mail->send();//可以var_dump一下，发送成功会返回true，失败false
}

function getIP()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realIP = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realIP = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realIP = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realIP = getenv("HTTP_CLIENT_IP");
        } else {
            $realIP = getenv("REMOTE_ADDR");
        }
    }
    return $realIP;
}

function adminStateCheck()
{
    return !isEmpty($_SESSION['admin_session']) &&
        $_SESSION['admin_session']['ip_addr'] == getIP() &&
        $_SESSION['admin_session']['user_agent'] == $_SERVER['HTTP_USER_AGENT'];
}

function countX($array)
{
    if (!$array) return 0;
    else return count($array);
}

function getSetting($key)
{
    $mysql = new mysql_core();
    $sql = 'SELECT `value` FROM main_site_settings WHERE `key` = ?';
    $mysql->bind_query($sql, $key);
    return $mysql->fetchLine('value');
}
