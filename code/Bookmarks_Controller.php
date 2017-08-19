<?php

class Bookmarks_Controller extends Page_Controller {

	private
		$currentUserID = null;

	private static $allowed_actions = array(
		'addBookmark' => '->canAddBookmark',
		'addBookmarkFolder' => '->canAddBookmarkFolder',
		'editBookmark' => '->canEditOrDeleteCurrentBookmark',
		'editBookmarkFolder' => '->canEditOrDeleteCurrentBookmarkFolder',

		'AddBookmarkForm' => '->canAddBookmark',
		'AddBookmarkFolderForm' => '->canAddBookmarkFolder',
		'EditBookmarkForm' => '->canEditOrDeleteCurrentBookmark',
		'EditBookmarkFolderForm' => '->canEditOrDeleteCurrentBookmarkFolder',

		'folder' => '->canViewCurrentBookmarkFolder',

		'saveAllBookmarks',
		'moveBookmarkToBookmarkFolder'
	);

	private static $url_handlers = array(
		'editBookmark/$bookmarkID!' => 'editBookmark',
		'editBookmarkFolder/$bookmarkFolderID!' => 'editBookmarkFolder',

		'EditBookmarkForm/$bookmarkID!' => 'EditBookmarkForm',
		'EditBookmarkFolderForm/$bookmarkFolderID!' => 'EditBookmarkFolderForm',

		'folder/$bookmarkFolderID!/addBookmark' => 'addBookmark',
		'folder/$bookmarkFolderID!/addBookmarkFolder' => 'addBookmarkFolder',
		'folder/$bookmarkFolderID!/editBookmark/$bookmarkID!' => 'editBookmark',
		'folder/$parentBookmarkFolderID!/editBookmarkFolder/$bookmarkFolderID!' => 'editBookmarkFolder',

		'folder/$bookmarkFolderID!/AddBookmarkForm' => 'AddBookmarkForm',
		'folder/$bookmarkFolderID!/AddBookmarkFolderForm' => 'AddBookmarkFolderForm',
		'folder/$bookmarkFolderID!/EditBookmarkForm/$bookmarkID!' => 'EditBookmarkForm',
		'folder/$parentBookmarkFolderID!/EditBookmarkFolderForm/$bookmarkFolderID!' => 'EditBookmarkFolderForm',

		'folder/$bookmarkFolderID!' => 'folder'
	);

	public function init() {
		if (!($this->currentUserID = Member::currentUserID()))
			Security::permissionFailure($this);

		parent::init();

		$title = _t('Bookmarks_Controller.BOOKMARKSTITLE', 'Bookmarks');

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;
	}

	public function index() {
		if (($form = $this->NullForm()) && $form->Message() && $form->MessageType() == "good")
			$content = '';
		else {
			$form = null;
			$content = '';
		}

		if ($this->request->isAjax())
			$template = 'Bookmarks';
		else
			$template = array('Bookmarks', 'Page');

		$outputData = array(
			'Content' => $content,
			'Form' => $form
		);

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

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		$template = array('Bookmarks_add', 'Bookmark_action');

		if (!$this->request->isAjax())
			$template[] = 'Page';

		$outputData = array(
			'Content' => $content,
			'Form' => $this->AddBookmarkForm()
		);

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
		$form->setFormAction($this->Link($form->Name));

		return $form;
	}

	public function doAddBookmark($data, $form) {
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmark = Bookmark::create();

		$bookmark->Title = $data['Title'];
		$bookmark->Url = $data['Url'];
		$bookmark->OwnerID = $this->currentUserID;

		if ($currentBookmarkFolder = $this->getCurrentBookmarkFolder())
			$bookmark->ParentID = $currentBookmarkFolder->ID;

		$bookmark->write();

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKADDED', 'Bookmark added'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
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

	public function addBookmarkFolder() {
		$title = _t('Bookmarks_Controller.ADDBOOKMARKFOLDER.TITLE', 'Add folder');
		$content = '';

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		$template = array('BookmarksFolder_add', 'Bookmark_action');

		if (!$this->request->isAjax())
			$template[] = 'Page';

		$outputData = array(
			'Content' => $content,
			'Form' => $this->AddBookmarkFolderForm()
		);

		return $this->customise($outputData)->renderWith($template);
	}

	public function AddBookmarkFolderForm() {
		$bookmarkFolder = singleton('BookmarkFolder');

		$fields = $bookmarkFolder->getFrontEndFields();

		$title = null;
		if ($currentTitle = $this->CurrentTitle())
			$title = $currentTitle;

		$url = null;
		if ($currentUrl = urldecode($this->CurrentUrl()))
			$url = $currentUrl;

		$fields->push(new HiddenField('CurrentTitle', null, $title));
		$fields->push(new HiddenField('CurrentUrl', null, $url));

		$fields->dataFieldByName('Title');

		$actions = new FieldList(
			FormAction::create('doAddBookmarkFolder', _t('Bookmarks_Controller.ADDBOOKMARKFOLDER.ACTION', 'Add'))
		);

		$validator = $bookmarkFolder->getFrontEndValidator();

		$form = Form::create($this, __FUNCTION__, $fields, $actions, $validator);
		$form->setFormAction($this->Link($form->Name));

		return $form;
	}

	public function doAddBookmarkFolder($data, $form) {
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmarkFolder = BookmarkFolder::create();

		$bookmarkFolder->Title = $data['Title'];
		$bookmarkFolder->OwnerID = $this->currentUserID;

		if ($currentBookmarkFolder = $this->getCurrentBookmarkFolder())
			$bookmarkFolder->ParentID = $currentBookmarkFolder->ID;

		$bookmarkFolder->write();

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKFOLDERADDED', 'Folder added'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'Bookmark' => $bookmarkFolder->forTemplate()->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKFOLDERADDED', 'Folder added'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function editBookmark() {
		$title = _t('Bookmarks_Controller.EDITBOOKMARK.TITLE', 'Edit bookmark');
		$content = '';

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		$template = array('Bookmarks_edit', 'Bookmark_action');

		if (!$this->request->isAjax())
			$template[] = 'Page';

		$outputData = array(
			'Content' => $content,
			'Form' => $this->EditBookmarkForm()
		);

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
		$form->setFormAction($this->Link($form->Name));
		$form->loadDataFrom($bookmark);

		$form->fields()->push(new HiddenField('CurrentTitle', null, $this->CurrentTitle()));
		$form->fields()->push(new HiddenField('CurrentUrl', null, urldecode($this->CurrentUrl())));

		return $form;
	}

	public function doEditBookmark($data, $form) {
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmark = $this->getCurrentBookmark();

		$form->saveInto($bookmark);

		$bookmark->write();

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
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmark = $this->getCurrentBookmark();

		$bookmark->delete();

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

	public function editBookmarkFolder() {
		$title = _t('Bookmarks_Controller.EDITBOOKMARKFOLDER.TITLE', 'Edit folder');
		$content = '';

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		$template = array('BookmarksFolder_edit', 'Bookmark_action');

		if (!$this->request->isAjax())
			$template[] = 'Page';

		$outputData = array(
			'Content' => $content,
			'Form' => $this->EditBookmarkFolderForm()
		);

		return $this->customise($outputData)->renderWith($template);
	}

	public function EditBookmarkFolderForm() {
		$bookmarkFolder = $this->getCurrentBookmarkFolder();

		$fields = $bookmarkFolder->getFrontEndFields();

		$actions = new FieldList();

		if ($bookmarkFolder->canEdit())
			$actions->push(FormAction::create('doEditBookmarkFolder', _t('Bookmarks_Controller.SAVEBOOKMARKFOLDER.ACTION', 'Save')));

		if ($bookmarkFolder->canDelete())
			$actions->push(FormAction::create('doDeleteBookmarkFolder', _t('Bookmarks_Controller.DELETEBOOKMARKFOLDER.ACTION', 'Delete')));

		$validator = $bookmarkFolder->getFrontEndValidator();

		$form = Form::create($this, __FUNCTION__, $fields, $actions, $validator);
		$form->setFormAction($this->Link($form->Name));
		$form->loadDataFrom($bookmarkFolder);

		$form->fields()->push(new HiddenField('CurrentTitle', null, $this->CurrentTitle()));
		$form->fields()->push(new HiddenField('CurrentUrl', null, urldecode($this->CurrentUrl())));

		return $form;
	}

	public function doEditBookmarkFolder($data, $form) {
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmarkFolder = $this->getCurrentBookmarkFolder();

		$form->saveInto($bookmarkFolder);

		$bookmarkFolder->write();

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKFOLDERSAVED', 'Folder saved'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'BookmarkID' => $bookmarkFolder->ID,
				'Bookmark' => $bookmarkFolder->forTemplate()->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKFOLDERSAVED', 'Folder saved'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function doDeleteBookmarkFolder($data, $form) {
		$bookmarksLink = $this->BookmarksLinkWithParams($data);

		$bookmarkFolder = $this->getCurrentBookmarkFolder();

		$bookmarkFolder->delete();

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => _t('Bookmarks_Controller.BOOKMARKFOLDERDELETED', 'Folder deleted'),
				'Type' => 'good',
				'BookmarksLink' => $bookmarksLink,
				'BookmarkID' => $bookmarkFolder->OldID,
				'BookmarkWidget' => $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage(_t('Bookmarks_Controller.BOOKMARKFOLDERDELETED', 'Folder deleted'), 'good');

			return $this->redirect($bookmarksLink);
		}
	}

	public function folder() {
		return $this->index();
	}

	public function saveAllBookmarks() {
		if ($this->request->isAjax()) {
			if ($bookmarks = $this->request->postVar('bookmarks')) {
				foreach ($bookmarks as $index => $bookmarkID)
					if (($bookmark = DataObject::get_one('Bookmark', array('ID' => $bookmarkID, 'OwnerID' => $this->currentUserID))) && $bookmark->canEdit() && $bookmark->Sort != ++$index) {
						$bookmark->Sort = $index;
						$bookmark->write();
					}
			}

			return $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue();
		}
		else
			return $this->httpError(404);
	}

	public function moveBookmarkToBookmarkFolder() {
		if ($this->request->isAjax()) {
			if (($postVars = $this->request->postVars()) && isset($postVars['newParentID']) && is_numeric($postVars['newParentID'])
			&& ($currentBookmarkID = $this->request->postVar('currentBookmarkID')) && is_numeric($currentBookmarkID)) {
				if (($bookmark = DataObject::get_one('Bookmark', array('ID' => $currentBookmarkID, 'OwnerID' => $this->currentUserID))) && $bookmark->canEdit()) {
					$oldParentID = $bookmark->ParentID;
					$newParentID = $postVars['newParentID'];

					if (!$newParentID || ($bookmarkFolder = DataObject::get_one('BookmarkFolder', array('ID' => $newParentID, 'OwnerID' => $this->currentUserID))) && $bookmarkFolder->canEdit()) {
						$bookmark->ParentID = $newParentID;
						$bookmark->Sort = ($maxSort = Bookmark::get()->filter(array('OwnerID' => $this->currentUserID, 'ParentID' => $newParentID))->max('Sort')) ? ++$maxSort : 1;
						$bookmark->write();
					}

					if ($bookmarks = Bookmark::get()->filter(array('OwnerID' => $this->currentUserID, 'ParentID' => $oldParentID))) {
						foreach ($bookmarks as $index => $bookmark) {
							if ($bookmark->canEdit() && $bookmark->Sort != ++$index) {
								$bookmark->Sort = $index;
								$bookmark->write();
							}
						}
					}
				}
			}

			return $this->customise(array('Bookmarks' => singleton('BookmarksWidget')->getBookmarks()))->renderWith('BookmarksWidget')->getValue();
		}
		else
			return $this->httpError(404);
	}

	public function Link($action = null) {
		$finalAction = null;

		$currentBookmark = null;
		if ($action && ($currentBookmark = $this->getCurrentBookmark()))
			$finalAction = "$action/$currentBookmark->ID";

		if ($currentBookmarkFolder = $this->getCurrentBookmarkFolder()) {
			if ($currentBookmark)
				$finalAction = "folder/$currentBookmarkFolder->ID/$action/$currentBookmark->ID";
			elseif (($currentParentBookmarkFolder = $this->getCurrentParentBookmarkFolder()) && $action)
				$finalAction = "folder/$currentParentBookmarkFolder->ID/$action/$currentBookmarkFolder->ID";
			elseif ($this->request->param('Action') == 'folder') {
				if ($currentParentBookmarkFolder)
					$finalAction = "folder/$currentParentBookmarkFolder->ID";
				else {
					$finalAction = "folder/$currentBookmarkFolder->ID";

					if ($action)
						$finalAction .= "/$action";
				}

			}
			elseif ($action)
				$finalAction = "$action/$currentBookmarkFolder->ID";
		}

		return Controller::join_links(Director::baseURL() . 'bookmarks', $finalAction ?: $action);
	}

	public function FormObjectLink($formName) {
		return Controller::join_links(Director::baseURL() . 'bookmarks', $formName);
	}

	private function BookmarksLinkWithParams($data) {
		return Controller::join_links(
			$this->Link(),
			isset($data['CurrentTitle']) ? "?CurrentTitle={$data['CurrentTitle']}" : null,
			isset($data['CurrentUrl']) ? "?CurrentUrl={$data['CurrentUrl']}" : null
		);
	}

	protected function canAddBookmark() {
		return singleton('Bookmark')->canCreate() && !$this->request->param('ID')
		&& (!$this->getCurrentBookmarkFolder(true) || $this->canViewCurrentBookmarkFolder());
	}

	protected function canAddBookmarkFolder() {
		return singleton('BookmarkFolder')->canCreate() && !$this->request->param('ID')
		&& (!$this->getCurrentBookmarkFolder(true) || $this->canViewCurrentBookmarkFolder());
	}

	protected function canEditOrDeleteCurrentBookmark() {
		return ($bookmark = $this->getCurrentBookmark()) && ($bookmark->canEdit() || $bookmark->canDelete())
		&& (!(($parentFolder = $bookmark->Parent()) && $parentFolder->exists()) ||
				($currentBookmarkFolder = $this->getCurrentBookmarkFolder()) && $currentBookmarkFolder->ID == $parentFolder->ID && $currentBookmarkFolder->canView());
	}

	protected function canEditOrDeleteCurrentBookmarkFolder() {
		return ($bookmarkFolder = $this->getCurrentBookmarkFolder()) && ($bookmarkFolder->canEdit() || $bookmarkFolder->canDelete())
		&& (!(($parentFolder = $bookmarkFolder->Parent()) && $parentFolder->exists()) ||
				($currentParentBookmarkFolder = $this->getCurrentParentBookmarkFolder()) && $currentParentBookmarkFolder->ID == $parentFolder->ID && $currentParentBookmarkFolder->canView());
	}

	protected function canViewCurrentBookmarkFolder() {
		return ($currentBookmarkFolder = $this->getCurrentBookmarkFolder()) && $currentBookmarkFolder->canView();
	}

	private function getCurrentObject($attributeName, $className, $checkOnlyParam = false) {
		if ($paramID = $this->request->param("{$attributeName}ID")) {
			if ($checkOnlyParam)
				return true;
			elseif (!is_numeric($paramID))
				$this->$attributeName = null;
			elseif (!$this->$attributeName || $this->$attributeName->ID != $paramID)
				$this->$attributeName = DataObject::get_one($className, array('ID' => $paramID, 'ClassName' => $className));
		}
		elseif ($checkOnlyParam)
			return false;
		else
			$this->$attributeName = null;

		return $this->$attributeName;
	}

	private function getCurrentBookmark($checkOnlyParam = false) {
		return $this->getCurrentObject('bookmark', 'Bookmark', $checkOnlyParam);
	}

	protected function getCurrentBookmarkFolder($checkOnlyParam = false) {
		return $this->getCurrentObject('bookmarkFolder', 'BookmarkFolder', $checkOnlyParam);
	}

	private function getCurrentParentBookmarkFolder($checkOnlyParam = false) {
		return $this->getCurrentObject('parentBookmarkFolder', 'BookmarkFolder', $checkOnlyParam);
	}

	public function BookmarksBreadcrumbs() {
		if ($bookmarkFolder = $this->getCurrentBookmarkFolder()) {
			$crumbs = array($bookmarkFolder);

			while (($bookmarkFolder = $bookmarkFolder->Parent()) && $bookmarkFolder->exists())
				$crumbs[] = $bookmarkFolder;

			$defaultBookmark = new BookmarkFolder();
			$defaultBookmark->Title = _t('Bookmarks_Controller.ALLBOOKMARKS', 'All bookmarks');

			$crumbs[] = $defaultBookmark;

			return $this->customise(array(
				'BookmarkFolders' => new ArrayList(array_reverse($crumbs))
			))->renderWith('BookmarksBreadcrumbs');
		}
	}
}