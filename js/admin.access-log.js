let data = {};
let action = getQueryString('action');
$("#admin-content-area").append(`
                <table id="access-log-table" lay-filter="access-log-table">
                    <thead>
                        <tr>
                            <th lay-data="{field:'id',width:70}">id</th>    
                            <th lay-data="{field:'ip_addr',width:120}">IP地址</th>
                            <th lay-data="{field:'is_login',width:90}">是否登录</th>
                            <th lay-data="{field:'timestamp',width:150}">访问时间</th>
                            <th lay-data="{field:'access_url'}">访问地址</th>
                            <th lay-data="{field:'action',width:100}">操作</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody> 
                </table>
    `);
if (!action) {
    data.app_id = 3;
    data.type = 'get-access-log';
    data.timestamp = getUnixTS();
    data.sign = $.md5(
        'app_id' + data.app_id +
        'timestamp' + data.timestamp +
        'type' + data.type +
        'c0d17cb5a0f5c1bd94aa59dcf4f57e93').toUpperCase();
    $.ajax({
        url: 'API/admin.php',
        type: "POST",
        dataType: 'json',
        timeout: 5000,
        data: data,
        success: function (result) {
            //id, ip_addr, is_login, timestamp, access_url
            let json = eval(result);
            if (json['code'] === 100) {
                let area = $("#access-log-table tbody");
                for (let i = 0; i < json['data']['row']; i++) {
                    area.append(`
                    <tr>
                        <td>` + json['data'][i]['id'] + `</td>
                        <td>` + json['data'][i]['ip_addr'] + `</td>
                        <td>` + json['data'][i]['is_login'] + `</td>
                        <td>` + getDate(json['data'][i]['timestamp'], 'yyyy-MM-dd hh:mm') + `</td>
                        <td>` + json['data'][i]['access_url'] + `</td>
                        <td><a style="color: blue" href="admin-access-log.html?action=show&id=` + json['data'][i]['id'] + `">详情</a>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color: red"  href="admin-access-log.html?action=edit&id=` + json['data'][i]['id'] + `">删除</a></td>
                    </tr>
                    `);
                }
                layui.use('table', function () {
                    let table = layui.table;
                    table.init('access-log-table', {
                        limit: 100,
                        height: 500,
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