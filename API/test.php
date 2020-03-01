<?php
require './config.php';
require './Core/DB/MySQL/mysql_core.php';
require './Core/DB/Redis/redis_core.php';
require './Lib/LibSMTP.php';
require './Core/Task/task_core.php';
$redis = new redis_core();
$task = new task_core();
//$task->start_(1, __DIR__);
$task->start_($task->create_('email', 'email.php', '发送邮件进程'), __DIR__);
echo $task->check_(1);
//exec("ps " . 3953, $pState);
//var_dump(count($pState) >= 2);