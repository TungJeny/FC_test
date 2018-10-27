Vue.filter('format_number', function (n) {
    if (isNaN(n) || n == 0) return n;
    n = parseFloat(n);
    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
});
Vue.filter('display_value', function(value) {
    if (value === null || !value.length) {
        return '-';
    }
    return value;
});
