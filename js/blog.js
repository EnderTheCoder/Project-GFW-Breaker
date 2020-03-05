let json = {};
json.app_id = 1;
json.type = 'all';
json.timestamp = getUnixTS();
json.sign = $.md5('app_id1timestamp' + json.timestamp + 'type' + json.type + '6ab43fb5a4d624f9fa000bc83ccef011');
json.sign = json.sign.toUpperCase();
$.ajax({
    url: 'API/blog.php',
    type: "POST",
    dataType: 'json',
    timeout: 5000,
    data: json,
    success: function (result) {
        let json = eval(result);
        switch (json['code']) {
            case 100:
                for (let i = 0; i < json['data']['row']; i++) {
                    $("#blog-append-area").append(
                        '<div class="layui-col-xs12 layui-col-sm4 layui-col-md3 blog-frame">\n' +
                        '            <div class="blog-block">\n' +
                        '                <h1>\n' +
                        json['data'][i]['title'] +
                        '                </h1>\n' +
                        '                <p>\n' +
                        json['data'][i]['summary'] +
                        '                </p>\n' +
                        '            </div>\n' +
                        '        </div>'
                    );
                }
                break;
            case 213:
                layer.msg('您太快了!慢点吧');
                break;
            default:
                layer.msg('发生了未知的错误');
        }
    },
    error: function () {

    }
});