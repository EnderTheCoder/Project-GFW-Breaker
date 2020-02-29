<?php


class token_core
{
    protected $token;

    function __construct()
    {
        $this->token = $_SESSION['token'];
    }

    public function judgeToken()
    {
        if (isEmpty($this->token)) return false;
        return !(time() - $this->token['timestamp'] > MAX_TOKEN_LIVE);
    }

    public function updateToken()
    {
        $this->token['timestamp'] = time();
        $this->token['token_value'] = md5(TOKEN_SALT . time() . TOKEN_SALT);
        $_SESSION['token'] = $this->token;
    }

    public function setToken($uid, $username)
    {
        $this->token['uid'] = $uid;
        $this->token['username'] = $username;
        $this->token['timestamp'] = time();
        $this->token['token_value'] = md5(TOKEN_SALT . time() . TOKEN_SALT);
        $_SESSION['token'] = $this->token;
    }
}