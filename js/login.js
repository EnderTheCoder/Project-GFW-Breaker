layui.use('form', function () {
    const form = layui.form;
    form.on('submit(login)', function (data) {
        let json = data.field;
        let json_decode = eval(json);
        let app_key = '6ab43fb5a4d624f9fa000bc83ccef011';
        let timestamp = getUnixTS();
        //title visible summary markdown
        json.sign = $.md5('app_id1id' + json_decode['id'] + 'password' + json_decode['password'] + 'timestamp' + timestamp + 'typelogin' + app_key);
        json.sign = json.sign.toUpperCase();
        json.type = 'login';
        json.app_id = '1';
        json.timestamp = timestamp;
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
                    case 100:
                        window.location.href = 'index.html';
                        break;
                    default:
                        layer.msg('发生了未知的错误');
                }
            },
            error: function () {

            }
        });
        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    });
});