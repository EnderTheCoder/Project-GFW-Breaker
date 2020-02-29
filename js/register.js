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
            let json = {};
            json.app_id = 1;
            json.timestamp = getUnixTS();
            json.type = 'dupCheck';
            json.key = 'username';
            json.value = value;
            json.sign = $.md5('app_id1key' + json.key + 'timestamp' + json.timestamp + 'type' + json.type + 'value' + json.value + '6ab43fb5a4d624f9fa000bc83ccef011');
            json.sign = json.sign.toUpperCase();
            $.ajax({
                url: 'API/identify.php',
                type: "POST",
                dataType: 'json',
                async: false,
                timeout: 5000,
                data: json,
                success: function (result) {
                    let json = eval(result);
                    if (json['code'] !== 100)
                        return '该用户名已被注册!请更换未注册用户名';
                },
                error: function () {

                }
            });
            json.key = 'email';
            json.value = $('#email').val();
            json.sign = $.md5('app_id1key' + json.key + 'timestamp' + json.timestamp + 'type' + json.type + 'value' + json.value + '6ab43fb5a4d624f9fa000bc83ccef011');
            json.sign = json.sign.toUpperCase();
            $.ajax({
                url: 'API/identify.php',
                type: "POST",
                dataType: 'json',
                async: false,
                timeout: 5000,
                data: json,
                success: function (result) {
                    let json = eval(result);
                    if (json['code'] !== 100)
                        return '该邮箱已被注册!请更换未注册邮箱';
                },
                error: function () {

                }
            });
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
        data.field.sign = $.md5('app_id1email' + data.field.email + 'password' + data.field.password + 'timestamp' + data.field.timestamp + 'type' + data.field.type + 'username' + data.field.username + '6ab43fb5a4d624f9fa000bc83ccef011');
        data.field.sign = data.field.sign.toUpperCase();
        console.log(data.field);
        $.ajax({
            url: 'API/identify.php',
            type: "POST",
            dataType: 'json',
            async: true,
            timeout: 5000,
            data: data.field,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    layer.msg('注册成功,请到邮箱查收验证连接后登录');
                    window.location.href = 'login.html';
                } else {
                    layer.msg('发生了未知的错误!');
                }
            },
            error: function () {

            }
        });
        return false;
    });
});