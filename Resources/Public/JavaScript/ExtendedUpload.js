const uploaderTriggers = document.querySelectorAll('.t3js-drag-uploader-trigger');

uploaderTriggers.forEach(function (trigger) {
  trigger.addEventListener('updateProgress', function (event) {
    let me = event.detail[0];
    const percentage = event.detail[1];

    if (percentage === '100%' && me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
      me.progressPercentage.textContent = '';
      me.updateMessage(TYPO3.lang["tinyimg.compressingLabel"]);
      me.row.classList.add('compressing');
    }
  });

  trigger.addEventListener('uploadSuccess', function (event) {
    let me = event.detail[0];

    if (me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
      me.row.classList.remove('compressing');
    }
  });
});
