$(document).on('click', '.showBookmarks, .mfp-content #member_bookmarks .ajax-popup-link, .mfp-content #member_bookmarks .ajax-folder-link, .WidgetHolder.BookmarksWidget .bookmark-list .ajax-folder-link', function() {
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

function sortable_and_droppable_bookmarks(saveAllBookmarksLink, moveBookmarkToBookmarkFolderLink) {
  var bookmarksSortable = $('#bookmarks-sortable');
  var bookmarkFoldersDroppable = $('#member_bookmarks .bookmarkFolder');

  var placeholder = null;
  var widget = null;

  var sortable_enabled = true;

  bookmarksSortable.sortable({
    appendTo: document.body,
    cursor: 'move',
    helper: 'clone',
    placeholder: 'bookmark-placeholder',
    zIndex: 9999,
    start: function(event, ui) {
      ui.item.addClass('bookmark-sort-active').show();
    },
    change: function(event, ui) {
      placeholder = ui.placeholder;

      sortable_change(ui.item);
    },
    stop: function(event, ui) {
      ui.item.removeClass('bookmark-sort-active');
    },
    update: function(event, ui) {
      if (sortable_enabled)
        $.ajax({
          data: $(this).sortable('serialize', {key: 'bookmarks[]'}),
          type: 'POST',
          url: saveAllBookmarksLink,
          success: function(data) {
            update_bookmarks_widget(data);
          }
        });
    }
  });

  bookmarkFoldersDroppable.droppable({
    accept: '.bookmark, .bookmarkFolder',
    hoverClass: 'bookmarkFolder-hovered',
    tolerance: 'pointer',
    over: function(event, ui) {
      sortable_enabled = false;

      widget = $(this).droppable('widget');

      if (placeholder)
        placeholder.removeClass('bookmark-placeholder-highlighted');
    },
    out: function(event, ui) {
      sortable_enabled = true;

      if (widget.data('id') == $(this).droppable('widget').data('id'))
        widget = null;

      sortable_change(ui.draggable);
    },
    drop: function(event, ui) {
      var bookmarkID = ui.draggable.data('id');
      var bookmarkFolderID = $(this).droppable('widget').data('id');

      if (bookmarkID != null && bookmarkFolderID != null) {
        ui.draggable.remove();

        $.ajax({
          data: {newParentID: bookmarkFolderID, currentBookmarkID: bookmarkID},
          type: 'POST',
          url: moveBookmarkToBookmarkFolderLink,
          success: function(data) {
            widget = null;

            update_bookmarks_widget(data);
            reload_memberwidgets();
          },
          complete: function() {
            sortable_enabled = true;
          }
        });
      }
      else
        sortable_enabled = true;
    }
  });

  var sortable_change = function(currentElement) {
    if (!widget && placeholder) {
      var currentID = currentElement.data('id');
      var prevID = placeholder.prev().data('id');
      var nextID = placeholder.next(':not(.ui-sortable-helper)').data('id');

      if (currentID != prevID && currentID != nextID)
        placeholder.addClass('bookmark-placeholder-highlighted');
      else
        placeholder.removeClass('bookmark-placeholder-highlighted');
    }
  }
}