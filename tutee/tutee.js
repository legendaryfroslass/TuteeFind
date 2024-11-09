// JavaScript to show the toast
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('msg-sendBtn').addEventListener('click', function () {
        var toastEl = document.getElementById('toastMsgSent');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
});

