$(document).on('click', ".mfp-content #Form_EditBookmarkForm_action_doEditBookmark, .mfp-content #Form_EditBookmarkForm_action_doDeleteBookmark", function(event) {
  event.preventDefault();

  var form = $("#Form_EditBookmarkForm");
  var submitButton = $(this);

  var actionName = $(this).attr("name");
  var action = actionName+"="+$(this).attr("value");

  $.ajax(form.attr('action'), {
    type: "POST",
    data: form.serialize()+"&"+action,
    beforeSend: function() {
      submitButton.attr('value', ss.i18n._t('Bookmark.PROCESSING', 'Processing...'));
      submitButton.attr("disabled", true);
    },
    success: function(data) {
      try {
        var json = jQuery.parseJSON(data);

        if (typeof json == 'object') {
          var bookmark = $('#bookmarks-sortable #bookmark-'+json.BookmarkID);

          if (actionName == 'action_doDeleteBookmark')
            bookmark.remove();
          else
            bookmark.replaceWith(json.Bookmark);

          $('.bookmark-star').replaceWith(json.BookmarkStar);

          update_bookmarks_widget(json.BookmarkWidget);
          reload_memberwidgets();
          open_mfp_popup_ajax(json.BookmarksLink);
        }
      }
      catch(err) {
        form.replaceWith(data);
        $('#Form_EditBookmarkForm fieldset .field :input:visible').focus();
        $('#Form_EditBookmarkForm :input[required]:visible').first().focus();
      }
    }
  });
});