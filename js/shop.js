let action = getQueryString('action');
$("#nav-shop").addClass('layui-this');
area = $("#main-content-area");
area.append(`
<div class="layui-container">
    <div class="layui-row">
        
    </div>
</div>
`);
let data = {};
data.app_id = 3;
data.type = 'get-plan';
data.timestamp = getUnixTS();
data.sign = $.md5('app_id3timestamp' + data.timestamp + 'type' + data.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93');
data.sign = data.sign.toUpperCase();
$.ajax({
    url: 'API/shop.php',
    type: "POST",
    dataType: 'json',
    timeout: 5000,
    data: data,
    success: function (result) {
        let json = eval(result);
        if (json['code'] === 100) {
            let area = $("#main-content-area .layui-row");
            for (let i = 0; i < json['data']['row']; i++) {
                area.append(`
                <div class="layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg-3">
                    <div class="shop-plan-block ` + randomColorClass() + `">
                        <h2>` + json['data'][i]['name'] + `</h2>
                        <div><span>流量：</span>` + json['data'][i]['flow_limit'] + `GB/月</div>
                        <div><span>价格：</span>￥` + json['data'][i]['price'] + `/月</div>
                        <div><span>介绍：</span>` + json['data'][i]['info'] + `</div>
                        <button type="button" class="layui-btn layui-btn-fluid" id="shop-buy-btn-` + json['data'][i]['id'] + `">购买</button>
                    </div>
                </div>
                `);
                $("#shop-buy-btn-" + json['data'][i]['id']).click(function () {
                    if (is_login) {
                        layui.use('layer', function () {
                            let layer = layui.layer;
                            layer.open({
                                type: 2,
                                title: '购买计划',
                                content: './iframe/shop-buy.html?id=' + json['data'][i]['id'],
                                area: ['350px', '160px'],
                            });
                        });
                    } else {
                        layui.use('layer', function () {
                            let layer = layui.layer;
                            layer.open({
                                title: '警告',
                                content: '您尚未登录，将跳转至登录页',
                                yes: function (index) {
                                    window.location.href = 'login.html';
                                    layer.close(index);
                                }
                            });
                        });
                    }
                })
            }
        } else layui.use('layer', function () {
            let layer = layui.layer;
            layer.alert('奇怪的错误增加了！')
        });
    },
    error: function () {
        layui.use('layer', function () {
            let layer = layui.layer;
            layer.alert('与服务器失去连接，请检查网络')
        });
    }
});