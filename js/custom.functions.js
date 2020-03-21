const SITE_URL = 'http://localhost';

function getUnixTS() {
    return Math.round(new Date().getTime() / 1000).toString();
}

function getQueryString(name) {
    const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    const r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

function getDate(val, fmt) {
    if (!val) {
        return '';
    }
    if (val && val.length === 10) {
        val = parseInt(val * 1000);
    }
    let date = new Date(val);
    let o = {
        "M+": date.getMonth() + 1, //月份
        "d+": date.getDate(), //日
        "h+": date.getHours(), //小时
        "m+": date.getMinutes(), //分
        "s+": date.getSeconds(), //秒
        "q+": Math.floor((date.getMonth() + 3) / 3), //季度
        "S": date.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (let k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

function checkLogin() {
    $.ajax({
        url: 'API/loginCheck.php',
        type: "POST",
        dataType: 'json',
        async: false,
        timeout: 3000,
        success: function (result) {
            let json = eval(result);
            return json['data']['is_login'];
        }
    });
    return false;
}

function booleanToWord(boolean) {
    return boolean ? 'YES' : 'NO';
}

function arrToObj(arr, isString) {
    let result = {};
    for (let a = 0; a < arr.length; a++) {
        result[a] = arr[a];
    }

    return isString ? JSON.stringify(result) : result;
}

