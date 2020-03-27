let id = getQueryString('page');
$("#nav-blog").addClass('layui-this');
if (id) {
    $(".layui-container").remove();
    $("body").append("<h1 id='blog-title'></h1>" +
        "<div id='blog-author'></div>" +
        "<div id='blog-time'></div>" +
        "<div id='blog-content'></div>");
    let json = {};
    json.app_id = 1;
    json.type = 'single';
    json.id = id;
    json.timestamp = getUnixTS();
    json.sign = $.md5('app_id1id' + json.id + 'timestamp' + json.timestamp + 'type' + json.type + '6ab43fb5a4d624f9fa000bc83ccef011');
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
                    $("title").html('GFW-Breaker ' + json['data'][0]['title']);
                    let converter = new Markdown.Converter();
                    let html = converter.makeHtml(json['data'][0]['content']);
                    $("#blog-title").html(json['data'][0]['title']);
                    $("#blog-author").html('作者:' + json['data'][0]['author']);
                    $("#blog-time").html('时间:' + getDate(json['data'][0]['timestamp'], 'yyyy-MM-dd hh:mm'));
                    $("#blog-content").html(html);
                    break;
                case 213:
                    layui.use('layer', function () {
                        let layer = layui.layer;
                        layer.alert('您太快了!慢点吧')
                    });
                    break;
                default:
                    layui.use('layer', function () {
                        let layer = layui.layer;
                        layer.alert('发生了未知的错误')
                    });
            }
        },
        error: function () {
            layui.use('layer', function () {
                let layer = layui.layer;
                layer.alert('与服务器连接断开,请检查您的网络')
            });
        }
    });
} else {
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
                            '<div class="layui-col-xs12 layui-col-sm4 layui-col-md3 blog-frame" id="' +
                            'row-' + json['data'][i]['id'] +
                            '">\n' +
                            '            <div class="blog-block ' + randomColorClass() + '">\n' +
                            '                <h1>\n' +
                            json['data'][i]['title'] +
                            '                </h1>\n' +
                            '                <p>\n' +
                            json['data'][i]['summary'] +
                            '                </p>\n' +
                            '            </div>\n' +
                            '        </div>'
                        );
                        $("#row-" + json['data'][i]['id']).click(function () {
                            window.location.href = 'blog.html?page=' + json['data'][i]['id'];
                        });
                    }
                    break;
                case 213:
                    layui.use('layer', function () {
                        let layer = layui.layer;
                        layer.alert('您太快了!慢点吧')
                    });
                    break;
                default:
                    layui.use('layer', function () {
                        let layer = layui.layer;
                        layer.alert('发生了未知的错误')
                    });
            }
        },
        error: function () {
            layui.use('layer', function () {
                let layer = layui.layer;
                layer.alert('与服务器连接断开,请检查您的网络')
            });
        }
    });
}
