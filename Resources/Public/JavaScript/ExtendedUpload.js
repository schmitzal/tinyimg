import DocumentService from "@typo3/core/document-service.js";

class ExtendedUpload {
  constructor() {
    const trigger = document.querySelector('.t3js-drag-uploader-trigger');

    trigger.addEventListener('progress', function (event, me, percentage) {
      console.log(me);
      console.log(percentage);
      if (percentage === '100%' && me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
        me.$progressPercentage.textContent = '';
        me.updateMessage(TYPO3.lang["compressingLabel"]);
        me.$row.classList.add('compressing');
      }
    });
    trigger.addEventListener('uploadSuccess', function (event, me, data) {
      if (me.file.type.match(/image\/(jpg|jpeg|png)$/i)) {
        me.$row.classList.remove('compressing');
      }
    });
  }
}

export default new ExtendedUpload;
