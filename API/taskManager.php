<?php
require './config.php';
require './Core/DB/MySQL/mysql_core.php';
require './Core/DB/Redis/redis_core.php';
require './Core/Task/task_core.php';
require './Core/custom_functions.php';

$task = new task_core();
$sign = new sign_core();

