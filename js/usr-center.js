$("#nav-usr-center").addClass('layui-this');
$.ajax({
    url: 'API/loginCheck.php',
    type: "POST",
    dataType: 'json',
    async: true,
    timeout: 3000,
    tryCount: 0,
    retryLimit: 5,
    success: function (result) {
        let json = eval(result);
        if (!json['data']['is_login']) {
            alert("登录已过期或失效！请重新登录");
            window.location.href = "index.html";
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
let action = getQueryString('action');
let body = $("#main-content-area");
switch (action) {
    case 'plan':
        body.append("<div class=\"layui-container\"><div class=\"layui-row\" id='plan-area'></div></div>\n");
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
            timeout: 2000,
            data: json,
            tryCount: 0,
            retryLimit: 5,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    for (let i = 0; i < json['data']['row']; i++) {
                        $("#plan-area").append(
                            '<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 plan-box">' +
                            '            <div class="plan-block">' +
                            '                <h2>' +
                            json['data'][i]['name'] +
                            '</h2>' +
                            '                <div>到期时间:' +
                            getDate(json['data'][i]['lim_time'], 'yyyy-MM-dd') +
                            '</div>' +
                            '                <div>流量剩余:' +
                            (json['data'][i]['flow']) + `/` + (json['data'][i]['lim_flow']) +
                            'GB</div>' +
                            '                <div>已付款:' +
                            json['data'][i]['charge'] +
                            'CNY</div>' +
                            '<div>信息:' +
                            json['data'][i]['info'] +
                            '</div>' +
                            '                <button type="button" class="layui-btn layui-btn-fluid ' + (json['data']['chosen'] === json['data'][i]['id'] ? 'layui-btn-danger' : 'layui-btn-normal') + '" id="plan-btn-' +
                            json['data'][i]['id'] +
                            '">' +
                            (json['data']['chosen'] === json['data'][i]['id'] ? '已选中' : '选择') + '</button>' +
                            '</div>' +
                            '</div>'
                        );
                        if (json['data']['chosen'] !== json['data'][i]['id'])
                            $("#plan-btn-" + json['data'][i]['id']).click(function () {
                                let data = {};
                                data.app_id = 1;
                                data.type = 'chose-plan';
                                data.timestamp = getUnixTS();
                                data.id = json['data'][i]['id'];
                                data.sign = $.md5(
                                    'app_id' + data.app_id +
                                    'id' + data.id +
                                    'timestamp' + data.timestamp +
                                    'type' + data.type +
                                    '6ab43fb5a4d624f9fa000bc83ccef011').toUpperCase();
                                $.ajax({
                                    url: './API/usrCenter.php',
                                    type: "POST",
                                    dataType: 'json',
                                    timeout: 2000,
                                    data: data,
                                    tryCount: 0,
                                    retryLimit: 5,
                                    success: function (result) {
                                        let json = eval(result);
                                        if (json['code'] === 100) {
                                            window.location.reload();
                                        } else layui.use('layer', function () {
                                            let layer = layui.layer;
                                            layer.alert('奇怪的错误增加了！')
                                        })
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
                            });
                    }
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
        break;
    case 'billing':
        let invite_settings;
        let getSettings = {};
        getSettings.app_id = 1;
        getSettings.type = 'get-feedback';
        getSettings.timestamp = getUnixTS();
        getSettings.sign = $.md5('app_id1id' + getSettings.id + 'timestamp' + getSettings.timestamp + 'type' + getSettings.type + '6ab43fb5a4d624f9fa000bc83ccef011').toUpperCase();
        $.ajax({
            url: 'API/usrCenter.php',
            type: "POST",
            dataType: 'json',
            timeout: 2000,
            data: getSettings,
            tryCount: 0,
            retryLimit: 5,
            success: function (result) {
                invite_settings = eval(result)['data'];
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
        body.append(`<div class="layui-container">
            <div class="layui-row cash-top">
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md6" style="
    line-height: 150px">
            <span>余额：</span><span id="billing-a"></span><span>&nbsp;CNY</span>
            </div>
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md6 invite-link-border" style="line-height: 50px">
            <span>邀请链接：</span><br><span id="invite-link"></span><button class="layui-btn layui-btn-normal invite-info" type="button">奖励说明</button>

            </div>
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md6" style="
    line-height: 150px">
            <span>累计邀请：</span><span id="billing-b"></span><span>&nbsp;人</span>
            </div>
            <button type="button" class="layui-btn layui-btn-fluid layui-btn-normal recharge-btn">充值</button>
            </div>
            <div class="layui-row billing-area">
            </div>
            </div>`);
        $(".invite-info").click(function () {
            layui.use('layer', function () {
                let layer = layui.layer;
                layer.open({
                    type: 1,
                    title: '邀请用户注册奖励说明',
                    content: '广大用户请注意,邀请你的朋友注册即可获取大量余额返利!发福利我们是认真的!' +
                        '当前返利比例为:被邀请用户充值金额的' + Number(invite_settings['invite_feedback_rating'] * 100) + '%;' +
                        '当前被邀请用户的充值倍率为' + Number(invite_settings['invite_recharge_rating'] * 100) + '%;' +
                        '当前每日邀请用户上限为' + invite_settings['invite_daily_limit'] + '.'
                });
            });
        });
        $(".recharge-btn").click(function () {
            layui.use('layer', function () {
                let layer = layui.layer;
                layer.open({
                    type: 2,
                    title: '充值',
                    content: './codepay/index.php',
                    area: ['400px', '400px'],
                    scrollbar: false
                });
            });
        });
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
            timeout: 2000,
            data: billing,
            tryCount: 0,
            retryLimit: 5,
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
                } else {
                    layui.use('layer', function () {
                        let layer = layui.layer;
                        layer.alert('奇怪的错误增加了!')
                    });
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
        billing.type = 'billing-top';
        $.ajax({
            url: 'API/usrCenter.php',
            type: "POST",
            dataType: 'json',
            timeout: 2000,
            data: billing,
            tryCount: 0,
            retryLimit: 5,
            success: function (result) {
                let json = eval(result);
                if (json['code'] === 100) {
                    $("#billing-a").html(json['data'][0]['money']);
                    $("#invite-link").html('http://' + window.location.host + '?invite=' + json['data'][0]['invite_token']);
                    $("#billing-b").html(json['data'][0]['invite_tot']);
                } else layui.use('layer', function () {
                    let layer = layui.layer;
                    layer.alert('奇怪的错误增加了!')
                });
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
        break;
    case 'setting':

        break;
    default:
        window.location.href = "index.html";
}