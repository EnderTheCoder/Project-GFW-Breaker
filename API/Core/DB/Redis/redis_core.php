<?php

class redis_core
{
    protected $conn;

    function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        if ($this->conn) return $this->conn;
        $this->conn = new Redis();
        $this->conn->connect('127.0.0.1', 6379);
//      $this->conn->auth('mypassword');//密码
        return $this->conn;
    }

    public function queue_create($queue_name)
    {
        $queue = array();
        $queue['length'] = 0;
        $queue['front'] = 0;
        $queue['tail'] = -1;
        $queue = json_encode($queue);
        $conn = $this->connect();
        $conn->set('redis-queue-' . $queue_name, $queue);
    }

    public function queue_insert($queue_name, $value)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        if (!$queue) return false;
        $queue = json_decode($queue, true);
        $queue['length']++;
        $queue['tail']++;
        $queue[$queue['tail']] = $value;
        $queue = json_encode($queue);
        $conn->set('redis-queue-' . $queue_name, $queue);
        return true;
    }

    public function queue_front($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        if (!$queue) return null;
        $queue = json_decode($queue, true);
        return $queue[$queue['front']];
    }

    public function queue_tail($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        if (!$queue) return null;
        $queue = json_decode($queue, true);
        return $queue[$queue['tail']];
    }

    public function queue_pop($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        if (!$queue) return false;
        $queue = json_decode($queue, true);
        if ($queue['length'] == 0) return false;
        $queue['length']--;
        unset($queue[$queue['front']]);
        $queue['front']++;
        $queue = json_encode($queue);
        $conn->set('redis-queue-' . $queue_name, $queue);
        return true;
    }

    public function queue_length($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        $queue = json_decode($queue, true);
        return $queue['length'];
    }

    public function queue_check($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('redis-queue-' . $queue_name);
        if (!$queue) return false;
        else return true;
    }

    public function set($key, $value)
    {
        $conn = $this->connect();
        $conn->set($key, $value);
    }

    public function get($key)
    {
        $conn = $this->connect();
        return $conn->get($key);
    }
}