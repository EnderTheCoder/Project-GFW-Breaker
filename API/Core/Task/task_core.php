<?php

class task_core
{
    const FILE_PREFIX = './Task/';

    public function runningCheck($id)
    {
        $sql = 'SELECT pid FROM main_tasks WHERE id = ?';
        $params = array(1 => $id);
        $mysql = new mysql_core();
        $result = $mysql->bind_query($sql ,$params);
        exec("ps " . $result[0]['pid'], $pState);
        return ((count($pState) >= 2) && !empty($pid));
    }

    public function activeTask($id)
    {
        $sql = 'SELECT file_name FROM main_tasks WHERE id = ?';
        $params = array(1 => $id);
        $mysql = new mysql_core();
        $result = $mysql->bind_query($sql ,$params);
        $command = 'php ' . self::FILE_PREFIX . $result[0]['file_name'];
        $pid = exec("nohup $command > /dev/null 2>&1 & echo $!");
        $sql = 'UPDATE main_tasks SET pid = ? WHERE id = ?';
        $params = array(1 => $pid, 2 => $id);
        $mysql->bind_query($sql, $params);
    }

}