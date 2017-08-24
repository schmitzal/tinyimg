define(['jquery'], function($) {
    $(document).ready(function () {
        $.on('updateProgress', function () {
            alert('in progress!');
        });
    });
});