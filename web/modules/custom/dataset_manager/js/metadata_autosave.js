(function ($, Drupal) {
  Drupal.behaviors.metadataAutosave = {
    attach: function (context, settings) {
      if (!context.dataset_autosave_initialized) {
        context.dataset_autosave_initialized = true;
        setInterval(() => {
          $('input[name="autosave"]').trigger('click');
        }, 30000); // 30 seconds
      }
    }
  };
})(jQuery, Drupal);
