<?php

class Bookmarks_Validator extends RequiredFields {

	/* fix for betterbuttons */
	private function canValid() {
		if (class_exists('BetterButton'))
			return $this->form->buttonClicked() && $this->form->buttonClicked()->actionName() != "doDelete";
		else
			return true;
	}
	/* end fix */

	public function php($data) {
		$parent_validator = true;

		if ($this->canValid()) {
			$parent_validator = parent::php($data);
		}

		return $parent_validator;
	}
}