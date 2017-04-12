if (typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
  if (typeof(console) != 'undefined') console.error('Trieda ss.i18n nie je definovaná!');
} else {
  ss.i18n.addDictionary('sk', {
    "Bookmark.PROCESSING": "Prebieha odosielanie...",
    "Bookmarks.LOADING": "Prebieha načítanie...",
    "Bookmarks.CLOSE": "Zatvoriť (Esc)"
  });
}