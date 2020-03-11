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
                    layer.alert('发布成功！');
                    window.location.href = 'admin-blog-publish.html'
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