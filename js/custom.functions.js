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