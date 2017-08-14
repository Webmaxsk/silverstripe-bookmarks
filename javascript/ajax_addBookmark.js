$(document).on('click', ".mfp-content #Form_AddBookmarkForm_action_doAddBookmark", function(event) {
  event.preventDefault();

  var form = $("#Form_AddBookmarkForm");
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

          $('.bookmark-star').replaceWith(json.BookmarkStar);

          update_bookmarks_widget(json.BookmarkWidget);
          reload_memberwidgets();
          open_mfp_popup_ajax(json.BookmarksLink);
        }
      }
      catch(err) {
        form.replaceWith(data);
        $('#Form_AddBookmarkForm fieldset .field :input:visible').focus();
        $('#Form_AddBookmarkForm :input[required]:visible').first().focus();
      }
    }
  });
});