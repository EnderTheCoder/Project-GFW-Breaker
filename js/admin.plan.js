$("#admin-content-area").append(`
<table id="plan-table" lay-filter="plan-table">
<thead>
    <tr>
        <th lay-data="{field:'id'}">名称</th>    
        <th lay-data="{field:'name'}">名称</th>
        <th lay-data="{field:'price'}">价格</th>
        <th lay-data="{field:'buy_cnt'}">购买数</th>
    </tr> 
</thead>
<tbody>

</tbody> 
</table>
`);
let json = {};
json.app_id = 3;
json.type = 'get-plan';
json.timestamp = getUnixTS();
json.sign = $.md5('app_id3timestamp' + json.timestamp + 'type' + json.type + 'c0d17cb5a0f5c1bd94aa59dcf4f57e93');
json.sign = json.sign.toUpperCase();
$.ajax({
    url: 'API/admin.php',
    type: "POST",
    dataType: 'json',
    timeout: 5000,
    data: json,
    success: function (result) {
        let json = eval(result);
        if (json['code'] === 100) {
            layui.use('table', function () {
                let table = layui.table;
                table.init('plan-table', {
                    height: 315
                    , limit: 10
                });
            });

        } else layer.msg('奇怪的错误增加了！')
    },
    error: function () {
        layer.msg('与服务器失去连接，请检查网络')
    }
});
