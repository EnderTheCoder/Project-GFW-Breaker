<?php

class task_core
{
    public function check_($id)
    {
        $sql = 'SELECT pid FROM main_tasks WHERE id = ?';
        $params = array(1 => $id);
        $mysql = new mysql_core();
        $result = $mysql->bind_query($sql, $params);
        exec("ps " . $result[0]['pid'], $pState);
        return ((count($pState) >= 2) && !empty($result[0]['pid']));
    }

    public function start_($id, $dir)
    {
        $sql = 'SELECT file_name FROM main_tasks WHERE id = ?';
        $params = array(1 => $id);
        $mysql = new mysql_core();
        $result = $mysql->bind_query($sql, $params);
        $command = 'php ' . $dir . '/Task/' . $result[0]['file_name'];
        $pid = exec("nohup $command > /dev/null 2>&1 & echo $!");
        $sql = 'UPDATE main_tasks SET pid = ? WHERE id = ?';
        $params = array(1 => $pid, 2 => $id);
        $mysql->bind_query($sql, $params);
    }

    public function create_($task_name, $file_name, $info)
    {
        $sql = 'INSERT INTO main_tasks (task_name, file_name, info) VALUES (?, ?, ?)';
        $params = array(1 => $task_name, 2 => $file_name, $info);
        $mysql = new mysql_core();
        $mysql->bind_query($sql, $params);
        return $mysql->getId();
    }

    public function stop_($id)
    {
        $sql = 'SELECT pid FROM main_tasks WHERE id = ?';
        $params = array(1 => $id);
        $mysql = new mysql_core();
        $result = $mysql->bind_query($sql, $params);
        exec("ps " . $result[0]['pid'], $pState);
        if ((count($pState) >= 2)) {
            exec("kill " . $result[0]['pid']);
        }
    }
}