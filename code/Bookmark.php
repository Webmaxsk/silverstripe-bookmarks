<?php

class Bookmark extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(55)',
		'Url' => 'Varchar(2083)',

		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Owner' => 'Member'
	);

	private static $default_sort = 'Sort Asc';

	public function fieldLabels($includerelations = true) {
		$cacheKey = $this->class . '_' . $includerelations;

		if(!isset(self::$_cache_field_labels[$cacheKey])) {
			$labels = parent::fieldLabels($includerelations);
			$labels['Title'] = _t('Bookmark.TITLE', 'Title');
			$labels['Url'] = _t('Bookmark.URL', 'Url');
			$labels['Sort'] = _t('Bookmark.SORT', 'Sort');

			if($includerelations) {
				$labels['Owner'] = _t('Member.SINGULARNAME', 'Member');
			}

			self::$_cache_field_labels[$cacheKey] = $labels;
		}

		return self::$_cache_field_labels[$cacheKey];
	}

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

	public function getFrontEndFields($params = null) {
		$fields = new FieldList(array(
			$this->dbObject('Title')->scaffoldFormField(_t('Bookmark.TITLE', 'Title')),
			$this->dbObject('Url')->scaffoldFormField(_t('Bookmark.URL', 'Url'))
		));

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
		return ($member || ($member = Member::currentUser())) && $member->ID == $this->OwnerID;
	}

	protected function isAdmin() {
		return Permission::check('ADMIN');
	}

	public function canView($member = null) {
		return $this->isOwner($member) || $this->isAdmin() || !$this->exists();
	}

	public function canEdit($member = null) {
		return $this->isOwner($member) || $this->isAdmin() || !$this->exists();
	}

	public function canDelete($member = null) {
		return $this->isOwner($member) || $this->isAdmin();
	}

	public function canCreate($member = null) {
		return true;
	}

	public function BookmarkHolder($currentTitle=null, $currentUrl=null) {
		return $currentTitle!=null || $currentUrl!=null ? $this->customise(array('CurrentTitle'=>$currentTitle,'CurrentUrl'=>$currentUrl))->renderWith("BookmarkHolder") : $this->renderWith("BookmarkHolder");
    }

	public function forTemplate() {
		return $this->BookmarkHolder();
	}
}