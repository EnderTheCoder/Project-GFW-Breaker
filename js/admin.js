let json = {};
json.app_id = 3;
json.type = 'login-check';
json.timestamp = getUnixTS();
json.sign = $.md5('app_id3timestamp' + json.timestamp + 'type' + json.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93');
json.sign = json.sign.toUpperCase();
$.ajax({
    url: 'API/admin.php',
    type: "POST",
    dataType: 'json',
    timeout: 5000,
    data: json,
    async: false,
    success: function (result) {
        let json = eval(result);
        if (json['code'] === 100) {
            if (!json['data']['is_login']) {
                layer.alert('ACCESS DENIED');
                window.location.href = 'admin-login.html';
            }
            // else showFramework();
        } else layer.msg('奇怪的错误增加了！')
    },
    error: function () {
        layer.msg('与服务器失去连接，请检查网络')
    }
});
//
// function showLoginForm() {
//     $("body").append(
//         `<form class="layui-form layui-form-pane" action="" id="login-form">
//     <h1>欢迎回来</h1>
//     <div class="layui-form-item">
//         <label class="layui-form-label" for="id">用&nbsp;&nbsp;户</label>
//         <div class="layui-input-block">
//             <input type="text" name="id" required lay-verify="required" placeholder="UID/用户名/邮箱"
//                    class="layui-input" id="id">
//         </div>
//     </div>
//     <div class="layui-form-item">
//         <label class="layui-form-label" for="password">密&nbsp;&nbsp;码</label>
//         <div class="layui-input-block">
//             <input type="password" name="password" required lay-verify="required|password" placeholder="请输入密码"
//                    class="layui-input" id="password">
//         </div>
//     </div>
//     <div class="layui-form-item">
//         <label class="layui-form-label" for="captcha">验证码</label>
//         <div class="layui-input-inline">
//             <input type="text" name="captcha" required lay-verify="required" placeholder="请输入图形验证码"
//                    class="layui-input" id="captcha" maxlength="4" autocomplete="off">
//         </div>
//         <img src="API/captcha.php" alt="验证码"
//              onclick="this.src='API/captcha.php?rand=' + Math.random()">
//
//     </div>
//     <button class="layui-btn layui-btn-fluid layui-bg-blue" lay-filter="login" lay-submit>登录</button>
// </form>`
//     )
// }

// function showFramework() {
//     $("body").append(`<ul class="layui-nav layui-nav-tree layui-nav-side">
//     <li class="layui-nav-item">
//         <a href="javascript:">日志</a>
//         <dl class="layui-nav-child">
//             <dd><a href="" id="access-log">访问日志</a></dd>
//             <dd><a href="" id="login-log">登录日志</a></dd>
//             <dd><a href="" id="admin-log">管理员日志</a></dd>
//         </dl>
//     </li>
//     <li class="layui-nav-item">
//         <a href="javascript:">博客管理</a>
//         <dl class="layui-nav-child">
//             <dd><a href="admin.html?action=pub">博客总览</a></dd>
//             <dd><a href="admin-blog-publish.html">发布博客</a></dd>
//         </dl>
//     </li>
//     <li class="layui-nav-item">
//         <a href="javascript:">用户管理</a>
//         <dl class="layui-nav-child">
//             <dd><a href="">用户设置</a></dd>
//             <dd><a href="">用户列表</a></dd>
//             <dd><a href="">添加用户</a></dd>
//         </dl>
//     </li>
//     <li class="layui-nav-item"><a href="">产品</a></li>
//     <li class="layui-nav-item"><a href="">大数据</a></li>
// </ul>
// <!--<div id="admin-content-area">-->
// <!--</div>-->
// `)
// }

// let action = getQueryString('action');
//
// switchTo(action);
//
//
// function switchTo(name) {
//     let area = $("#admin-content-area");
//     area.empty();
//     let template;
//     switch (name) {
//         case 'blog-publish':
//             template = `<form class="layui-form publish-blog layui-form-pane">
//         <h1>发布博客</h1>
//         <div class="layui-form-item">
//             <label class="layui-form-label" for="title">标题</label>
//             <div class="layui-input-block">
//                 <input type="text" name="blog_title" placeholder="请输入博客标题" autocomplete="off" class="layui-input" id="title">
//             </div>
//         </div>
//         <div class="layui-form-item">
//             <label class="layui-form-label" for="summary">简述</label>
//             <div class="layui-input-block">
//                 <textarea name="blog_summary" placeholder="请输入内容简述" class="layui-textarea" id="summary"
//                           autocomplete="off"></textarea>
//             </div>
//         </div>
//         <div class="layui-form-item">
//             <label class="layui-form-label" for="content">内容</label>
//             <div class="layui-input-block">
//                 <input type="file" name="blog_content" autocomplete="off" class="layui-btn"
//                        id="content">
//             </div>
//         </div>
//         <div class="layui-form-item">
//             <label class="layui-form-label">可见性</label>
//             <div class="layui-input-block">
//                 <input type="radio" name="blog_visibility" value="1" title="立即可见" checked>
//                 <input type="radio" name="blog_visibility" value="0" title="仅管理员可见">
//             </div>
//         </div>
//         <button class="layui-btn layui-btn-fluid layui-bg-blue" lay-filter="publish_blog" lay-submit>提交</button>
//     </form>
//     <script src="layui/layui.all.js"></script>
//     <script>
//     layui.use('form', function () {
//         const form = layui.form;
//         form.on('submit(publish_blog)', function (data) {
//             let button = $(".publish-blog button");
//             button.removeClass('layui-bg-blue');
//             button.addClass('layui-btn-disabled');
//             let json = data.field;
//             json.timestamp = getUnixTS();
//             json.type = 'publish-new';
//             json.app_id = 3;
//             json.sign = $.md5('app_id3blog_content' + json.blog_content +
//             'blog_summary' + json.blog_summary +
//             'blog_title' + json.blog_title +
//             'blog_visible' + json.blog_visibility +
//             'timestamp' + json.timestamp +
//             'type' + json.type +
//             'c0d17cb5a0f5c1bd94aa59dcf4f57e93'
//             );
//             json.sign = json.sign.toUpperCase();
//             $.ajax({
//                 url: 'API/blog.php',
//                 type: "POST",
//                 dataType: 'json',
//                 async: false,
//                 xhrFields: {withCredentials: true},
//                 timeout: 5000,
//                 data: json,
//                 success: function (result) {
//                     let json = eval(result);
//                     if (json['code'] === 100) {
//                         layer.alert('发布成功！');
//                         window.location.href = 'admin.html?action=blog-publish'
//                     } else layer.alert('奇怪的错误增加了！')
//                 },
//                 error: function () {
//                     layer.alert('与服务器失去连接，请检查您的网络状况!');
//                 }
//             });
//             button.addClass('layui-bg-blue');
//             button.removeClass('layui-btn-disabled');
//             return false;
//         });
//     });
//     </script>
// `;
//             break;
//     }
//     area.append(template);
// }