$(document).ready(function() {
    if($('#seconds').length) {
        var sec = parseInt($("#seconds").html()) + parseInt($("#minutes").html()) * 60 + parseInt($("#hours").html()) * 3600;
        function pad(val) {
            return val > 9 ? val : "0" + val;
        }
        window.setInterval(function(){
            $("#seconds").html(pad(++sec % 60));
            $("#minutes").html(pad(parseInt((sec / 60) % 60, 10)));
            $("#hours").html(pad(parseInt(sec / 3600, 10)));
			$("#lifetime_counter").html(parseInt($("#lifetime_counter").html())-1);
        }, 1000);
    }
});

function showError(message, hideDelay = 30000){
    if(!message || message.length == 0) return;
    $.notify({
        icon: 'fa fa-exclamation-triangle',
        title: '',
        message: message
    },{
        type: 'danger',
        delay: hideDelay,
        mouse_over: "pause",
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated fadeOutRight'
        },
    });
}
function showWarning(message, hideDelay = 30000){
    if(!message || message.length == 0) return;
    $.notify({
        icon: 'fa fa-warning',
        title: '',
        message: message
    },{
        type: 'warning',
        delay: hideDelay,
        mouse_over: "pause",
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated fadeOutRight'
        },
    });
}
function showInfo(message, hideDelay = 30000){
    if(!message || message.length == 0) return;
    $.notify({
        icon: 'fa fa-info',
        title: '',
        message: message
    },{
        type: 'info',
        delay: hideDelay,
        mouse_over: "pause",
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated fadeOutRight'
        },
    });
}
function showSuccess(message, hideDelay = 30000){
    if(!message || message.length == 0) return;
    $.notify({
        icon: 'fa fa-check',
        title: '',
        message: message
    },{
        type: 'success',
        delay: hideDelay,
        mouse_over: "pause",
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated fadeOutRight'
        },
    });
}
