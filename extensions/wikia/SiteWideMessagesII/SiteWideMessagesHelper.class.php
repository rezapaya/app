<?php

class SiteWideMessagesHelper extends WikiaModel {

	const MESSAGE_ACTIVE = 0;
	const MESSAGE_REMOVED = 1;
	const NOTIFICATION_UNREAD = 0;
	const NOTIFICATION_DISMISSED = 1;
	const NOTIFICATION_ALL = -1;

	public function getAllActiveMessages() {
		wfProfileIn( __METHOD__ );

		$messages = $this->wg->Memc->get( "swm::messages::active" );

		if ( empty( $messages ) ) {
			$messages = [];
			$dbr = $this->getSharedDB();
			$quotedTimeStamp = $dbr->addQuotes( wfTimestamp( TS_DB ) );

			$res = $dbr->select(
				'swm_text',
				[ 'msg_id', 'msg_text', 'msg_lang' ],
				[
					'msg_removed' => self::MESSAGE_ACTIVE,
					'msg_start < ' . $quotedTimeStamp,
					'msg_expire > ' . $quotedTimeStamp
				],
				__METHOD__
			);

			if ( $res === false ) {
				wfProfileOut( __METHOD__ );
				return false;
			}

			foreach ( $res as $row ) {
				$messages[$row->msg_id] = $row->msg_text;
			}
			$this->wg->Memc->set( "swm::messages::active", $messages, 60 * 60 );
		}

		wfProfileOut( __METHOD__ );
		return $messages;
	}

	public function getTextForMessage( $msgId, $parse = true ) {
		wfProfileIn( __METHOD__ );

		// Paranoia
		$msgId = (int)$msgId;

		$messageText = $this->wg->Memc->get( "swm::message::$msgId" );

		if ( empty( $messageText ) ) {
			$dbr = $this->getSharedDB();

			$res = $dbr->selectRow(
				'swm_text',
				[ 'msg_text' ],
				[
					'msg_id' => $msgId,
				],
				__METHOD__
			);

			if ( $res === false ) {
				wfProfileOut( __METHOD__ );
				return false;
			}
			if ( $parse ) {
				$messageText = $this->parseMessageText( $res->msg_text );
				$this->wg->Memc->set( "swm::message::$msgId", $messageText, 60 * 60 * 24 );
			} else {
				$messageText = $res->msg_text;
				$this->wg->Memc->set( "swm::message::noparse::$msgId", $messageText, 60 * 60 * 24 );
			}
		}

		wfProfileOut( __METHOD__ );
		return $messageText;
	}

	/**
	 * Dismiss a message
	 *
	 * @param Integer $userId The ID of the user for whom to dismiss the message
	 * @param Integer $msgId the ID of the message to dismiss
	 * @return Boolean
	 */
	public function dismissMessage( $userId, $msgId ) {
		wfProfileIn( __METHOD__ );

		$dbw = $this->getSharedDB( DB_MASTER );

		// Paranoia
		$userId = (int)$userId;
		$msgId = (int)$msgId;

		// Anon messages are dismissed with a cookie,
		// so in case this somehow happens, bail
		if ( $userId === 0 ) {
			wfProfileOut( __METHOD__ );
			return false;
		}

		// This should most often be cached at this stage
		// (and is generally fast anyway), so it's an easy way
		// to check if a message with this ID actually exists
		// for this user preventing us from touching the DB unnecessarily
		$activeMessages = $this->getActiveMessagesForUser( $userId );

		if ( !array_key_exists( $msgId, $activeMessages['messages'] ) ) {
			wfProfileOut( __METHOD__ );
			return false;
		}

		// Select + insert is faster than replace into
		// as a replace into always deletes first
		$res = $dbw->selectRow(
			'swm_notification',
			[ 'notification_id', 'notification_status' ],
			[
				'notification_recipient_id' => $userId,
				'notification_id' => $msgId
			],
			__METHOD__
		);

		if ( $res === false ) {
			$dbResult = $dbw->insert(
				'swm_notification',
				[
					'notification_id' => $msgId,
					'notification_recipient_id' => $userId,
					'notification_status' => self::NOTIFICATION_DISMISSED,
				],
				__METHOD__
			);
		} else {
			$dbResult = $dbw->update(
				'swm_notification',
				[
					'notification_status' => self::NOTIFICATION_DISMISSED
				],
				[
					'notification_recipient_id' => $userId,
					'notification_id' => $msgId
				],
				__METHOD__
			);
		}

		$this->wg->Memc->delete( "swm::user::$userId" ); // Refresh the cached messages for user (possibly remove specific message from cache instead?)

		wfProfileOut( __METHOD__ );
		return $dbResult;
	}

	/**
	 * Get all active messages for a given user.
	 *
	 * @param Integer $userId The ID of the user we're retriecing the message for
	 * @return Array An array of the active messages for a user
	 */
	public function getActiveMessagesForUser( $userId ) {
		wfProfileIn( __METHOD__ );

		$messages = $this->wg->Memc->get( "swm::user::$userId" );
		$lastMessageTime = $this->wg->Memc->get( 'swm::message::lastupdate' );

		if (
			empty( $messages ) ||
			( !empty( $lastMessageTime ) && isset( $messages['lastcheck'] ) &&
				$lastMessageTime > $messages['lastcheck'] )
		) {
			$messages = [ 'lastcheck' => wfTimestamp( TS_DB ), 'messages' => [] ];

			$dbr = $this->getSharedDB();
			$quotedTimeStamp = $dbr->addQuotes( wfTimestamp( TS_DB ) );

			if ( $userId === 0 ) {
				$res = $dbr->select(
					[ 'swm_notification', 'swm_text' ],
					[ 'notification_id', 'notification_recipient_id', 'msg_text', 'msg_lang', 'wikis_matched' ],
					[
						'notification_id = msg_id',
						'notification_recipient_id' => $userId,
						'msg_removed' => self::MESSAGE_ACTIVE,
						'msg_start < ' . $quotedTimeStamp,
						'msg_expire > ' . $quotedTimeStamp
					],
					__METHOD__,
					[ 'ORDER BY' => 'msg_priority DESC' ]
				);
			} else {
				// Sub query lets us check for dismissed messages sent to
				// all users in the same query
				$subQuery = $dbr->selectSQLText(
					'swm_notification',
					[ 'notification_id' ],
					[
						'notification_recipient_id' => (int)$userId,
						'notification_status' => self::NOTIFICATION_DISMISSED,
					],
					__METHOD__
				);

				$res = $dbr->select(
					[ 'swm_notification', 'swm_text' ],
					[ 'notification_id', 'notification_recipient_id', 'msg_text', 'msg_lang', 'wikis_matched' ],
					[
						'notification_id = msg_id',
						'notification_recipient_id = ' .  $dbr->addQuotes( self::NOTIFICATION_ALL ) . ' OR notification_recipient_id = ' . $dbr->addQuotes( (int)$userId ),
						'notification_status' => self::NOTIFICATION_UNREAD,
						'msg_removed' => self::MESSAGE_ACTIVE,
						'msg_start < ' . $quotedTimeStamp,
						'msg_expire > ' . $quotedTimeStamp,
						"notification_id NOT IN ($subQuery)"
					],
					__METHOD__,
					[ 'ORDER BY' => 'msg_priority DESC', 'STRAIGHT_JOIN' ]
				);
			}
			foreach ( $res as $row ) {
				$wikisMatched = unserialize( $row->wikis_matched );
				$messages['messages'][$row->notification_id] = [ 'msgid' => $row->notification_id, 'recipient' => $row->notification_recipient_id, 'msg_lang' => explode( ',', $row->msg_lang ), 'wikismatched' => $wikisMatched ];
				$messageText = $this->wg->Memc->get( "swm::message::{$row->notification_id}" );
				if ( empty( $messageText ) ) {
					$messageText = $this->parseMessageText( $row->msg_text );
					$this->wg->Memc->set( "swm::message::{$row->notification_id}", $messageText, 60 * 60 * 24 );
				}
				$messages['messages'][$row->notification_id]['text'] = $messageText;
			}
			$this->wg->Memc->set( "swm::user::$userId", $messages, 60 * 60 ); // How long to cache for?
		}
		wfProfileOut( __METHOD__ );
		return $messages;
	}

	/**
	 * Remove an active message
	 *
	 * @param Integer $msgId the ID of the message to remove
	 * @return Boolean
	 */
	public function removeMessage( $msgId ) {
		wfProfileIn( __METHOD__ );

		$dbw = $this->getSharedDB( DB_MASTER );

		$dbResult = $dbw->update(
			'swm_text',
			[
				'msg_removed' => self::MESSAGE_REMOVED
			],
			[
				'msg_id' => $msgId
			],
			__METHOD__
		);

		wfProfileOut( __METHOD__ );
		return $dbResult;
	}

	/**
	 * Parse given message text
	 *
	 * @param String $messageText The text to parse
	 * @return String The parsed message text
	 */
	public function parseMessageText( $messageText ) {
		$title = $this->wg->Title;
		// Sometimes wgTitle doesn't exist, so make ghost title
		// A hack, but a standard one in core it seems...
		if ( !$title || !$title instanceof Title ) {
			$title = Title::newFromText( 'Dwimmerlaik' );
		}
		return ParserPool::parse( $messageText, $title, new ParserOptions(), false )->getText();
	}

}
