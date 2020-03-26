$("#admin-content-area").append(`<form class="layui-form publish-blog layui-form-pane">
        <h1>发布博客</h1>
        <div class="layui-form-item">
            <label class="layui-form-label" for="title">标题</label>
            <div class="layui-input-block">
                <input type="text" name="blog_title" lay-verify="required" placeholder="请输入博客标题" autocomplete="off"
                       class="layui-input" id="title">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" for="summary">简述</label>
            <div class="layui-input-block">
                <textarea name="blog_summary" placeholder="请输入内容简述" class="layui-textarea" id="summary"
                          autocomplete="off" lay-verify="required"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" for="content">内容</label>
            <div class="layui-input-block">
                <!--                <input type="file" name="blog_content" autocomplete="off" class="layui-btn"-->
                <!--                       id="content" lay-verify="required">-->
                <textarea type="file" name="blog_content" autocomplete="off" class="layui-textarea"
                          placeholder="请输入markdown格式的文章内容" id="content" lay-verify="required" rows="20"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">可见性</label>
            <div class="layui-input-block">
                <input type="radio" name="blog_visibility" value="1" title="立即可见" checked>
                <input type="radio" name="blog_visibility" value="0" title="仅管理员可见">
            </div>
        </div>
        <button class="layui-btn layui-btn-fluid layui-bg-blue" lay-filter="publish_blog" lay-submit>提交</button>
    </form>`);
layui.use('form', function () {
    const form = layui.form;
    form.on('submit(publish_blog)', function (data) {
        let button = $(".publish-blog button");
        button.removeClass('layui-bg-blue');
        button.addClass('layui-btn-disabled');
        let json = data.field;
        json.timestamp = getUnixTS();
        json.type = 'publish-new';
        json.app_id = 3;
        json.sign = $.md5('app_id' + json.app_id +
            'blog_content' + json.blog_content +
            'blog_summary' + json.blog_summary +
            'blog_title' + json.blog_title +
            'blog_visibility' + json.blog_visibility +
            'timestamp' + json.timestamp +
            'type' + json.type +
            'c0d17cb5a0f5c1bd94aa59dcf4f57e93'
        );
        json.sign = json.sign.toUpperCase();
        $.ajax({
            url: 'API/blog.php',
            type: "POST",
            dataType: 'json',
            async: false,
            xhrFields: {withCredentials: true},
            timeout: 5000,
            data: json,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    layer.open({
                        title: '成功',
                        content: '博客已经发布,点击确定刷新此页面',
                        yes: function (index) {
                            window.location.href = 'admin-blog-publish.html';
                            layer.close(index);
                        }
                    });
                } else layer.alert('奇怪的错误增加了！')
            },
            error: function () {
                layer.alert('与服务器失去连接，请检查您的网络状况!');
            }
        });
        button.addClass('layui-bg-blue');
        button.removeClass('layui-btn-disabled');
        return false;
    });
});