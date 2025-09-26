say_thank = function (mode,attach_id) {
    ajax.exec({
        action: 'thank',
        a : attach_id,
        m : mode
    })
}
ajax.callback.thank = function (data) {
    var json = data.message;
    if(json['error'] === true) {
        return false;
    }
    if(json['mode']=='list') {
        $('#VL' + json['attach_id'] ).html('' + json['list']);
    }
    if(json['mode']=='thank') {
        $('#VT' + json['attach_id'] ).html('' + json['thanked']);
        $('#VB' + json['attach_id'] ).html('' + json['list_button']);

    }
}
rate = function (attach_id,rating) {
    ajax.exec({
        action: 'rate',
        a: attach_id,
        v: rating
    })
}
ajax.callback.rate = function (data) {
    var json = data.message;
    if(json['error'] === true) {
        return false;
    }
    $('#VD' + json['attach_id'] ).html('' + json['your_rating']);
    $('#VR' + json['attach_id'] ).html('' + json['rating']);
    $('#VC' + json['attach_id'] ).html('' + json['rating_count']);
}