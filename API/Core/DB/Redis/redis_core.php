<?php

class redis_core
{
    protected $conn;
    //初始化连接
    function __construct()
    {
        $this->connect();
    }
    //连接redis
    private function connect()
    {
        if ($this->conn) return $this->conn;
        $this->conn = new Redis();
        $this->conn->connect('127.0.0.1', 6379);
//      $this->conn->auth('mypassword');//密码
        return $this->conn;
    }
    //创建队列
    public function queue_create($queue_name)
    {
        $queue = array();
        $queue['length'] = 0;
        $queue['front'] = 0;
        $queue['tail'] = -1;
        $queue = json_encode($queue);
        $conn = $this->connect();
        $conn->set('__queue-' . $queue_name, $queue);
    }
    //插入队尾
    public function queue_insert($queue_name, $value)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        if (!$queue) return false;
        $queue = json_decode($queue, true);
        $queue['length']++;
        $queue['tail']++;
        $queue[$queue['tail']] = $value;
        $queue = json_encode($queue);
        $conn->set('__queue-' . $queue_name, $queue);
        return true;
    }
    //返回队头元素
    public function queue_front($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        if (!$queue) return null;
        $queue = json_decode($queue, true);
        return $queue[$queue['front']];
    }
    //返回队尾元素
    public function queue_tail($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        if (!$queue) return null;
        $queue = json_decode($queue, true);
        return $queue[$queue['tail']];
    }
    //队头元素出队
    public function queue_pop($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        if (!$queue) return false;
        $queue = json_decode($queue, true);
        if ($queue['length'] == 0) return false;
        $queue['length']--;
        unset($queue[$queue['front']]);
        $queue['front']++;
        $queue = json_encode($queue);
        $conn->set('__queue-' . $queue_name, $queue);
        return true;
    }
    //返回队长
    public function queue_length($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        $queue = json_decode($queue, true);
        return $queue['length'];
    }
    //检查是否存在队
    public function queue_check($queue_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__queue-' . $queue_name);
        if (!$queue) return false;
        else return true;
    }
    //销毁队
    public function queue_destroy($queue_name)
    {
        $conn = $this->connect();
        $conn->set('__queue-' . $queue_name, null);
    }
    //创建栈
    public function stack_create($stack_name)
    {
        $stack = array();
        $stack['size'] = 0;
        $stack['top'] = -1;
        $stack = json_encode($stack);
        $conn = $this->connect();
        $conn->set('__stack-' . $stack_name, $stack);
    }
    //入栈
    public function stack_push($stack_name, $value)
    {
        $conn = $this->connect();
        $stack = $conn->get('__stack-' . $stack_name);
        $stack = json_decode($stack, true);
        $stack['size']++;
        $stack[$stack['size']] = $value;
        $conn->set('__stack-' . $stack_name, $stack);
    }
    //出栈
    public function stack_pop($stack_name)
    {
        $conn = $this->connect();
        $stack = $conn->get('__stack-' . $stack_name);
        $stack = json_decode($stack, true);
        if ($stack['size'] >= 0) {
            unset($stack[$stack['size']]);
            $stack['size']--;
        }
        $conn->set('__stack-' . $stack_name, $stack);
    }
    //返回栈顶元素
    public function stack_top($stack_name)
    {
        $conn = $this->connect();
        $stack = $conn->get('__stack-' . $stack_name);
        $stack = json_decode($stack, true);
        return $stack[$stack['size']];
    }
    //检查是否存在栈
    public function stack_check($stack_name)
    {
        $conn = $this->connect();
        $queue = $conn->get('__stack-' . $stack_name);
        if (!$queue) return false;
        else return true;
    }
    //销毁栈
    public function stack_destroy($stack_name)
    {
        $conn = $this->connect();
        $conn->set('__stack-' . $stack_name, null);
    }
    //设定值
    public function set($key, $value)
    {
        $conn = $this->connect();
        $conn->set($key, $value);
    }
    //获取值
    public function get($key)
    {
        $conn = $this->connect();
        return $conn->get($key);
    }
}