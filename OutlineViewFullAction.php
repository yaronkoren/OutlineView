<?php

use MediaWiki\MediaWikiServices;

/**
 * Handles the 'outlineview' action.
 *
 * @author Yaron Koren
 * @ingroup OutlineView
 */

class OutlineViewFullAction extends Action {
	/**
	 * Return the name of the action this object responds to
	 * @return string lowercase
	 */
	public function getName() {
		return 'outlineviewfull';
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
		$outlineViewPage->execute( $pageName, true );
	}

}

