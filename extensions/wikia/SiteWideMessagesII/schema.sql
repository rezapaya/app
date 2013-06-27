CREATE TABLE IF NOT EXISTS /*_*/swm_text (
	`msg_id` int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`msg_text` mediumtext NOT NULL,
	`msg_removed` int(1) NOT NULL DEFAULT 0,
	`msg_start` datetime NOT NULL,
	`msg_expire` datetime NOT NULL,
	`msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`msg_lang` varchar(255),
	`msg_priority` int(3) unsigned NOT NULL DEFAULT 1,
	`msg_data` TEXT
) /*$wgDBTableOptions*/;
CREATE INDEX /*i*/removed_start_expire ON /*_*/swm_text (msg_removed, msg_start, msg_expire);

CREATE TABLE IF NOT EXISTS /*_*/swm_notification (
	`notification_id` int unsigned NOT NULL,
	`notification_recipient_id` int signed NOT NULL,
	`wikis_matched` TEXT,
	`notification_status` int(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY(`notification_id`, `notification_recipient_id`)
) /*$wgDBTableOptions*/;
CREATE INDEX /*i*/notification_recipient_id_status ON /*_*/swm_notification (notification_recipient_id, notification_status);
