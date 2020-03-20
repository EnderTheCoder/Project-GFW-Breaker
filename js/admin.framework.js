$("body").prepend(`
<ul class="layui-nav layui-nav-tree layui-nav-side">
    <li class="layui-nav-item">
        <a href="javascript:">日志</a>
        <dl class="layui-nav-child">
            <dd><a href="" id="access-log">访问日志</a></dd>
            <dd><a href="" id="login-log">登录日志</a></dd>
            <dd><a href="" id="admin-log">管理员日志</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item">
        <a href="javascript:">博客管理</a>
        <dl class="layui-nav-child">
            <dd><a href="admin.html">博客总览</a></dd>
            <dd><a href="admin-blog-publish.html">发布博客</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item">
        <a href="javascript:">用户管理</a>
        <dl class="layui-nav-child">
            <dd><a href="">用户设置</a></dd>
            <dd><a href="">用户列表</a></dd>
            <dd><a href="">添加用户</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item">
        <a href="javascript:">产品管理</a>
        <dl class="layui-nav-child">
            <dd><a href="admin-plan.html">计划列表</a></dd>
            <dd><a href="admin-vmess-group.html">vmess组列表</a></dd>
            <dd><a href="admin-vmess.html">vmess线路列表</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item"><a href="">退出登录</a></li>
</ul>
<div id="admin-content-area">

</div>
`);
layui.use('element', function(){
    let element = layui.element;

});