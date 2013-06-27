<?php
/**
 * Internationalisation for SiteWideMessages extension
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English
 * @author Daniel Grunwell (grunny)
 */
$messages['en'] = array(
	'sitewidemessages' => 'Site wide messages',
	'sitewidemessages-desc' => 'This extension provides an interface for sending messages seen on all wikis.',
	'sitewidemessages-dismissed' => 'Successfully dismissed message',
	'sitewidemessages-removed' => 'Successfully removed message',
	'sitewidemessages-error-permissions' => 'You do not have permission to perform that action',
	'sitewidemessages-error-nosuchmessage' => 'Message with ID $1 does not exist',
	'sitewidemessages-error-incorrectuser' => 'User ID does not match the current User',
	'sitewidemessages-error-dismissfailed' => 'Failed to dismiss message',
	'sitewidemessages-error-removefailed' => 'Failed to remove message',
	'action-sitewidemessages' => 'send site wide messages',
);

/**
 * @author Daniel Grunwell (grunny)
 */
$messages['qqq'] = array(
	'sitewidemessages' => 'Special page name',
	'sitewidemessages-desc' => '{{desc}}',
	'sitewidemessages-dismissed' => 'Success message returned when a user successfully dismissed a message.',
	'sitewidemessages-removed' => 'Success message returned when a user successfully removes a message.',
	'sitewidemessages-error-permissions' => 'Permission error when user attempts to make changes to message without appropriate permissions',
	'sitewidemessages-error-nosuchmessage' => 'Error message returned when trying to get the text of a message that does not exist. $1 is the message ID that was provided in the request.',
	'sitewidemessages-error-incorrectuser' => 'Error message returned when a user attempts to get or dismiss messages for a different account (user ID).',
	'sitewidemessages-error-dismissfailed' => 'Error message returned when a message failed to be dismissed.',
	'sitewidemessages-error-removefailed' => 'Error message returned when a message failed to be removed.',
	'action-sitewidemessages' => '{{doc-action|sitewidemessages}}',
);
