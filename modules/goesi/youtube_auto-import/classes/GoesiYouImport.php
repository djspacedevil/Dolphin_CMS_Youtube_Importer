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
    ***************************************************************************/

	$channel_url = addslashes($_POST['channel_url']);
// Youtube Channel öffnen
//$this -> oModule     = BxDolModule::getInstance('GoesiFbModule');
//https://img.youtube.com/vi/345fV2pBi8M/0.jpg
//https://gdata.youtube.com/feeds/api/users/EmslandPartyDE/uploads

if ($_POST['NewChannel'] && $_POST['NewAlbum'] && $_POST['dbTyp']) {
/////////////////////////////////////////////////Neuen Eintrag
	ob_end_clean();

$ersetzen = array( 'https://www.youtube.com/playlist?list=' => '', 'http://www.youtube.com/playlist?list=' => '', 'https://www.youtube.com/user/' => '', 'http://www.youtube.com/user/' => '', 'https://youtube.com/user/' => '', 'www.youtube.com/user/' => '', 'youtube.com/user/' => '', '?feature=g-user-a' => '', '?feature=plcp' => '', '?feature=mhee' => '', '?feature=g-all-c' => '', ', ' => '+', ' ' => '+', ',' => '+');
$_POST['NewChannel'] = strtr( $_POST['NewChannel'], $ersetzen );

$query = mysql_query("SELECT * FROM `goesi_youtube` WHERE `channel`='".$_POST['NewChannel']."'");
if (mysql_fetch_row($query) == '0') {
	
	$_POST['NewChannel'] = strtr( $_POST['NewChannel'], $ersetzen );

 mysql_query("INSERT INTO `goesi_youtube` (`type`,`channel`,`user_id`,`album`) VALUES ('".$_POST['dbTyp']."','".$_POST['NewChannel']."','".getLoggedId()."','".$_POST['NewAlbum']."')");

$query2 = mysql_query("SELECT * FROM `goesi_youtube` WHERE `channel`='".$_POST['NewChannel']."'");
$q = mysql_fetch_assoc($query2);

 echo '<tr class="zeile_'.$q['id'].'">
    <td width="1%" align="center"><div id="aktiv"><img  class="aktiv" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/bullet_green.png"><input type="hidden" class="DBid" name="DBid" value="'.$q['id'].'"></div></td>
	<td width="1%" align="center">'.$q['id'].'</td>
    <td width="20%" align="center">'.$q['type'].'</td>
    <td width="20%" align="center">'.$q['channel'].'</td>
    <td width="20%" align="center">'.$q['album'].'</td>
    <td width="20%" align="center">'.$q['user_id'].'</td>
     <td width="20%" align="center"><font color=#FF0000>'.date("d-m-y H:i:s", strtotime ($q['last_update'])).'<img  width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no_ok.png"></font></td>
    <td width="5%" align="center">'.$q['last_count'].'</td>
    <td width="10%" align="center"><div class="sync"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/sync.png"><input type="hidden" class="DBid" name="DBid" value="'.$q['id'].'"></div></td>
    <td width="10%" align="center"><div class="delete"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no.png"><input type="hidden" class="DBid" name="DBid" value="'.$q['id'].'"></div></td>
  </tr>'; 
} else { echo 'Error';}
	exit;
} else if ($_POST['NewChannel'] && $_POST['NewChannel'] == "")
{
ob_end_clean();
echo 'NoAlbum';
exit;
}

if ($_POST['AKTIV'] && $_POST['db_id']) {
/////////////////////////////////////////////////Eintrag deaktivieren / aktivieren
	ob_end_clean();
	mysql_query("UPDATE `goesi_youtube` SET active='".$_POST['AKTIV']."' WHERE `id`='".$_POST['db_id']."'");
	exit;}



if ($_POST['DELETE'] == 'yes' && $_POST['db_id']) {
/////////////////////////////////////////////////Eintrag entfernen
	ob_end_clean();
	mysql_query("DELETE FROM `goesi_youtube` WHERE `id`='".$_POST['db_id']."'");
	exit;}

if ($_POST['TIME'] == 'yes' && $_POST['db_id']) {
/////////////////////////////////////////////////Einen Channel Snyc
	ob_end_clean();
	$query = mysql_query("SELECT `last_update` FROM `goesi_youtube` WHERE `id`='".$_POST['db_id']."' ");
	$d = mysql_fetch_row($query);
	echo '<font color=#008000>'.date("d-m-y H:i:s", strtotime ($d['0'])).' <img width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/tick.png"></font>';
	exit;
}

if ($_POST['LINKS'] == 'yes' && $_POST['db_id']) {
/////////////////////////////////////////////////Einen Channel Snyc
	ob_end_clean();
	$query = mysql_query("SELECT `last_count` FROM `goesi_youtube` WHERE `id`='".$_POST['db_id']."' ");
	$d = mysql_fetch_row($query);
	echo $d['0'];
	exit;
}




if ($_POST['SYNC'] == 'yes' && $_POST['db_id']) {
/////////////////////////////////////////////////Einen Channel Snyc
	ob_end_clean();
//Channel erfassen:
$you_cookie = 'cookie.txt'; 

$query = mysql_query("SELECT * FROM `goesi_youtube` WHERE `id`='".$_POST['db_id']."'");
$q = mysql_fetch_row($query);

	$type = $q['2'];
	$channel = $q['3'];
	$Owner = $q['4'];
	$album = $q['5'];
	$active = $q['1'];
	$last_count = $q['6'];

//Wenn nicht aktiv, breaken.
if ($active == "no") {echo 'NoNew'; exit;}

$ch = curl_init();
	if ($type == "channel") {
		curl_setopt($ch, CURLOPT_URL, 'https://gdata.youtube.com/feeds/api/users/'.$channel.'/uploads');
	} else if ($type == "playlist") {
		curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/view_play_list?p='.$channel);
	} else if ($type == "tags") {
		curl_setopt($ch, CURLOPT_URL, 'https://youtube.com/rss/tag/'.$channel.'.rss');
	} else if ($type == "search") {
		curl_setopt($ch, CURLOPT_URL, 'https://youtube.com/rss/tag/'.$channel.'.rss');
	}
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $you_cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $you_cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3");

	 $result = curl_exec($ch);

	//echo htmlspecialchars($result); 
	if ($type == "channel") {
	
	//Channel Videos
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

	} else if ($type == "playlist") {
	
	//Playlist Videos
			//Links $YouLinks
			preg_match_all('/data-video-ids="(.*)"/iU', $result, $YouLinks);
				//if (count($YouLinks['1']) > 0) { echo '<br>AJAX:'.count($YouLinks['1']).' Links<br>'; } else {echo 'NoLinks';}
			//Names $YouNames
			preg_match_all('/<span class="title video-title "  dir="ltr">(.*)<\/span>/iU', $result, $YouNames);
				//if (count($YouNames['1']) > 0) { echo '<br>AJAX:'.count($YouNames['1']).' Names<br>'; } else {echo 'No Names';}
			//Video Time $YouTime
			preg_match_all('/<span class="video-time">(.*)<\/span>/iU', $result, $YouTime);
				//if (count($YouTime['1']) > 0) { echo '<br>AJAX:'.count($YouTime['1']).' Times<br>'; } else {echo 'No Times';}
			//Keywords $YouKeyword
			preg_match_all('/<meta name="keywords" content="(.*)">/iU', $result, $YouKeyword);
				//if (count($YouKeyword['1']) > 0) { echo '<br>AJAX:'.count($YouKeyword['1']).' Keywords<br>'; } else {echo 'No Keys';}
			//Beschreibung  $YouDesc
			preg_match_all('/<meta name="description" content="(.*)">/iU', $result, $YouDesc);
				//if (count($YouDesc['1']) > 0) { echo '<br>AJAX:'.count($YouDesc['1']).' Desc<br>'; } else {echo 'No Descs';}

	} else if ($type == "tags") {

	//Tags Videos
			//Links $YouLinks
			preg_match_all('/video:(.*)<\/guid>/iU', $result, $YouLinks);
				//if (count($YouLinks['1']) > 0) { echo '<br>AJAX:'.count($YouLinks['1']).' Links<br>'; } else {echo 'NoLinks';}
			//Names $YouNames
			preg_match_all('/video<\/category><title>(.*)<\/title>/iU', $result, $YouNames);
				//if (count($YouNames['1']) > 0) { echo '<br>AJAX:'.count($YouNames['1']).' Names<br>'; } else {echo 'No Names';}
			//Video Time $YouTime
			preg_match_all('/&lt;span style="color: #000000; font-size: 11px; font-weight: bold;"&gt;(.*)&lt;\/span&gt;&lt;\/td&gt;/iU', $result, $YouTime);
				//if (count($YouTime['1']) > 0) {echo '<br>AJAX:'.count($YouTime['1']).' Times<br>'; } else {echo 'No Times';}
			//Keywords $YouKeyword
			preg_match_all('/<guid isPermaLink=\'false\'>tag:(.*)<\/guid>/iU', $result, $YouKeyword);
				//if (count($YouKeyword['1']) > 0) { echo '<br>AJAX:'.count($YouKeyword['1']).' Keywords<br>'; } else {echo 'No Keys';}
			//Beschreibung  $YouDesc
			preg_match_all('/&lt;div style="font-size: 12px; margin: 3px 0px;"&gt;&lt;span&gt;(.*)&lt;\/span&gt;&lt;\/div&gt;&lt;\/td&gt;/iUs', $result, $YouDesc);
				//if (count($YouDesc['1']) > 0) { echo $YouDesc['1']['1']; echo'<br>AJAX:'.count($YouDesc['1']).' Desc<br>'; } else {echo 'No Descs';}

	} else if ($type == "search") {

	//search Videos
			//Links $YouLinks
			preg_match_all('/video:(.*)<\/guid>/iU', $result, $YouLinks);
				//if (count($YouLinks['1']) > 0) { echo '<br>AJAX:'.count($YouLinks['1']).' Links<br>'; } else {echo 'NoLinks';}
			//Names $YouNames
			preg_match_all('/video<\/category><title>(.*)<\/title>/iU', $result, $YouNames);
				//if (count($YouNames['1']) > 0) { echo '<br>AJAX:'.count($YouNames['1']).' Names<br>'; } else {echo 'No Names';}
			//Video Time $YouTime
			preg_match_all('/&lt;span style="color: #000000; font-size: 11px; font-weight: bold;"&gt;(.*)&lt;\/span&gt;&lt;\/td&gt;/iU', $result, $YouTime);
				//if (count($YouTime['1']) > 0) {echo '<br>AJAX:'.count($YouTime['1']).' Times<br>'; } else {echo 'No Times';}
			//Keywords $YouKeyword
			preg_match_all('/<guid isPermaLink=\'false\'>tag:(.*)<\/guid>/iU', $result, $YouKeyword);
				//if (count($YouKeyword['1']) > 0) { echo '<br>AJAX:'.count($YouKeyword['1']).' Keywords<br>'; } else {echo 'No Keys';}
			//Beschreibung  $YouDesc
			preg_match_all('/&lt;div style="font-size: 12px; margin: 3px 0px;"&gt;&lt;span&gt;(.*)&lt;\/span&gt;&lt;\/div&gt;&lt;\/td&gt;/iUs', $result, $YouDesc);
				//if (count($YouDesc['1']) > 0) { echo $YouDesc['1']['1']; echo'<br>AJAX:'.count($YouDesc['1']).' Desc<br>'; } else {echo 'No Descs';}

	}
$count =count($YouLinks['1']);
$last_update_count = '0';

$i=0;
while($i < $count) 
 { 
//Aktionen zum Einlesen

//Infos festhalten
$Video = $YouLinks['1'][$i];
$Name =  $YouNames['1'][$i];
$sonderzeichen=array( "'" => "-", " " => "-", "&" => "-", ";"=> "-", "." => "-", "ä" => "-", "ö" => "-", "ü" => "-", "Ä" => "-", "Ö" => "-", "Ü" => "-", "+" => "-", "ß" => "-", "?" => "-", "'" => "-", '"' => '-', "`" => "-", ":" => "-"  );
  	$Uri = trim(strtr($Name, $sonderzeichen));

if ($type == "channel") $Zeit =  $YouTime['1'][$i];
if ($type == "playlist") $Zeit =  str_replace(':', '.', $YouTime['1'][$i])*60;
if ($type == "tags") $Zeit =  str_replace(':', '.', $YouTime['1'][$i])*60;
if ($type == "search") $Zeit =  str_replace(':', '.', $YouTime['1'][$i])*60;

$Stichwort = str_replace(":", ", ", $YouKeyword['1'][$i]);

$sonderzeichen1=array( "&amp;" => "&", "&amp;quot;" => '"', "&amp;amp;" => "&", "&amp;lt;" => "<", "&amp;gt;" => ">", "&amp;nbsp;" => " ", "&amp;Auml;" => "Ä", "&amp;auml;" => "ä", "&amp;Uuml;" => "Ü", "&amp;uuml;" => "ü", "&amp;Ouml;" => "Ö", "&amp;ouml;" => "ö", "&amp;szlig;" => "ss",);
$Beschreibung = trim(strtr($YouDesc['1'][$i], $sonderzeichen1));

$BildURL = 'https://img.youtube.com/vi/'.$Video.'/1.jpg'; 
//$YouThumb['1'][$i];

//Checken ob das Video schon vorhanden ist
$query = mysql_query ("SELECT * FROM `RayVideoFiles` WHERE Owner='".$Owner."' AND `Video` ='".addslashes($Video)."'");
	if (mysql_fetch_row($query) == '0') {
	
	//Nicht vorhanden  -> Video einfügen
	mysql_query("INSERT INTO `RayVideoFiles` (`Title`, `Uri`, `Tags`, `Description`, `Time`, `Date`, `Owner`, `Status`, `Source`, `Video`) VALUES ('".addslashes($Name)."', '".addslashes($Uri)."', '".addslashes($Stichwort)."', '".addslashes($Beschreibung)."', '".($Zeit*1000)."', '".time()."', '".addslashes($Owner)."', 'approved', 'youtube', '".addslashes($Video)."')");
	
	$last_update_count = $last_update_count+1;
	//Neue ID holen
	$query = mysql_query ("SELECT `id` FROM `RayVideoFiles` WHERE `Video` ='".addslashes($Video)."' AND `Source`='youtube'");
	$d = mysql_fetch_row($query);
	$NewID = $d['0'];

	//Zum Album hinzufügen
	$query = mysql_query("SELECT `id` FROM `sys_albums` WHERE `Type`='bx_videos' AND `Caption`='".addslashes($album)."' AND Owner='".addslashes($Owner)."'");
	$d = mysql_fetch_row($query);
	$albID = $d['0'];
	mysql_query("INSERT INTO `sys_albums_objects` (`id_album`, `id_object`) VALUES ('".addslashes($albID)."','".addslashes($NewID)."')");
	
	//SYS_ALBUM aktualisieren
	mysql_query("UPDATE `sys_albums` SET `ObjCount`=`ObjCount`+1, `LastObjId`='".addslashes($NewID)."' WHERE `id`='".addslashes($albID)."' AND `Type`='bx_videos'");
	
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
$last_count = $last_count+$last_update_count;
//Gefundene neue Links
mysql_query("UPDATE `goesi_youtube` SET `last_count`='".addslashes($last_count)."' WHERE `channel` ='".addslashes($channel)."'");
if ($last_update_count > '0') {echo 'New';} else {echo 'NoNew';}


	exit;}

?>