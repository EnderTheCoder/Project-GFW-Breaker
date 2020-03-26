let is_login;
$("body").prepend(`
<ul class="layui-nav" id="nav-list">
    <li class="layui-nav-item" id="nav-logo">
        <a href="index.html">
            <img src="img/Logo-Big.png" alt="logo" id="logo">
        </a>
    </li>
    <li class="layui-nav-item" id="nav-shop"><a href="shop.html">购买计划</a></li>
    <li class="layui-nav-item" id="nav-blog"><a href="blog.html">官方博客</a></li>
    <li class="layui-nav-item" id="nav-client">
                <a href="#">客户端下载</a>
                <dl class="layui-nav-child">
                    <dd><a href="client.html?type=windows">Windows</a></dd>
                    <dd><a href="client.html?type=linux">Linux</a></dd>
                    <dd><a href="client.html?type=android">Android</a></dd>
                </dl>
            </li>
    <li class="layui-nav-item" id="nav-support"><a href="">服务支持</a></li>
    <li class="layui-nav-item" id="nav-survey"><a href="">用户调查</a></li>
</ul>
<div id="main-content-area">
    
</div>
`);
let area = $("#nav-list");
if (getQueryString('action') === 'quit')
    $.ajax({url: 'API/loginDisable.php', type: 'GET', async: false});
$.ajax({
    url: 'API/loginCheck.php',
    type: "POST",
    dataType: 'json',
    async: false,
    timeout: 5000,
    success: function (result) {
        let json = eval(result);
        if (json['data']['is_login']) {
            area.append(`
            <li class="layui-nav-item" id="nav-usr-center">
                <a href="#">用户中心</a>
                <dl class="layui-nav-child">
                    <dd><a href="usr-center.html?action=plan">订阅计划</a></dd>
                    <dd><a href="usr-center.html?action=billing">用户账单</a></dd>
                    <dd><a href="usr-center.html?action=setting">用户设置</a></dd>
                    <dd id="nav-qiut"><a href="index.html?action=quit">退出登录</a></dd>
                </dl>
            </li>`);
            is_login = true;
        } else {
            area.append(`
                    <li class="layui-nav-item" id="nav-login">
                        <a href="login.html">登录</a>
                    </li>
                    <li class="layui-nav-item" id="nav-register">
                        <a href="register.html">注册</a>
                    </li>
            `);
            is_login = false;
        }
    },
    error: function () {
        area.append(`
        <li class="layui-nav-item">
            <a href="login.html">登录</a>
        </li>
        <li class="layui-nav-item">
            <a href="register.html">注册</a>
        </li>`);
        is_login = false;
        layui.use('layer', function () {
            let layer = layui.layer;
            layer.alert('与服务器失去连接，请检查网络')
        });
    }
});
layui.use('element', function(){
    let element = layui.element;
});