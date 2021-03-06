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
<!--    <li class="layui-nav-item" id="nav-support"><a href="">服务支持</a></li>-->
<!--    <li class="layui-nav-item" id="nav-survey"><a href="">用户调查</a></li>-->
</ul>
<div id="main-content-area">
    
</div>
<footer>
<div class="layui-container">
    <div class="layui-row">
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">©2020 GFW-Breaker,.Inc.</div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6"><a href="https://t.me/ZHGFWBreaker">加入Telegram官方售后群</a></div>
    </div>
    </div>
</footer>
`);
let area = $("#nav-list");
if (getQueryString('action') === 'quit')
    $.ajax({url: 'API/loginDisable.php', type: 'GET', async: false});
$.ajax({
    url: 'API/loginCheck.php',
    type: "POST",
    dataType: 'json',
    async: false,
    timeout: 3000,
    tryCount: 0,
    retryLimit: 5,
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
        if (this.tryCount === 0) {
            area.append(`
        <li class="layui-nav-item">
            <a href="login.html">登录</a>
        </li>
        <li class="layui-nav-item">
            <a href="register.html">注册</a>
        </li>`);
            is_login = false;
        }
        if (this.tryCount < this.retryLimit) {
            this.tryCount++;
            $.ajax(this);
        } else {
            layui.use('layer', function () {
                let layer = layui.layer;
                layer.msg('连接服务器超时,请检查您的网络连接后重试')
            });
        }
    }
});
layui.use('element', function () {
    let element = layui.element;
});
if (getQueryString('invite')) $.ajax({
    url: 'API/setInvitationLink.php',
    type: 'POST',
    data: {"invite_token": getQueryString('invite')},
});
