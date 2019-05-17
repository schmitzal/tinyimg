define([
    'jquery'
], function ($) {
    $(document).ready(function () {
        $('.t3js-drag-uploader-trigger').on('updateProgress', function (event, me, percentage) {
            if (percentage === '100%' && me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
                me.$progressPercentage.text('');
                me.updateMessage($('.tinyimg-compressing').text());
                me.$row.addClass('compressing');
            }
        }).on('uploadSuccess', function (event, me, data) {
            if (me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
                me.$row.removeClass('compressing');
            }
        });
    });
});
