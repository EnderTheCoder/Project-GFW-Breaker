$("#main-content-area").append(`
<table id="vmess-group" lay-filter="vmess-group"></table>
`);


layui.use('table', function () {
    let table = layui.table;
    table.render({
        elem: '#vmess-group',
        url: '/API/admin.php',
        where: {token: 'sasasas', id: 123},
        method: 'post',
        response: {
            statusName: 'code',
            statusCode: 100,
            msgName: 'msg',
            countName: 'count',
            dataName: 'data',
        }
    });


});