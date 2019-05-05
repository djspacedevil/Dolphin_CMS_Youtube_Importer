-- Tabelle erstellen
DROP TABLE IF EXISTS `goesi_youtube`;
CREATE TABLE IF NOT EXISTS `goesi_youtube` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('channel','playlist','tags','search') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'channel',
  `channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `album` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_count` int(11) NOT NULL DEFAULT '0',
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Youtube Importer', @iMaxOrder);

-- permalinks
INSERT INTO `sys_permalinks` VALUES (NULL, 'modules/?r=youtube_auto-import/', 'm/youtube_auto-import/', 'goesi_yout_permalinks');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'goesi_youtube', '_goesi_youtube', '{siteUrl}modules/?r=youtube_auto-import/administration/', 'Youtube Auto Importer', 'modules/goesi/youtube_auto-import/|icon.png', @iMax+1);

-- CronJob
INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`) VALUES ('goesi_youtube', '0 */1 * * *', 'goesi_you_cron', 'modules/goesi/youtube_auto-import/classes/GoesiYouCron.php');

