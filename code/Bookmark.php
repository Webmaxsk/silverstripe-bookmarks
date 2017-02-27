<?php

class Bookmark extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(20)',
		'Url' => 'Varchar(2083)',

		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Member' => 'Member'
	);

	private static $default_sort = 'Sort Asc';

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if(
			$this->Url
			&& !parse_url($this->Url, PHP_URL_SCHEME)
			&& !preg_match('#^//#', $this->Url)
			&& !preg_match('#^/#', $this->Url)
		) {
			$this->Url = 'http://' . $this->Url;
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');

		return $fields;
	}

	public function getFrontEndFields($emailOnly = false) {
		$fields = new FieldList();

		$fields->push(TextField::create('Title'));
		$fields->push(TextField::create('Url'));

		return $fields;
	}

	public function getFrontEndValidator() {
		$required = array();

		$required[] = 'Title';
		$required[] = 'Url';

		return Bookmarks_Validator::create($required);
	}

	public function getCMSValidator() {
		return $this->getFrontEndValidator();
	}

	public function getName() {
		return $this->Title;
	}

	public function editBookmarkLink() {
		return singleton('Bookmarks_Controller')->Link("editBookmark/{$this->ID}");
	}

	protected function isOwner($member = null) {
		return ($member || ($member = Member::currentUser())) && $member->ID == $this->MemberID;
	}

	protected function isAdmin() {
		return Permission::check('ADMIN');
	}

	public function canEditCurrent() {
		return $this->canEdit(Member::currentUser());
	}

	public function canEdit($member = null) {
		return $this->IsOwner($member) || $this->isAdmin();
	}

	public function BookmarkHolder($currentTitle=null, $currentUrl=null) {
		return $currentTitle!=null || $currentUrl!=null ? $this->customise(array('CurrentTitle'=>$currentTitle,'CurrentUrl'=>$currentUrl))->renderWith("BookmarkHolder") : $this->renderWith("BookmarkHolder");
    }

	public function forTemplate() {
		return $this->BookmarkHolder();
	}
}