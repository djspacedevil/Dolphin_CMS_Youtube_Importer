<?php

    /***************************************************************************
    *
    *                            Youtube - Auto-Importer
    *                      
    *     copyright            : (C) 2012 Sven Goessling / SmileAndGo.de
    *     website              : http://www.sven-goessling.de
    *
    *     IMPORTANT: This is a commercial product made by Sven Goessling and cannot be modified for other than personal usage. 
    *     This product cannot be redistributed for free or redistribute it and/or modify without written permission from Sven Goessling. 
    *     This notice may not be removed from the source code.
    *     See license.txt file; if not, write to info@emsland-party.de 
    *
    *                            Dolphin Smart Community Builder
    *                              -------------------
    *     begin                : Mon Mar 23 2006
    *     copyright            : (C) 2007 BoonEx Group
    *     website              : http://www.boonex.com
    * This file is part of Dolphin - Smart Community Builder
    *
    * Dolphin is free software; you can redistribute it and/or modify it under
    * the terms of the GNU General Public License as published by the
    * Free Software Foundation; either version 2 of the
    * License, or  any later version.
    *
    * Dolphin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
    * without even the implied warranty of  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    * See the GNU General Public License for more details.
    * You should have received a copy of the GNU General Public License along with Dolphin,
    * see license.txt file; if not, write to marketing@boonex.com
    ***************************************************************************/

    bx_import('BxDolCron');

    require_once('GoesiYouModule.php');

    class goesi_you_cron extends BxDolCron 
    {
        var $oSpyObject;
        var $iDaysForRows;

        /** 
         * Class constructor;
         */
        function goesi_you_cron()
        {
       // echo 'Youtube Import:<br>'; 
// Anfang
	$this -> oModule     = BxDolModule::getInstance('GoesiYouModule');
	
$loop_query = mysql_query("SELECT * FROM `goesi_youtube`");
while($loop = mysql_fetch_assoc($loop_query)) {






/////////////////////////////////////////////////////////
	$channel = $loop['channel']; 
	$Owner = $loop['user_id'];
	$album = $loop['album'];
	$active = $loop['active'];
	$last_count = $loop['last_count'];


//Wenn nicht aktiv, breaken.
if ($active == "no") continue;
$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://gdata.youtube.com/feeds/api/users/'.$channel.'/uploads');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $you_cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $you_cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3");

	 $result = curl_exec($ch);

	//echo htmlspecialchars($result); 
			//Links $YouLinks
			preg_match_all('/<media:player url=\'https:\/\/www.youtube.com\/watch\?v=(.*)&/iU', $result, $YouLinks);
				//if (count($YouLinks['1']) > 0) { echo '<br>AJAX:'.count($YouLinks['1']).' Links<br>'; } else {echo 'NoLinks';}
			//Names $YouNames
			preg_match_all('/<media:title type=\'plain\'>(.*)<\/media:title>/iU', $result, $YouNames);
				//if (count($YouNames['1']) > 0) { echo '<br>AJAX:'.count($YouNames['1']).' Names<br>'; } else {echo 'No Names';}
			//Video Time $YouTime
			preg_match_all('/<yt:duration seconds=\'(.*)\'\/>/iU', $result, $YouTime);
				//if (count($YouTime['1']) > 0) { echo '<br>AJAX:'.count($YouTime['1']).' Times<br>'; } else {echo 'No Times';}
			//Keywords $YouKeyword
			preg_match_all('/<media:keywords>(.*)<\/media:keywords>/iU', $result, $YouKeyword);
				//if (count($YouKeyword['1']) > 0) { echo '<br>AJAX:'.count($YouKeyword['1']).' Keywords<br>'; } else {echo 'No Keys';}
			//Beschreibung  $YouDesc
			preg_match_all('/<media:description type=\'plain\'>(.*)<\/media:description>/iU', $result, $YouDesc);
				//if (count($YouDesc['1']) > 0) { echo '<br>AJAX:'.count($YouDesc['1']).' Desc<br>'; } else {echo 'No Descs';}
			

$count =count($YouLinks['1']);
$last_update_count = '0';

$i=0;
while($i < $count) 
 { 
//Aktionen zum Einlesen

//Infos festhalten
$Video = $YouLinks['1'][$i];
$Name =  $YouNames['1'][$i];
$sonderzeichen=array( "'" => "-", " " => "-", "&" => "-", ";"=> "-", "." => "-", "ä" => "-", "ö" => "-", "ü" => "-", "Ä" => "-", "Ö" => "-", "Ü" => "-", "+" => "-", "ß" => "-", "?" => "-", "'" => "-", '"' => '-'  );
  	$Uri = trim(strtr($Name, $sonderzeichen));
$Bild =  $YouThumb['1'][$i];
$Zeit =  $YouTime['1'][$i];
$Stichwort = $YouKeyword['1'][$i];
$Beschreibung = $YouDesc['1'][$i];
$BildURL = 'https://img.youtube.com/vi/'.$Video.'/1.jpg'; 
//$YouThumb['1'][$i];

//Checken ob das Video schon vorhanden ist
$query = mysql_query ("SELECT * FROM `RayVideoFiles` WHERE Owner='".$Owner."' AND `Video` ='".$Video."' AND `Source`='youtube'");
	if (mysql_fetch_row($query) == '0') {
	//Nicht vorhanden  -> Video einfügen
	mysql_query("INSERT INTO `RayVideoFiles` (`Title`, `Uri`, `Tags`, `Description`, `Time`, `Date`, `Owner`, `Status`, `Source`, `Video`) VALUES ('".$Name."', '".$Uri."', '".$Stichwort."', '".$Beschreibung."', '".($Zeit*1000)."', '".time()."', '".$Owner."', 'approved', 'youtube', '".$Video."')");

	$last_update_count = $last_update_count+1;
	//Neue ID holen
	$query = mysql_query ("SELECT `id` FROM `RayVideoFiles` WHERE `Video` ='".$Video."' AND `Source`='youtube'");
	$d = mysql_fetch_row($query);
	$NewID = $d['0'];

	//Zum Album hinzufügen
	$query = mysql_query("SELECT `id` FROM `sys_albums` WHERE `Type`='bx_videos' AND `Caption`='".addslashes($album)."'");
	$d = mysql_fetch_row($query);
	$albID = $d['0'];
	mysql_query("INSERT INTO `sys_albums_objects` (`id_album`, `id_object`) VALUES ('".$albID."','".$NewID."')");
	
	//SYS_ALBUM aktualisieren
	mysql_query("UPDATE `sys_albums` SET `ObjCount`=`ObjCount`+1, `LastObjId`='".$NewID."' WHERE `id`='".$albID."' AND `Type`='bx_videos'");

	//Video Bild einfügen
	// Path for uploads - goesi
	if (!is_dir(BX_DIRECTORY_PATH_ROOT.'flash/modules/video/files')) {
	mkdir(BX_DIRECTORY_PATH_ROOT.'flash/modules/video/files', 0777);
	echo BX_DIRECTORY_PATH_ROOT.'flash/modules/video/files/ folder not available; Create it.'; }
	$sPath     = BX_DIRECTORY_PATH_ROOT.'flash/modules/video/files/' . $NewID . '_small.jpg';
	$content = file_get_contents($BildURL);
	//Store in the filesystem.
	$fp = fopen($sPath, "w");
	fwrite($fp, $content);
	fclose($fp);
	}


//Ende
$i++;
} 

//Gefundene neue Links
$last_count = $last_count+$last_update_count;
mysql_query("UPDATE `goesi_youtube` SET `last_count`='".$last_count."' WHERE `channel` ='".$channel."'");
}

/////////////////////////////////////////////////////////

//Ende Schleife
}
	//Ende
	}
   

