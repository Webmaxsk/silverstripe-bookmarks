<?php

class Bookmark extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(55)',
		'Url' => 'Varchar(2083)',

		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Owner' => 'Member',
		'Parent' => 'BookmarkFolder'
	);

	private static $searchable_fields = array(
		'Title',
		'Url',
		'Owner.ID'
	);

	private static $summary_fields = array(
		'i18n_singular_name',
		'Title',
		'Url',
		'Owner.Name'
	);

	private static $default_sort = 'Sort Asc';

	public function fieldLabels($includerelations = true) {
		$cacheKey = $this->class . '_' . $includerelations;

		if (!isset(self::$_cache_field_labels[$cacheKey])) {
			$labels = parent::fieldLabels($includerelations);
			$labels['i18n_singular_name'] = _t('Bookmark.TYPE', 'Type');
			$labels['Title'] = _t('Bookmark.TITLE', 'Title');
			$labels['Url'] = _t('Bookmark.URL', 'Url');
			$labels['Sort'] = _t('Bookmark.SORT', 'Sort');
			$labels['Owner.ID'] = _t('Bookmark.OWNER', 'Owner');
			$labels['Owner.Name'] = _t('Bookmark.OWNER', 'Owner');

			if ($includerelations) {
				$labels['Owner'] = _t('Bookmark.OWNER', 'Owner');
				$labels['Parent'] = _t('BookmarkFolder.SINGULARNAME', 'Folder');
			}

			self::$_cache_field_labels[$cacheKey] = $labels;
		}

		return self::$_cache_field_labels[$cacheKey];
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->IsCreating = !$this->ID;

		if ($this->Url
		&& substr($this->Url, 0, 1) !== '/'
		&& ($urlParts = parse_url($this->Url)) && empty($urlParts['scheme']))
			$this->Url = 'http://' . $this->Url;

		if ($this->IsCreating && $this->OwnerID)
			$this->Sort = ($maxSort = Bookmark::get()->filter(array(
				'OwnerID' => $this->OwnerID,
				'ParentID' => $this->ParentID ?: 0
			))->max('Sort')) ? ++$maxSort : 1;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		if ($this->ID) {
			$fields->replaceField('OwnerID', $fields->dataFieldByName('OwnerID')->performReadonlyTransformation());
			$fields->replaceField('ParentID', $fields->dataFieldByName('ParentID')->performReadonlyTransformation());
		}

		if (class_exists('GridFieldSortableRows') || class_exists('GridFieldOrderableRows'))
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
		$required = array(
			'Title',
			'Url'
		);

		return Bookmarks_Validator::create($required);
	}

	public function getCMSValidator() {
		return $this->getFrontEndValidator();
	}

	public function getName() {
		return $this->Title;
	}

	public function editLink() {
		return Controller::curr()->Link("editBookmark/{$this->ID}");
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

	public function BookmarkHolder($currentTitle = null, $currentUrl = null) {
		return $currentTitle != null || $currentUrl != null ? $this->customise(array('CurrentTitle' => $currentTitle, 'CurrentUrl' => $currentUrl))->renderWith(__FUNCTION__) : $this->renderWith(__FUNCTION__);
	}

	public function forTemplate() {
		return $this->BookmarkHolder();
	}
}