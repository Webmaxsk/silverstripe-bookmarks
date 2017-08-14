$(document).on('click', ".mfp-content #Form_EditBookmarkFolderForm_action_doEditBookmarkFolder, .mfp-content #Form_EditBookmarkFolderForm_action_doDeleteBookmarkFolder", function(event) {
  event.preventDefault();

  var form = $("#Form_EditBookmarkFolderForm");
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

          if (actionName == 'action_doDeleteBookmarkFolder')
            bookmark.remove();
          else
            bookmark.replaceWith(json.Bookmark);

          update_bookmarks_widget(json.BookmarkWidget);
          reload_memberwidgets();
          open_mfp_popup_ajax(json.BookmarksLink);
        }
      }
      catch(err) {
        form.replaceWith(data);
        $('#Form_EditBookmarkFolderForm fieldset .field :input:visible').focus();
        $('#Form_EditBookmarkFolderForm :input[required]:visible').first().focus();
      }
    }
  });
});