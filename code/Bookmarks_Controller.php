<?php

class Bookmarks_Controller extends Page_Controller {

	protected
		$currentUserID = null,
		$bookmark = null;

	private static $allowed_actions = array(
		'addBookmark' => '->canAddBookmark',
		'editBookmark' => '->canEditOrDeleteBookmark',
		'saveAllBookmarks',

		'AddBookmarkForm' => '->canAddBookmark',
		'EditBookmarkForm' => '->canEditOrDeleteBookmark'
	);

	private static $url_handlers = array(
		'editBookmark/$ID!' => 'editBookmark',

		'EditBookmarkForm/$ID!' => 'EditBookmarkForm'
	);

	public function init() {
		if (!($this->currentUserID = Member::currentUserID()))
			Security::permissionFailure($this);

		parent::init();
	}

	public function index() {
		if (($form = $this->NullForm()) && $form->Message() && $form->MessageType() == "good") {
			$title = _t('Bookmarks_Controller.BOOKMARKSTITLE', 'Bookmarks');
			$content = '';
		}
		else {
			$title = _t('Bookmarks_Controller.BOOKMARKSTITLE', 'Bookmarks');
			$content = '';
		}

		$outputData = array (
			'Content' => $content,
			'Form' => $form
		);

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		if ($this->request->isAjax())
			$template = 'Bookmarks';
		else
			$template = array('Bookmarks', 'Page');

		return $this->customise($outputData)->renderWith($template);
	}

	public function NullForm() {
		$fields = new FieldList();
		$actions = new FieldList();
		$validator = null;

		return Form::create($this, __FUNCTION__, $fields, $actions, $validator);
	}

	public function addBookmark() {
		$title = _t('Bookmarks_Controller.ADDBOOKMARK.TITLE', 'Add bookmark');
		$content = '';
		$form = $this->AddBookmarkForm();

		$outputData = array (
			'Content' => $content,
			'Form' => $form
		);

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		if ($this->request->isAjax())
			$template = 'Bookmarks_add';
		else
			$template = array('Bookmarks_add', 'Page');

		return $this->customise($outputData)->renderWith($template);
	}

	public function AddBookmarkForm() {
		$bookmark = singleton('Bookmark');

		$fields = $bookmark->getFrontEndFields();

		$title = null;
		if ($currentTitle = $this->CurrentTitle())
			$title = $currentTitle;

		$url = null;
		if ($currentUrl = urldecode($this->CurrentUrl()))
			$url = $currentUrl;

		$fields->push(new HiddenField('CurrentTitle', null, $title));
		$fields->push(new HiddenField('CurrentUrl', null, $url));

		$fields->dataFieldByName('Title')->setValue($title);
		$fields->dataFieldByName('Url')->setValue($url);

		$actions = new FieldList(
			FormAction::create('doAddBookmark', _t('Bookmarks_Controller.ADDBOOKMARK.ACTION', 'Add'))
		);

		$validator = $bookmark->getFrontEndValidator();

		$form = Form::create($this, __FUNCTION__, $fields, $actions, $validator);

		return $form;
	}

	public function doAddBookmark($data, $form) {
		$bookmark = Bookmark::create();

		$bookmark->Title = $data['Title'];
		$bookmark->Url = $data['Url'];
		$bookmark->OwnerID = $this->currentUserID;

		$bookmark->write();

		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKADDED', 'Bookmark added'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'BookmarkID' => $bookmark->ID,
				'Bookmark' => $bookmark->forTemplate()->getValue(),
				'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks' => $this->isCurrentUrlInBookmarks(isset($data['CurrentUrl']) ? $data['CurrentUrl'] : null)))->renderWith('BookmarkStar')->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKADDED', 'Bookmark added'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function editBookmark() {
		$title = _t('Bookmarks_Controller.EDITBOOKMARK.TITLE', 'Edit bookmark');
		$content = '';
		$form = $this->EditBookmarkForm();

		$outputData = array (
			'Content' => $content,
			'Form' => $form
		);

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		if ($this->request->isAjax())
			$template = 'Bookmarks_edit';
		else
			$template = array('Bookmarks_edit', 'Page');

		return $this->customise($outputData)->renderWith($template);
	}

	public function EditBookmarkForm() {
		$bookmark = $this->getCurrentBookmark();

		$fields = $bookmark->getFrontEndFields();

		$actions = new FieldList();

		if ($bookmark->canEdit())
			$actions->push(FormAction::create('doEditBookmark', _t('Bookmarks_Controller.SAVEBOOKMARK.ACTION', 'Save')));

		if ($bookmark->canDelete())
			$actions->push(FormAction::create('doDeleteBookmark', _t('Bookmarks_Controller.DELETEBOOKMARK.ACTION', 'Delete')));

		$validator = $bookmark->getFrontEndValidator();

		$form = Form::create($this, __FUNCTION__, $fields, $actions, $validator);
		$form->loadDataFrom($bookmark);

		$form->fields()->push(new HiddenField('CurrentTitle', null, $this->CurrentTitle()));
		$form->fields()->push(new HiddenField('CurrentUrl', null, urldecode($this->CurrentUrl())));

		return $form;
	}

	public function doEditBookmark($data, $form) {
		$bookmark = $this->getCurrentBookmark();

		$form->saveInto($bookmark);

		$bookmark->write();

		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKSAVED', 'Bookmark saved'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'BookmarkID' => $bookmark->ID,
				'Bookmark' => $bookmark->forTemplate()->getValue(),
				'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks' => $this->isCurrentUrlInBookmarks(isset($data['CurrentUrl']) ? $data['CurrentUrl'] : null)))->renderWith('BookmarkStar')->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKSAVED', 'Bookmark saved'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function doDeleteBookmark($data, $form) {
		$bookmark = $this->getCurrentBookmark();

		$bookmark->delete();

		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKDELETED', 'Bookmark deleted'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'BookmarkID' => $bookmark->OldID,
				'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks' => $this->isCurrentUrlInBookmarks(isset($data['CurrentUrl']) ? $data['CurrentUrl'] : null)))->renderWith('BookmarkStar')->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKDELETED', 'Bookmark deleted'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function saveAllBookmarks() {
		if ($bookmarks = $this->request->postVar('bookmark')) {
			foreach ($bookmarks as $index => $bookmarkID)
				if (($bookmark = DataObject::get_one('Bookmark', array('ID' => $bookmarkID, 'OwnerID' => $this->currentUserID))) && $bookmark->canEdit() && $bookmark->Sort != ++$index) {
					$bookmark->Sort = $index;
					$bookmark->write();
				}

			return $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue();
		}

		return $this->httpError(404);
	}

	public function Link($action = null) {
		return Controller::join_links(Director::baseURL() . 'bookmarks', $action);
	}

	public function FormObjectLink($formName) {
		return $this->Link(Controller::join_links($formName, $this->request->param('ID')));
	}

	private function BookmarksLinkWithParams($data) {
		return Controller::join_links(
			$this->Link(),
			isset($data['CurrentTitle']) ? "?CurrentTitle={$data['CurrentTitle']}" : null,
			isset($data['CurrentUrl']) ? "?CurrentUrl={$data['CurrentUrl']}" : null
		);
	}

	public function canAddBookmark() {
		return singleton('Bookmark')->canCreate();
	}

	public function canEditOrDeleteBookmark() {
		return ($ID = $this->request->param('ID')) && is_numeric($ID) && ($bookmark = DataObject::get_by_id('Bookmark', $ID)) && ($bookmark->canEdit() || $bookmark->canDelete());
	}

	private function getCurrentBookmark() {
		if (!$this->bookmark)
			$this->bookmark = DataObject::get_by_id('Bookmark', $this->request->param('ID'));

		return $this->bookmark;
	}
}