define([
    'jquery',
    'TYPO3/CMS/Backend/Notification'
], function ($, Notification) {
    $(document).ready(function () {
        $('.t3js-drag-uploader-trigger').on('updateProgress', function (event, me, percentage) {
            if (percentage === '100%' && me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
                me.$progressPercentage.text('');
                me.updateMessage($('.tinyimg-compressing').text());
                me.$row.addClass('compressing');
            }
        }).on('uploadSuccess', function (event, me, data) {
            if (me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
                var percentageSaved = Math.round(100 - ((data.upload[0].size / me.file.size) * 100));
                me.$row.removeClass('compressing');
                Notification.info('Compression', 'Tinyimg saved you about ' + percentageSaved + '% of your file size!', 10);
            }
        });
    });
});