<?php

class return_core
{
    protected $code = array(
        'success' => array(
            'code' => 100,
            'msg' => '请求成功',
        ),
        'jump' => array(
            'code' => 101,
            'msg' => '请求成功，即将跳转',
            'location' => URL,
        ),
        'signErr' => array(
            'code' => 201,
            'msg' => '不错的尝试'
        ),
        'dbErr' => array(
            'code' => 202,
            'msg' => '数据库错误',
        ),
        'emptyParam' => array(
            'code' => 203,
            'msg' => '参数不完整',
        ),
        'stateUnavailable' => array(
            'code' => 204,
            'msg' => '短信请求次数达到上限！',
        ),
        'emailMaxLimReached' => array(
            'code' => 205,
            'msg' => '邮箱请求次数达到上线！',
        ),
        'signOvertime' => array(
            'code' => 206,
            'msg' => '签名已经失效'
        ),
        'passErr' => array(
            'code' => 207,
            'msg' => '认证未通过',
        ),
        'unverifiedEmail' => array(
            'code' => 208,
            'msg' => '该邮箱未经认证',
        ),
        'emailServerErr' => array(
            'code' => 209,
            'msg' => '邮件服务器发生错误或邮件地址错误'
        ),
        'smsServerErr' => array(
            'code' => 210,
            'msg' => '短信服务发生错误'
        ),
        'captchaErr' => array(
            'code' => 211,
            'msg' => '验证码错误'
        ),
        'duplicateVal' => array(
            'code' => 212,
            'msg' => '重复的值',
        ),
        'formatErr' => array(
            'code' => 213,
            'msg' => '格式错误'
        ),
        'requestTooFast' => array(
            'code' => 214,
            'msg' => '请求速度过快',
        ),
        'entryErr' => array(
            'code' => 215,
            'msg' => '登入点错误',
        ),
        'paramErr' => array(
            'code' => 216,
            'msg' => '参数格式错误'
        ),
        'noResult' => array(
            'code' => 217,
            'msg' => '结果不存在',
        ),


        'dbgMsg' => array(
            'code' => 300,
            'msg' => '预留调试代码',
        ),
    );

    protected $retVal;

    public function setType($type)
    {
        $this->retVal = $this->code[$type];
    }

    public function setMsg($msg)
    {
        $this->retVal['msg'] = $msg;
    }

    public function setVal($key, $value)
    {
        $this->retVal[$key] = $value;
    }

    public function attachParams($params)
    {
        $this->retVal = array_merge($this->retVal, $params);
    }

    public function run()
    {
        $this->jsonReturn($this->retVal);
    }

    public function retMsg($type, $result = null, $msg = null)
    {
        if (isEmpty($this->code[$type])) $ret = $this->code['dbgMsg'];
        else $ret = $this->code[$type];
        if ($result)
            $ret = array_merge($ret, $result);
        if ($msg) $ret['msg'] = $msg;
        $this->jsonReturn($ret);
    }

    private function jsonReturn($res)
    {
        echo json_encode($res);
        exit;
    }
}