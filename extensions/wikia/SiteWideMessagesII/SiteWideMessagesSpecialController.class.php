<?php
/**
 * SiteWideMessages special page
 * @author grunny
 */

class SiteWideMessagesSpecialController extends WikiaSpecialPageController {

	public function __construct() {
		parent::__construct( 'SiteWideMessages', 'sitewidemessages' );
	}

	public function index() {
		if ( !$this->wg->User->isAllowed( 'sitewidemessages' ) ) {
			$this->displayRestrictionError();
			return false;
		}
	}

}
