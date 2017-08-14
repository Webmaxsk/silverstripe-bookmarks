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

function update_bookmarks_widget(data) {
  $('.BookmarksWidget .bookmark-list').replaceWith(data);
}

// silverstripe-member-widgets module
function reload_memberwidgets() {
  var memberWidgetsIsotope = $('#memberwidgets-sortable.memberwidgets-isotope');

  if (memberWidgetsIsotope.length)
    memberWidgetsIsotope.isotope('reloadItems').isotope({
      sortBy: 'original-order'
    });
}

function sortable_bookmarks(saveAllBookmarksLink) {
  $('#bookmarks-sortable').sortable({
    axis: 'y',
    cursor: 'move',
    helper: 'clone',
    placeholder: 'bookmark-placeholder',
    start: function(event, ui) {
      ui.item.addClass('bookmark-sort-active').show();
    },
    change: function(event, ui) {
      var currentID = ui.item.data('id');
      var prevID = ui.placeholder.prev().data('id');
      var nextID = ui.placeholder.next(':not(.ui-sortable-helper)').data('id');

      if (currentID != prevID && currentID != nextID)
        ui.placeholder.addClass('bookmark-placeholder-highlighted');
      else
        ui.placeholder.removeClass('bookmark-placeholder-highlighted');
    },
    stop: function(event, ui) {
      ui.item.removeClass('bookmark-sort-active');
    },
    update: function(event, ui) {
      $.ajax({
        data: $(this).sortable('serialize'),
        type: 'POST',
        url: saveAllBookmarksLink,
        success: function(data) {
          update_bookmarks_widget(data);
        }
      });
    }
  });
}