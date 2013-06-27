<?php
/**
 * SiteWideMessages
 *
 * Provides an interface for sending messages seen on all wikis
 *
 * @author Daniel Grunwell (Grunny) <grunny@wikia-inc.com>
 * @copyright (c) 2013 Daniel Grunwell, Wikia, Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'SiteWideMessages',
	'descriptionmsg' => 'sitewidemessages-desc',
	'author' => array(
		'[http://community.wikia.com/wiki/User:Grunny Daniel Grunwell (Grunny)]'
	),
);

$dir = __DIR__ . '/';

$wgAutoloadClasses['SiteWideMessagesController'] =  $dir . 'SiteWideMessagesController.class.php';
$wgAutoloadClasses['SiteWideMessagesSpecialController'] =  $dir . 'SiteWideMessagesSpecialController.class.php';
$wgAutoloadClasses['SiteWideMessagesHelper'] =  $dir . 'SiteWideMessagesHelper.class.php';

$wgExtensionMessagesFiles['SiteWideMessages'] = $dir . 'SiteWideMessages.i18n.php';

$wgSpecialPages['SiteWideMessages'] = 'SiteWideMessagesSpecialController';
$wgSpecialPageGroups['SiteWideMessages'] = 'wikia';

$wgAvailableRights[] = 'sitewidemessages';
$wgGroupPermissions['*']['sitewidemessages'] = false;
$wgGroupPermissions['staff']['sitewidemessages'] = true;
$wgGroupPermissions['util']['sitewidemessages'] = true;
