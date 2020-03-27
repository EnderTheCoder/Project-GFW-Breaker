$("#nav-client").addClass('layui-this');

let type = getQueryString('type');

area = $("#main-content-area");

area.append(`
    <div class="type-board">
        
    </div>
    <p class="version-board">最新版本:</p>
    <button class="layui-btn download-btn">免费下载</button>
`);

let data = {};


let board = $(".type-board");

let tail;

switch (type) {
    case 'windows': {
        board.html('Windows');
        tail = 'exe';
        break;
    }

    case 'android': {
        board.html('Android');
        tail = 'apk';
        break;
    }

    case 'linux': {
        board.html('Linux');
        tail = 'deb';
        break;
    }

    default: {
        window.location.href = 'index.html';
    }
}
data.app_id = 1;
data.timestamp = getUnixTS();
data.name = type;
data.sign = $.md5(
    'app_id' + data.app_id +
    'name' + data.name +
    'timestamp' + data.timestamp +
    '6ab43fb5a4d624f9fa000bc83ccef011');
data.sign = data.sign.toUpperCase();
$.ajax({
    url: 'API/version.php',
    type: "POST",
    dataType: 'json',
    timeout: 5000,
    data: data,
    success: function (result) {
        let json = eval(result);
        if (json['code'] === 100) {
            $(".version-board").append(json['data']);
            $(".download-btn").click(function () {
                window.open('./download/GFW-Breaker_' + type + '_' + json['data'] + '.' + tail);
            });
        } else layui.use('layer', function () {
            let layer = layui.layer;
            layer.alert('奇怪的错误增加了！')
        });
    }
});