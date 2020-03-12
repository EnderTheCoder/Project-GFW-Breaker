<?php

class sign_core
{
    protected $params;//去除原数据的签名后得到
    protected $sign;//原数据中的签名
    protected $key;//从数据库中取出的appkey
    protected $result;
    protected $str;
    protected $device;

    //使用appid从数据库中取出appkey
    private function getKey($app_id)
    {
        $mysql = new mysql_core();
        $sql = 'SELECT app_key, state, name FROM main_apps WHERE app_id = ?';
        $params = array();
        $params[1] = $app_id;
        $result = $mysql->bind_query($sql, $params);
        $this->key = $result[0][0];
        $_SESSION['device'] = $result[0]['name'];
        $this->device = $mysql->fetchLine('name');
        if ($result[0][1] != 1) $this->result = 'apiClosed';
    }

    //根据数据，生成签名
    private function spawnSign()
    {
        $this->str = '';
        foreach ($this->params as $key => $value) {
            if ($value !== '') {
                $this->str .= $key . $value;
            }
        }
        $this->str .= $this->key;
        return strtoupper(md5($this->str));
    }

    //初始化传入数据
    public function initParams($origin)
    {
        if (isEmpty($origin['sign']) ||
            isEmpty($origin['app_id']) ||
            isEmpty($origin['timestamp'])
        ) {
            $this->result = 'emptyParam';
            return;
        }
        if (time() - $origin['timestamp'] > MAX_SIGN_LIVE) {
            $this->result = 'signOvertime';
            return;
        }
        $this->sign = $origin['sign'];
        unset($origin['sign']);
        ksort($origin);
        $this->params = $origin;
        $this->getKey($origin['app_id']);
        if ($this->result == null) $this->result = true;
    }

    //获取已经计算完成的签名
    public function getSign()
    {
        return $this->sign;
    }

    //调试用，获取md5之前被拼接完成的字符串
    public function getStr()
    {
        return $this->str;
    }

    //获取原传入数组去掉sign元素后的结果
    public function getParams()
    {
        return $this->params;
    }

    public function getDevice()
    {
        return $this->device;
    }

    //比对传入签名判断是否正确
    public function checkSign()
    {
        if ($this->result === true)
            return ($this->spawnSign() === $this->sign) ? true : 'signErr';
        else return $this->result;
    }
}