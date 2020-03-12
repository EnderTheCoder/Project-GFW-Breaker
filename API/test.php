<html lang="zh">
<form action="#" method="post" style="width: 400px; margin-left: auto; margin-right: auto">
    <?php
    if (!$_POST['name'])
        echo '
        <h1 style="text-align: center">注册</h1>
        <label>
        姓名
        <input type="text" id="name" name="name" required>
        </label>
                <br>

        <label>
        性别
        <input type="radio" name="sex" value="男" checked>
        男
        <input type="radio" name="sex" value="女" >
        女
        </label>
        <br>
        <label>
        密码
        <input type="password" id="password" name="password" required>
        </label>
                <br>

        <label>
        重复密码
        <input type="password" id="re-password" name="re-password" required>
        </label>
                <br>

        <label>
        手机号码
        <input type="text" id="phone" name="phone" required>        
        </label>
        <br>
        <button type="submit">提交</button>
        ';
    else {
        if ($_POST['password'] != $_POST['re-password']) die('<script>alert("两次密码输入不正确！");window.location.href="test.php";</script>');
        echo "
        <h1>注册信息</h1>
        <p>姓名:{$_POST['name']}</p>
        <p>性别:{$_POST['sex']}</p>
        <p>密码:{$_POST['password']}</p>
        <p>重复密码:{$_POST['re-password']}</p>
        <p>电话号码:{$_POST['phone']}</p>
        ";
    }
    ?>
</form>
</html>