layui.use('form', function () {
    const form = layui.form;
    form.verify({
        username: function (value, item) {
            if (!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)) {
                return '用户名不能有特殊字符';
            }
            if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                return '用户名首尾不能出现下划线\'_\'';
            }
            if (/^\d+\d+\d$/.test(value)) {
                return '用户名不能全为数字';
            }
        },
        password: function (value, item) {
            if (value !== $('#re-password').val())
                return '两次输入的密码不一致';
        }

    });
    form.on('submit(register)', function (data) {
        delete data.field.re_password;
        data.field.timestamp = getUnixTS();
        data.field.app_id = 1;
        data.field.type = 'register';
        data.field.sign = $.md5(
            'app_id' + data.field.app_id +
            'captcha' + data.field.captcha +
            'email' + data.field.email +
            'password' + data.field.password +
            'timestamp' + data.field.timestamp +
            'type' + data.field.type +
            'username' + data.field.username +
            '6ab43fb5a4d624f9fa000bc83ccef011'
        ).toUpperCase();
        $.ajax({
            url: 'API/identify.php',
            type: "POST",
            dataType: 'json',
            async: false,
            timeout: 2000,
            data: data.field,
            tryCount: 0,
            retryLimit: 5,
            success: function (result) {
                let json = eval(result);
                switch (json['code']) {
                    case 211:
                        if (json['data']['key'] === 'username')
                            layer.msg('该用户名已被注册，请更换');
                        if (json['data']['key'] === 'email')
                            layer.msg('该邮箱已被注册，请更换');
                        return false;
                    case 210:
                        layer.msg('验证码错误');
                        break;
                    case 100: {
                        layui.use('layer', function () {
                            let layer = layui.layer;
                            layer.open({
                                title: '注册成功',
                                content: '注册成功,点击确定跳转至登录页',
                                yes: function (index) {
                                    window.location.href = 'login.html';
                                    layer.close(index);
                                },
                                cancel: function (index) {
                                    window.location.href = 'login.html';
                                    layer.close(index);
                                    return false;
                                }
                            });
                        });
                        break;
                    }
                    default:
                        layer.msg('奇怪的错误增加了！');
                }
            },
            error: function () {
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
        $("img").attr('src', 'API/captcha.php?rand=' + Math.random());
        return false;
    });
});