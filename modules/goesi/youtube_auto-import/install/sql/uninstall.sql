--liste
DROP TABLE `goesi_youtube`;

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Youtube Importer' LIMIT 1);
--DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'goesi_yout_permalinks';

-- permalinks
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=youtube_auto-import/';

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'goesi_youtube';

-- Cron Job
    DELETE FROM `sys_cron_jobs` WHERE `name` = 'goesi_youtube';