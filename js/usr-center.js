$.ajax({
    url: 'API/loginCheck.php',
    type: "POST",
    dataType: 'json',
    async: true,
    timeout: 3000,
    success: function (result) {
        let json = eval(result);
        if (!json['data']['is_login']) {
            alert("登录已过期或失效！请重新登录");
            window.location.href = "index.html";
        }
    }
});
let action = getQueryString('action');
let body = $("body");
switch (action) {
    case 'plan':
        body.append("<div class=\"layui-container\"><div class=\"layui-row\"></div></div>\n");
        let json = {};
        json.app_id = 1;
        json.type = 'plan-all';
        json.timestamp = getUnixTS();
        json.sign = $.md5('app_id1id' + json.id + 'timestamp' + json.timestamp + 'type' + json.type + '6ab43fb5a4d624f9fa000bc83ccef011');
        json.sign = json.sign.toUpperCase();
        $.ajax({
            url: 'API/usrCenter.php',
            type: "POST",
            dataType: 'json',
            async: false,
            timeout: 5000,
            data: json,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    for (let i = 0; i < json['data']['row']; i++) {
                        $(".layui-row").append(
                            '<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 plan-box">' +
                            '            <div class="plan-block">' +
                            '                <h2>' +
                            json['data'][i]['name'] +
                            '</h2>' +
                            '                <div>到期时间:' +
                            getDate(json['data'][i]['lim_time'], 'yyyy-MM-dd') +
                            '</div>' +
                            '                <div>允许多设备在线:' +
                            booleanToWord(json['data'][i]['multi_device']) +
                            '</div>' +
                            '                <div>流量剩余:' +
                            json['data'][i]['flow'] +
                            'GB</div>' +
                            '                <div>线路数量:' +
                            json['data'][i]['line'] +
                            '</div>' +
                            '                <div>线路评级:' +
                            json['data'][i]['speed_rank'] +
                            '</div>' +
                            '                <div>自动续费:' +
                            booleanToWord(json['data'][i]['auto_renewal']) +
                            '</div>' +
                            '                <div>已付款:' +
                            json['data'][i]['charge'] +
                            'CNY</div>' +
                            '                <button type="button" class="layui-btn layui-btn-fluid layui-btn-normal" id="plan-btn' +
                            json['data'][i]['id'] +
                            '">详细信息</button>\n' +
                            '            </div>\n' +
                            '        </div>'
                        );
                    }
                }
            },
            error: function () {
                layer.msg('与服务器失去连接，请检查网络')
            }
        });
        break;
    case 'billing':
        body.append('<div class="layui-container">\n' +
            '    <div class="layui-row cash-top">\n' +
            '        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">\n' +
            '            <span>账户余额：</span><span id="billing-a"></span><span>&nbsp;CNY</span>\n' +
            '        </div>\n' +
            '        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">\n' +
            '            <span>本月总账：</span><span id="billing-b"></span><span>&nbsp;CNY</span>\n' +
            '        </div>\n' +
            '        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">\n' +
            '            <span>本月支出：</span><span id="billing-c"></span><span>&nbsp;CNY</span>\n' +
            '        </div>\n' +
            '        <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">\n' +
            '            <span>本月收入：</span><span id="billing-d"></span><span>&nbsp;CNY</span>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="layui-row billing-area">\n' +
            '    </div>\n' +
            '</div>');
        let billing = {};
        billing.app_id = 1;
        billing.type = 'billing-all';
        billing.timestamp = getUnixTS();
        billing.sign = $.md5('app_id1id' + billing.id + 'timestamp' + billing.timestamp + 'type' + billing.type + '6ab43fb5a4d624f9fa000bc83ccef011');
        billing.sign = billing.sign.toUpperCase();
        $.ajax({
            url: 'API/usrCenter.php',
            type: "POST",
            dataType: 'json',
            timeout: 5000,
            data: billing,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    for (let i = 0; i < json['data']['row']; i++) {
                        let type;
                        switch (json['data'][i]['type']) {
                            case 'recharge':
                                type = '充值';
                                break;
                            case 'gift':
                                type = '赠送';
                                break;
                            case 'auto_renewal':
                                type = '自动续费';
                                break;
                            case 'subscription':
                                type = '订购';
                                break;
                        }
                        let color = (json['data'][i]['type'] === 'recharge' || json['data'][i]['type'] === 'gift') ? 'font-color-high' : 'font-color-low';
                        let template = '<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 billing-box">' +
                            '<div class="billing-block">' +
                            '<h1 class="' + color + '">' +
                            json['data'][i]['money'] +
                            '</h1>' +
                            '<h6>CNY</h6>' +
                            '<p>单号：' +
                            json['data'][i]['id'] +
                            '</p>' +
                            '<p>类型：' +
                            type +
                            '</p>' +
                            '<p>时间：' +
                            getDate(json['data'][i]['timestamp'], 'yyyy-MM-dd') +
                            '</p>' +
                            '</div>' +
                            '</div>';
                        $(".billing-area").append(template);
                    }
                }
            },
            error: function () {
                layer.msg('与服务器失去连接，请检查网络')
            }
        });
        billing.type = 'billing-top';
        $.ajax({
            url: 'API/usrCenter.php',
            type: "POST",
            dataType: 'json',
            timeout: 5000,
            data: billing,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    $("#billing-a").html(json['data'][0]['money']);
                    $("#billing-c").html(json['data'][0]['money_out']);
                    $("#billing-d").html(json['data'][0]['money_in']);
                    let b = $("#billing-b");
                    if (json['data'][0]['money_in'] > json['data'][0]['money_out']) {
                        b.html(json['data'][0]['money_in'] - json['data'][0]['money_out']);
                        b.css('color', '#EE0033');
                    } else {
                        b.html(json['data'][0]['money_out'] - json['data'][0]['money_out']);
                        b.css('color', '#00CC00');
                    }
                } else layer.msg('奇怪的错误增加了！')
            },
            error: function () {
                layer.msg('与服务器失去连接，请检查网络')
            }
        });
        break;
    case 'setting':

        break;
    default:
        window.location.href = "index.html";
}