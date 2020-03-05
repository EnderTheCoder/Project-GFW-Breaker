<?php
//php class file redis_core.php required

class token_core
{
    protected $token;

    function __construct()
    {
        $this->token = $_SESSION['token'];
    }

    public function getRedisToken($uid, $device)
    {
        $redis = new redis_core();
        $this->token = json_decode($redis->get('__token-' . $device . '-' . $uid), true);
    }

    public function judgeToken($uid = false, $device = false, $tokenVal = false)
    {
        if ($tokenVal && $device != 'web') {
            $this->getRedisToken($uid, $device);
            return $tokenVal == $this->token['token_value'] && ((time() - $this->token['timestamp']) < MAX_TOKEN_LIVE);
        }
        if (isEmpty($this->token)) return false;
        return ((time() - $this->token['timestamp']) < MAX_TOKEN_LIVE);
    }

    public function updateToken()
    {
        $this->token['timestamp'] = time();
        $this->token['token_value'] = md5($this->token['token_value'] . time() . TOKEN_SALT);
        $_SESSION['token'] = $this->token;
    }

    public function setToken($uid, $username, $device = 'web')
    {
        $this->token['device'] = $device;
        $this->token['uid'] = $uid;
        $this->token['username'] = $username;
        $this->token['timestamp'] = time();
        $this->token['token_value'] = md5($username . time() . TOKEN_SALT . $uid . $device);
        $json = json_encode($this->token);
        if ($device == 'web')
            $_SESSION['token'] = $this->token;
        else {
            $redis = new redis_core();
            $redis->set('__token-' . $device . '-' . $uid, $json);
        }

    }
}