layui.use('form', function () {
    const form = layui.form;
    form.on('submit(login)', function (data) {
        let json = data.field;
        json.type = 'login';
        json.app_id = '1';
        json.timestamp = getUnixTS();
        json.sign = $.md5(
            'app_id' + json.app_id +
            'captcha' + json.captcha +
            'id' + json.id +
            'password' + json.password +
            'timestamp' + json.timestamp +
            'type' + json.type +
            '6ab43fb5a4d624f9fa000bc83ccef011'
        );
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
                switch (json['code']) {
                    case 207:
                        layer.msg('用户名或密码错误');
                        break;
                    case 204:
                        layer.msg(json['data']);
                        break;
                    case 210:
                        layer.msg('验证码错误');
                        break;
                    case 100:
                        layer.msg('登陆成功');
                        window.location.href = 'index.html';
                        break;
                    default:
                        layer.msg('发生了未知的错误');
                }
            },
            error: function () {
                layui.use('layer', function () {
                    let layer = layui.layer;
                    layer.alert('与服务器失去连接，请检查网络后重试')
                });
            }
        });
        $("img").attr('src', 'API/captcha.php?rand=' + Math.random());
        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    });
});