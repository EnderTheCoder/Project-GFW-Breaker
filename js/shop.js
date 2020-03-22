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
                        <button type="button" class="layui-btn layui-btn-fluid" id="shop-buy-btn">购买</button>
                    </div>
                </div>
                `);
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