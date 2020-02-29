const area = $("#nav-list");
$.ajax({
    url: 'API/loginCheck.php',
    type: "POST",
    dataType: 'json',
    async: false,
    timeout: 5000,
    success: function (result) {
        let json = eval(result);
        if (json['data']['isLogin']) area.append(
            '        <li class="layui-nav-item">\n' +
            '            <a href="">用户中心</a>\n' +
            '            <dl class="layui-nav-child">\n' +
            '                <dd><a href="">订阅计划</a></dd>\n' +
            '                <dd><a href="">用户账单</a></dd>\n' +
            '                <dd><a href="">用户设置</a></dd>\n' +
            '                <dd><a href="">退出登录</a></dd>\n' +
            '            </dl>\n' +
            '        </li>');
        else area.append('<li class="layui-nav-item"><a href="login.html">登录</a></li>\n' +
            '    <li class="layui-nav-item"><a href="register.html">注册</a></li>');
    },
    error: function () {
        area.append('<li class="layui-nav-item"><a href="login.html">登录</a></li>\n' +
            '    <li class="layui-nav-item"><a href="register.html">注册</a></li>');
        layer.msg('检测到与服务器断开连接！请检查网络');
    }
});