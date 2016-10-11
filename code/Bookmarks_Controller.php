<?php

class Bookmarks_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'addBookmark',
		'editBookmark' => '->canEditBookmark',
		'saveAll',

		'AddBookmarkForm',
		'EditBookmarkForm'
	);

	private static $url_handlers = array(
		'editBookmark/$ID' => 'editBookmark'
	);

	public function init() {
		if (!Member::currentUserID())
			Security::permissionFailure($this);

		parent::init();
	}

	public function index() {
		if (($form = $this->NullForm()) && $form->Message() && $form->MessageType()=="good") {
			$title = 'Záložky';
			$content = '';
		}
		else {
			$title = 'Záložky';
			$content = '';
		}

		$outputData = array (
			'Content' => $content,
			'Form' => $form
		);

		$this->Title = $title;
		$this->MenuTitle = $title;
		$this->MetaTitle = $title;

		if ($this->request->isAjax()) {
			if ($currentTitle = $this->request->getVar('CurrentTitle'))
				$outputData['CurrentTitle'] = $currentTitle;

			if ($currentUrl = $this->request->getVar('CurrentUrl'))
				$outputData['CurrentUrl'] = $currentUrl;

			return $this->customise($outputData)->renderWith(array("Bookmarks"));
		}
		else
			return $this->customise($outputData)->renderWith(array("Bookmarks","Page"));
	}

	public function NullForm() {
		$fields = new FieldList();
		$actions = new FieldList();
		$validator = null;

		return Form::create($this, 'NullForm', $fields, $actions, $validator);
	}

	public function addBookmark() {
		$title = 'Pridať záložku';
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
			return $this->customise($outputData)->renderWith(array("Bookmarks_add"));
		else
			return $this->customise($outputData)->renderWith(array("Bookmarks_add","Page"));
	}

	public function AddBookmarkForm() {
		$bookmark = singleton('Bookmark');

		$fields = $bookmark->getFrontEndFields();

		$title = null;
		$url = null;

		if ($currentTitle = $this->request->getVar('CurrentTitle'))
			$title = $currentTitle;

		if ($currentUrl = $this->request->getVar('CurrentUrl'))
			$url = $currentUrl;

		$fields->push(new HiddenField('CurrentTitle',null,$title));
		$fields->push(new HiddenField('CurrentUrl',null,$url));

		$fields->dataFieldByName('Title')->setValue($title);
		$fields->dataFieldByName('Url')->setValue($url);

		$actions = new FieldList(
			FormAction::create('doAddBookmark', 'Pridať')
		);

		$validator = $bookmark->getFrontEndValidator();

		$form = Form::create($this, 'AddBookmarkForm', $fields, $actions, $validator);

		return $form;
	}

	public function doAddBookmark($data, $form) {
		$bookmark = Bookmark::create();

		$bookmark->Title = $data['Title'];
		$bookmark->Url = $data['Url'];
		$bookmark->MemberID = Member::currentUserID();
		
		$lastID = 0;
		if (($bookmarks = Bookmark::get()) && $bookmarks->count())
			$lastID = $bookmarks->last()->Sort;

		$bookmark->Sort = ++$lastID;

		$bookmark->write();

		if ($this->request->isAjax()) {
			$BookmarksLink = $this->Link();
			if (isset($data['CurrentTitle']))
				$BookmarksLinkParams[] = "CurrentTitle={$data['CurrentTitle']}";

			$currentUrl = null;
			if (isset($data['CurrentUrl'])) {
				$currentUrl = $data['CurrentUrl'];
				$BookmarksLinkParams[] = "CurrentUrl={$currentUrl}";
			}

			if (count($BookmarksLinkParams))
				$BookmarksLink .= "?".implode('&', $BookmarksLinkParams);

			return json_encode(array(
				'Message' => 'Záložka pridaná',
				'Type' => 'good',
				'BookmarksLink' => $BookmarksLink,
				'BookmarkID' => $bookmark->ID,
				'Bookmark' => $bookmark->forTemplate()->getValue(),
				'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks'=>$this->isCurrentUrlInBookmarks($currentUrl)))->renderWith(array('BookmarkStar'))->getValue(),
				'BookmarkWidget' => $this->customise(array('Bookmarks'=>singleton('BookmarksWidget')->getBookmarks()))->renderWith(array('BookmarksWidget'))->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage('Záložka pridaná', 'good');

			return $this->redirect($this->Link());
		}
	}

	public function editBookmark() {
		$title = 'Upraviť záložku';
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
			return $this->customise($outputData)->renderWith(array("Bookmarks_edit"));
		else
			return $this->customise($outputData)->renderWith(array("Bookmarks_edit","Page"));
	}

	public function EditBookmarkForm() {
		$bookmark = DataObject::get_by_id('Bookmark',($ID = $this->request->param('ID')) ? $ID : $this->request->postVar('ID'));

		$fields = $bookmark->getFrontEndFields();
		$fields->push(new HiddenField('ID'));

		$actions = new FieldList(
			FormAction::create('doEditBookmark', 'Uložiť'),
			FormAction::create('doDeleteBookmark', 'Vymazať')
		);

		$validator = $bookmark->getFrontEndValidator();

		$form = Form::create($this, 'EditBookmarkForm', $fields, $actions, $validator);
		$form->loadDataFrom($bookmark);

		$form->fields()->push(new HiddenField('CurrentTitle',null,$this->request->getVar('CurrentTitle')));
		$form->fields()->push(new HiddenField('CurrentUrl',null,$this->request->getVar('CurrentUrl')));

		return $form;
	}

	public function doEditBookmark($data, $form) {
		if (isset($data['ID']) && ($ID = $data['ID']) && is_numeric($ID) && ($bookmark = DataObject::get_by_id('Bookmark',$ID)) && $bookmark->canEditCurrent()) {
			
			$form->saveInto($bookmark);

			$bookmark->write();

			if ($this->request->isAjax()) {
				$BookmarksLink = $this->Link();
				if (isset($data['CurrentTitle']))
					$BookmarksLinkParams[] = "CurrentTitle={$data['CurrentTitle']}";

				$currentUrl = null;
				if (isset($data['CurrentUrl'])) {
					$currentUrl = $data['CurrentUrl'];
					$BookmarksLinkParams[] = "CurrentUrl={$currentUrl}";
				}

				if (count($BookmarksLinkParams))
					$BookmarksLink .= "?".implode('&', $BookmarksLinkParams);

				return json_encode(array(
					'Message' => 'Záložka uložená',
					'Type' => 'good',
					'BookmarksLink' => $BookmarksLink,
					'BookmarkID' => $bookmark->ID,
					'Bookmark' => $bookmark->forTemplate()->getValue(),
					'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks'=>$this->isCurrentUrlInBookmarks($currentUrl)))->renderWith(array('BookmarkStar'))->getValue(),
					'BookmarkWidget' => $this->customise(array('Bookmarks'=>singleton('BookmarksWidget')->getBookmarks()))->renderWith(array('BookmarksWidget'))->getValue()
				));
			}
			else {
				$this->NullForm()->sessionMessage('Záložka uložená', 'good');

				return $this->redirect($this->Link());
			}
		}

		return $this->redirectBack();
	}

	public function doDeleteBookmark($data, $form) {
		if (isset($data['ID']) && ($ID = $data['ID']) && is_numeric($ID) && ($bookmark = DataObject::get_by_id('Bookmark',$ID)) && $bookmark->canEditCurrent()) {
			
			$bookmark->delete();

			if ($this->request->isAjax()) {
				$BookmarksLink = $this->Link();
				if (isset($data['CurrentTitle']))
					$BookmarksLinkParams[] = "CurrentTitle={$data['CurrentTitle']}";

				$currentUrl = null;
				if (isset($data['CurrentUrl'])) {
					$currentUrl = $data['CurrentUrl'];
					$BookmarksLinkParams[] = "CurrentUrl={$currentUrl}";
				}

				if (count($BookmarksLinkParams))
					$BookmarksLink .= "?".implode('&', $BookmarksLinkParams);

				return json_encode(array(
					'Message' => 'Záložka vymazaná',
					'Type' => 'good',
					'BookmarksLink' => $BookmarksLink,
					'BookmarkID' => $bookmark->OldID,
					'BookmarkStar' => $this->customise(array('isCurrentUrlInBookmarks'=>$this->isCurrentUrlInBookmarks($currentUrl)))->renderWith(array('BookmarkStar'))->getValue(),
					'BookmarkWidget' => $this->customise(array('Bookmarks'=>singleton('BookmarksWidget')->getBookmarks()))->renderWith(array('BookmarksWidget'))->getValue()
				));
			}
			else {
				$this->NullForm()->sessionMessage('Záložka vymazaná', 'good');

				return $this->redirect($this->Link());
			}
		}

		return $this->redirectBack();
	}

	public function saveAll() {
		if ($bookmarks = $this->request->postVar('bookmark')) {
			$i = 0;

			foreach ($bookmarks as $id) {
				if ($bookmark = DataObject::get_by_id('Bookmark',$id)) {
					$bookmark->Sort = $i++;
					$bookmark->write();
				}
			}
		}
	}

	public function Link($action = null) {
	    return Controller::join_links(Director::baseURL().'bookmarks', $action);
	}

	public function canEditBookmark() {
		return ($ID = $this->request->param('ID')) && is_numeric($ID) && ($bookmark = DataObject::get_by_id('Bookmark',$ID)) && $bookmark->canEditCurrent();
	}
}