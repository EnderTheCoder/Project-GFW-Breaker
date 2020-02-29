<?php

class mysql_core
{
    private $con, $isError = false, $ErrorMsg;

    //在实例化对象时连接数据库
    public function __construct()
    {
        $this->connect();
    }

    //检测是否已经连接数据库。如果已经连接就返回连接，如果没有就进行连接。
    private function connect()
    {
        if ($this->con != null) return $this->con;
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $con = new pdo($dsn, DB_USERNAME, DB_PASSWORD);
        $this->con = $con;
        $con->query('set names utf8');
        return $this->con;
    }

    //改变使用的数据库
    public function changeDB($dbName)
    {
        $sql = 'use ' . $dbName;
        $conn = $this->connect();
        $conn->query($sql);
    }

    //执行不带有用户输入的sql语句
    public function query($sql)
    {
        $conn = $this->connect();
        $conn->query($sql);
        return true;
    }

    //使用绑定参数执行带有输入的sql语句
    public function bind_query($sql, $params = null)
    {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare($sql);
            for ($i = 1; $i <= count($params); $i++)
                $stmt->bindValue($i, $params[$i], PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $exception) {
            $this->isError = true;
            $this->ErrorMsg = '数据库查询错误,错误代码：' . $exception->getCode() . '错误信息：' . $exception->getMessage();
            return $this->ErrorMsg;
        }
    }

    //debug函数，判断是否发生错误
    public function isError()
    {
        return $this->isError;
    }

    //debug函数，获取错误信息
    public function getError()
    {
        return $this->ErrorMsg;
    }

    //获取上一次插入所产生的自增id
    public function getId()
    {
        $conn = $this->connect();
        return $conn->lastInsertId();
    }

    //关闭数据库连接
    public function close()
    {
        $this->con = null;
    }
}