let action = getQueryString('action');
let data = {};
if (!action) {
    $("#admin-content-area").append(`
<table id="plan-table" lay-filter="plan-table">
<thead>
    <tr>
        <th lay-data="{field:'id'}">id</th>    
        <th lay-data="{field:'name'}">名称</th>
        <th lay-data="{field:'price'}">价格</th>
        <th lay-data="{field:'flow_limit'}">流量限制</th>
        <th lay-data="{field:'buy_cnt'}">购买数</th>
        <th lay-data="{field:'action'}">操作</th>
    </tr>
</thead>
<tbody>

</tbody> 
</table>
<button type="button" class="layui-btn layui-btn-fluid" id="add-plan-btn">添加计划</button>
`);

    $("#add-plan-btn").click(function () {
        layui.use('layer', function(){
            let layer = layui.layer;
            layer.open({
                type: 2,
                content: './iframe/add-plan.html',
                anim: 1,
                area: ['400px', '380px'],
            });
        });
    });
    data.app_id = 3;
    data.type = 'get-plan';
    data.timestamp = getUnixTS();
    data.sign = $.md5('app_id3timestamp' + data.timestamp + 'type' + data.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93');
    data.sign = data.sign.toUpperCase();
    $.ajax({
        url: 'API/admin.php',
        type: "POST",
        dataType: 'json',
        timeout: 5000,
        data: data,
        success: function (result) {
            let json = eval(result);
            if (json['code'] === 100) {
                let area = $("#plan-table tbody");
                for (let i = 0; i < json['data']['row']; i++) {
                    area.append(`
                <tr>
                    <td>` + json['data'][i]['id'] + `</td>
                    <td>` + json['data'][i]['name'] + `</td>
                    <td>` + '￥' + json['data'][i]['price'] + `/月` + `</td>
                    <td>` + json['data'][i]['flow_limit'] + 'GB' + `</td>
                    <td>` + json['data'][i]['buy_cnt'] + `</td>
                    <td><a style="color: blue" href="admin-plan.html?action=show&id=` + json['data'][i]['id'] + `">详情</a>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color: red"  href="admin-plan.html?action=delete&id=` + json['data'][i]['id'] + `">删除</a></td>
                </tr>
                `);
                }
                layui.use('table', function () {
                    let table = layui.table;
                    table.init('plan-table', {
                        limit: 30
                    });
                });
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
}

if (action === 'show') {
    $("#admin-content-area").append(`
    <div class="layui-container">
        <div class="row">
            <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                <div id="plan-info">
                </div>
            </div>
            <div class="layui-col-xs12 layui-col-sm8 layui-col-md8">
                <table id="vmess-group-table" lay-filter="vmess-group-table">
                    <thead>
                        <tr>
                            <th lay-data="{field:'id'}">id</th>    
                            <th lay-data="{field:'name'}">名称</th>
                            <th lay-data="{field:'speed_rank'}">速度评级</th>
                            <th lay-data="{field:'flow_limit'}">流量限制</th>
                            <th lay-data="{field:'flow'}">已使用流量</th>
                            <th lay-data="{field:'action'}">操作</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody> 
                </table>
                <button type="button" class="layui-btn layui-btn-fluid" id="edit-vmess-group-btn">编辑vmess组</button>
            </div>
        </div>
    </div>
    `);
    $("#edit-vmess-group-btn").click(function () {
        layui.use('layer', function(){
            let layer = layui.layer;
            layer.open({
                type: 2,
                content: 'iframe/plan-edit-vmess-group.html?id=' + getQueryString('id'),
                anim: 1,
                area: ['400px', '380px'],
            });
        });
    });
    data.app_id = 3;
    data.type = 'get-plan';
    data.id = getQueryString('id');
    data.timestamp = getUnixTS();

    data.sign = $.md5('app_id3id' + data.id + 'timestamp' + data.timestamp + 'type' + data.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93').toUpperCase();
    $.ajax({
        url: 'API/admin.php',
        type: "POST",
        dataType: 'json',
        timeout: 5000,
        data: data,
        success: function (result) {
            let json = eval(result);
            if (json['code'] === 100) {
                let area = $("#plan-info");
                area.append(`
                <div>` + 'ID：' + json['data']['id'] + `</div>
                <div>` + '名称：' + json['data']['name'] + `</div>
                <div>` + '价格：￥' + json['data']['price'] + `/月` + `</div>
                <div>` + '流量上限：' + json['data']['flow_limit'] + 'GB' + `</div>
                <div>` + '累计售出：' + json['data']['buy_cnt'] + `</div>
                <button type="button" class="layui-btn layui-btn-fluid layui-btn-danger" onclick="window.location.href='admin-plan.html?action=delete&id=` + json['data']['id'] + `'">删除</button>
                `);
                area = $("#vmess-group-table tbody");
                for (let i = 0; i < json['data']['son']['row']; i++) {
                    area.append(`
                    <tr>
                        <td>` + json['data']['son'][i]['id'] + `</td>
                        <td>` + json['data']['son'][i]['name'] + `</td>
                        <td>` + json['data']['son'][i]['speed_rank'] + `</td>
                        <td>` + json['data']['son'][i]['flow_limit'] + 'GB' + `</td>
                        <td>` + json['data']['son'][i]['flow'] + 'GB' + `</td>
                        <td><a style="color: blue" href="admin-vm1a1ess-group.html?action=show&id=` + json['data']['son'][i]['id'] + `">详情</a>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color: red"  href=admin-plan.html?action=edit&id="` + json['data']['son'][i]['id'] + `">删除</a></td>
                    </tr>
                    `);
                }
                layui.use('table', function () {
                    let table = layui.table;
                    table.init('vmess-group-table', {
                        limit: 30
                    });
                });
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
}

if (action === 'delete') {
    data.app_id = 3;
    data.type = 'delete-plan';
    data.id = getQueryString('id');
    data.timestamp = getUnixTS();
    data.sign = $.md5('app_id3id' + data.id + 'timestamp' + data.timestamp + 'type' + data.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93').toUpperCase();
    $.ajax({
        url: 'API/admin.php',
        type: "POST",
        dataType: 'json',
        timeout: 5000,
        data: data,
        success: function (result) {
            let json = eval(result);
            if (json['code'] === 100) {
                window.location.href = 'admin-plan.html';
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
}