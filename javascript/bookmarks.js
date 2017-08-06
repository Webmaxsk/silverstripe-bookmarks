$(document).on('click', '.showBookmarks, .mfp-content #member_bookmarks .ajax-popup-link', function() {
  open_mfp_popup_ajax($(this).attr('href'));

  return false;
});

function open_mfp_popup_ajax(items_source) {
  $.magnificPopup.open({
    callbacks: {
      open: function() {
        return $('html').addClass('popup-opened');
      },
      close: function() {
        return $('html').removeClass('popup-opened');
      }
    },
    closeBtnInside: true,
    closeOnBgClick: false,
    enableEscapeKey: false,
    fixedContentPos: true,
    items: {
      src: items_source
    },
    type: 'ajax',
    tClose: ss.i18n._t('Bookmarks.CLOSE', 'Close (Esc)'),
    tLoading: ss.i18n._t('Bookmarks.LOADING', 'Loading...')
  });
}

function sortable_bookmarks(saveAllBookmarksLink) {
  $('#bookmarks-sortable').sortable({
    cursor: 'move',
    placeholder: 'bookmark-placeholder',
    forcePlaceholderSize: true,
    axis: 'y',
    stop: function(event, ui) {
      $.ajax({
        data: $(this).sortable('serialize'),
        type: 'POST',
        url: saveAllBookmarksLink,
        success: function(data) {
          $('.BookmarksWidget .bookmark-list').replaceWith(data);
        }
      });
    }
  });
}