<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../Core/DB/MySQL/mysql_core.php';
require __DIR__ . '/../Core/DB/Redis/redis_core.php';
require __DIR__ . '/../Lib/LibSMTP.php';
require __DIR__ . '/../Core/custom_functions.php';
echo "Email Process Start\n";
$redis = new redis_core();
$task_cnt = 0;
if (!$redis->queue_check('email')) {
    echo "Redis queue not found. Formed New queue.\n";
    $redis->queue_create('email');
}
while (1) {
    $count = $redis->queue_length('email');
    for ($i = 1; $i <= $count; $i++) {
        $task_cnt++;
        $email = $redis->queue_front('email');
        sendMail($email['email'], $email['title'], $email['content']);
        echo "Send email " . $email['title'] . " to " . $email['email'] . ". Count: " . $task_cnt . ". " . ($count - $i) . " left.\n";
        $redis->queue_pop('email');
    }
    if ($count)
        echo $count . " emails were successfully sent! Sleep 3 seconds to continue.\n";
    sleep(3);
}
echo "Email Process End\n";