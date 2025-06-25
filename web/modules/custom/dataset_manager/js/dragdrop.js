(function ($, Drupal) {
  Drupal.behaviors.dragDropUpload = {
    attach: function (context, settings) {
      const $dropArea = $('#drop-area', context);

      // Prevent duplicate processing
      if (!$dropArea.length || $dropArea.hasClass('processed')) return;
      $dropArea.addClass('processed');

      // Drag over styling
      $dropArea.on('dragover', function (e) {
        e.preventDefault();
        $dropArea.css('background-color', '#e6f7ff');
      });

      $dropArea.on('dragleave', function () {
        $dropArea.css('background-color', '#f4f4f4');
      });

      $dropArea.on('drop', function (e) {
        e.preventDefault();
        $dropArea.css('background-color', '#f4f4f4');

        const files = e.originalEvent.dataTransfer.files;
        if (!files.length) return;

        const file = files[0];
        const allowedTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type) || file.size > maxSize) {
          $('#file-info').html('<span style="color:red;">Invalid file type or file too large (max 5MB).</span>');
          return;
        }

        const formData = new FormData();
        formData.append('file', file);

        $('#file-info').html('Uploading...');

        fetch('/dataset/upload/ajax', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              $('#file-info').html(`<span style="color:red;">${data.error}</span>`);
            } else {
              $('#file-info').html(`
                <strong>File Name:</strong> ${data.filename}<br>
                <strong>Size:</strong> ${(data.filesize / 1024).toFixed(2)} KB
              `);
            }
          })
          .catch(() => {
            $('#file-info').html('<span style="color:red;">Upload failed.</span>');
          });
      });
    }
  };
})(jQuery, Drupal);
