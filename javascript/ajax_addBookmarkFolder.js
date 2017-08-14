$(document).on('click', ".mfp-content #Form_AddBookmarkFolderForm_action_doAddBookmarkFolder", function(event) {
  event.preventDefault();

  var form = $("#Form_AddBookmarkFolderForm");
  var submitButton = $(this);

  var bookmarks = $('#bookmarks-sortable');

  $.ajax(form.attr('action'), {
    type: "POST",
    data: form.serialize(),
    beforeSend: function() {
      submitButton.attr('value', ss.i18n._t('Bookmark.PROCESSING', 'Processing...'));
      submitButton.attr("disabled", true);
    },
    success: function(data) {
      try {
        var json = jQuery.parseJSON(data);

        if (typeof json == 'object') {
          bookmarks.append(json.Bookmark);

          update_bookmarks_widget(json.BookmarkWidget);
          reload_memberwidgets();
          open_mfp_popup_ajax(json.BookmarksLink);
        }
      }
      catch(err) {
        form.replaceWith(data);
        $('#Form_AddBookmarkFolderForm fieldset .field :input:visible').focus();
        $('#Form_AddBookmarkFolderForm :input[required]:visible').first().focus();
      }
    }
  });
});