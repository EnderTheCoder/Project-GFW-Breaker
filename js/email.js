let json = {};
json.type = 'emailV';
json.app_id = 1;
json.timestamp = getUnixTS();
json.email_token = getQueryString('email_token');
json.sign = $.md5('app_id1email_token' + json.email_token + 'timestamp' + json.timestamp + 'type' + json.type + '6ab43fb5a4d624f9fa000bc83ccef011');
json.sign = json.sign.toUpperCase();
$.ajax({
    url: 'API/identify.php',
    type: "POST",
    dataType: 'json',
    async: true,
    timeout: 5000,
    data: json,
    success: function (result) {
        let json = eval(result);
        switch (json['code']) {
            case 101:
                window.location.href = json['data']['location'];
                break;
            case 207:
                alert('链接已失效或不存在!');
                window.location.href = SITE_URL;
                break;
            case 203:
                alert('链接缺少关键部分!');
                window.location.href = SITE_URL;
                break;
            default:
                alert('发生了未知错误!');
                window.location.href = SITE_URL;
        }
    },
    error: function () {

    }
});