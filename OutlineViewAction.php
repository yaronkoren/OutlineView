<?php

use MediaWiki\MediaWikiServices;

/**
 * Handles the 'outlineview' action.
 *
 * @author Yaron Koren
 * @ingroup OutlineView
 */

class OutlineViewAction extends Action {
	/**
	 * Return the name of the action this object responds to
	 * @return string lowercase
	 */
	public function getName() {
		return 'outlineview';
	}

	/**
	 * The main action entry point. Do all output for display and send it
	 * to the context output.
	 * $this->getOutput(), etc.
	 */
	public function show() {
		$title = $this->page->getTitle();
		$pageName = $title->getFullText();

		$outlineViewPage = new SpecialOutlineView();
		$outlineViewPage->execute( $pageName );
	}

	/**
	 * Adds an "action" (i.e., a tab) to show the outline view.
	 *
	 * @param Title $obj
	 * @param array &$links
	 * @return bool
	 */
	public static function displayTab( $obj, &$links ) {
		// @todo - only add this for some pages?

		$links['actions']['outlineview'] = [
			'class' => false,
			'text' => $obj->msg( 'outlineview' )->text(),
			'href' => $obj->getTitle()->getLocalUrl( [ 'action' => 'outlineview' ] )
		];

		return true;
	}

}

