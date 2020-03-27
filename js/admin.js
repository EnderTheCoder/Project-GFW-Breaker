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
        switch (json['code']) {
            case 100:
                return;
            case 207:
                window.location.href = 'admin-login.html';
                break;
            default:
                layui.use('layer', function () {
                    let layer = layui.layer;
                    layer.alert('奇怪的错误增加了！')
                });
        }
    },
    error: function () {
        layui.use('layer', function () {
            let layer = layui.layer;
            layer.alert('与服务器失去连接，请检查网络')
        });
    }
});
