<?php
/**
 * Controller class for SiteWideMessages
 *
 * @author grunny
 */
class SiteWideMessagesController extends WikiaController  {

	public function getUserMessages() {
		wfProfileIn( __METHOD__ );

		$userId = $this->request->getInt( 'userid' );
		$wikiId = $this->wg->CityId;

		if ( $userId !== $this->wg->User->getId() ) {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-incorrectuser' )->plain() );
			wfProfileOut( __METHOD__ );
			return true;
		}

		$messages = ( new SiteWideMessagesHelper() )->getActiveMessagesForUser( $userId );
		$messagesForWiki = [];
		foreach ( $messages['messages'] as $notificationId => $notification ) {
			if (
				( $this->wg->User->isLoggedIn() && $notification['recipient'] == $userId )
				|| (
					( ( $userId === 0 && $this->request->getCookie( "swm-$notificationId" ) === null )
					|| ( $notification['recipient'] == SiteWideMessagesHelper::NOTIFICATION_ALL ) )
					&& ( in_array( 0, $notification['wikismatched'] )
					|| in_array( $wikiId, $notification['wikismatched'] ) )
				)
			) {
				$messagesForWiki[$notificationId] = $notification;
			}
		}
		$this->setVal( 'messages', $messagesForWiki );

		wfProfileOut( __METHOD__ );
	}

	public function getMessageText() {
		wfProfileIn( __METHOD__ );

		$msgId = $this->request->getInt( 'msgid' );

		$messageText = ( new SiteWideMessagesHelper() )->getTextForMessage( $msgId );
		if ( $messageText === false ) {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-nosuchmessage', $msgId )->plain() );
		} else {
			$this->setVal( 'message', $messageText );
		}

		wfProfileOut( __METHOD__ );
	}


	public function dismissMessage() {
		wfProfileIn( __METHOD__ );

		$userId = $this->request->getInt( 'userid' );
		$msgId = $this->request->getInt( 'msgid' );

		if ( $userId !== $this->wg->User->getId() ) {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-incorrectuser' )->plain() );
			wfProfileOut( __METHOD__ );
			return true;
		}

		if ( $this->wg->User->isAnon() ) {
			$this->request->setCookie( 'swm-' . $msgId, 1, time() + 86400 /*24h*/, '/', $this->wg->CookieDomain );
			$this->setVal( 'message', wfMessage( 'sitewidemessages-dismissed' )->plain() );
			wfProfileOut( __METHOD__ );
			return true;
		}

		$result = ( new SiteWideMessagesHelper() )->dismissMessage( $userId, $msgId );

		if ( $result === true ) {
			$this->setVal( 'message', wfMessage( 'sitewidemessages-dismissed' )->plain() );
		} else {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-dismissfailed' )->plain() );
		}

		wfProfileOut( __METHOD__ );
	}

	public function removeMessage() {
		wfProfileIn( __METHOD__ );

		if ( !$this->wg->User->isAllowed( 'sitewidemessages' ) ) {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-permissions' )->plain() );
			wfProfileOut( __METHOD__ );
			return true;
		}

		$msgId = $this->request->getInt( 'msgid' );

		$result = ( new SiteWideMessagesHelper() )->removeMessage( $msgId );

		if ( $result === true ) {
			$this->setVal( 'message', wfMessage( 'sitewidemessages-removed' )->plain() );
		} else {
			$this->setVal( 'error', wfMessage( 'sitewidemessages-error-removefailed' )->plain() );
		}
		wfProfileOut( __METHOD__ );
	}

}
