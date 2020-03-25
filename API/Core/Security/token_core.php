<?php
//php class file redis_core.php required

class token_core
{
    protected $token;
//
//    function __construct()
//    {
//        $this->token = $_SESSION['token'];
//    }
//
//    public function getRedisToken($uid, $device)
//    {
//        $redis = new redis_core();
//        $this->token = json_decode($redis->get('__token-' . $device . '-' . $uid), true);
//    }
//
//    public function getToken()
//    {
//        return $this->token;
//    }
//
//    public function getVal($key)
//    {
//        return $this->token[$key];
//    }
//
//    public function judgeToken($uid = false, $device = 'web', $tokenVal = false)
//    {
//        if ($tokenVal && $device != 'web') {
//            $this->getRedisToken($uid, $device);
//            return $tokenVal == $this->token['token_value'] && ((time() - $this->token['timestamp']) < MAX_TOKEN_LIVE);
//        }
//        if (isEmpty($this->token)) return false;
//        return ((time() - $this->token['timestamp']) < MAX_TOKEN_LIVE);
//    }
//
//    public function updateToken()
//    {
//        $this->token['timestamp'] = time();
//        $this->token['token_value'] = md5($this->token['token_value'] . time() . TOKEN_SALT);
//        $_SESSION['token'] = $this->token;
//    }
//
//    public function setToken($uid, $username, $device = 'web')
//    {
//        $this->token['device'] = $device;
//        $this->token['uid'] = $uid;
//        $this->token['username'] = $username;
//        $this->token['timestamp'] = time();
//        $this->token['token_value'] = md5($username . time() . TOKEN_SALT . $uid . $device);
//        $json = json_encode($this->token);
//        if ($device == 'web')
//            $_SESSION['token'] = $this->token;
//        else {
//            $redis = new redis_core();
//            $redis->set('__token-' . $device . '-' . $uid, $json);
//        }
//    }

    private function purge()//只有app使用
    {
        $sql = 'DELETE FROM main_token WHERE timestamp < ?';
        $params = array(1 => time() - MAX_TOKEN_LIVE);
        $mysql = new mysql_core();
        $mysql->bind_query($sql, $params);
    }

    public function del($token_value)
    {
        $mysql = new mysql_core();
        $sql = 'DELETE FROM main_token WHERE token_value = ?';
        $params = array(1 => $token_value);
        $mysql->bind_query($sql, $params);
    }

    public function update($token_value)
    {
        $mysql = new mysql_core();
        $sql = 'UPDATE main_token SET timestamp = ? WHERE token_value = ?';
        $params = array(
            1 => time(),
            2 => $token_value
        );
        $mysql->bind_query($sql, $params);
    }

    public function session($uid, $username)//只有web使用
    {
        $_SESSION['uid'] = $uid;
        $_SESSION['username'] = $username;
        $_SESSION['timestamp'] = time();
    }

    public function sessionJudge()
    {
        return $_SESSION['uid'] && $_SESSION['username'] && ($_SESSION['timestamp'] + MAX_SESSION_LIVE > time());
    }

    public function sessionUpdate()
    {
        $_SESSION['timestamp'] = time();
    }

    public function sessionDel()
    {
        $_SESSION = null;
    }

    public function getByValue($token_value)//只有app使用
    {
        $mysql = new mysql_core();
        $sql = 'SELECT * FROM main_token WHERE token_value = ?';
        $result = $mysql->bind_query($sql, array(1 => $token_value));
        $this->token = $result[0];
        $this->purge();
        return $this->token;
    }

    public function getByUID($uid)//只有app使用
    {
        $mysql = new mysql_core();
        $sql = 'SELECT * FROM main_token WHERE uid = ?';
        $result = $mysql->bind_query($sql, array(1 => $uid));
        $this->token = $result[0];
        $this->purge();
        return $this->token;
    }

    public function fetchKey($token, $key)
    {
        $tokens = $this->getByValue($token);
        return $tokens[$key];
    }

    public function judge($value)//只有app使用
    {
        $this->getByValue($value);
        if (!$this->token || $this->token['timestamp'] < time() - MAX_TOKEN_LIVE) return false;
        return true;
    }

    public function set($uid, $device, $ip)//只有app使用
    {
        $this->purge();
        $mysql = new mysql_core();
        $sql = 'SELECT username, multi_device FROM main_users WHERE uid = ?';
        $params = array(1 => $uid);
        $mysql->bind_query($sql, $params);
        if (!$mysql->bind_query($sql, $params)) return false;
        $devices = countX($this->getByUID($uid));
        if ($mysql->fetchLine('multi_device') < $devices) return false;
        $sql = 'INSERT INTO main_token (uid, timestamp, token_value, ip, device) VALUES (?, ?, ?, ?, ?)';
        $params = array(
            1 => $uid,
            2 => time(),
            3 => md5($uid . time() . $sql),
            4 => $ip,
            5 => $device
        );
        $mysql->bind_query($sql, $params);
        $this->getByUID($uid);
        return $this->token;
    }
}