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

bx_import('BxDolModule');

class GoesiYouModule extends BxDolModule {

    function GoesiYouModule(&$aModule) {        
        parent::BxDolModule($aModule);
    }
//Admin Bereich
    function actionAdministration () {

        if (!$GLOBALS['logged']['admin']) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();
	$cId = $this->_oDb->getSettingsCategory(); 
	$iId = $this->_oDb->listURL($listURL); 
	if(empty($cId)) { // if category is not found display page not found
            echo MsgBox(_t('_sys_request_page_not_found_cpt'));
            $this->_oTemplate->pageCodeAdmin (_t('_goesi_yout'));
            return;
        }
	$ids = $this->_oDb->URL($URL); // get id

//
require_once('GoesiYouImport.php');
$i = '0';
$URLList = '';


//Komplette Liste auslesen
while($i < $iId['0']['count(*)']) 
 { 
 if ($ids[$i]['active'] == "yes") {
$ids[$i]['active'] = '<a href="javascript:void(0)"><img class="aktiv" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/bullet_green.png"></a>'; } 
else {
$ids[$i]['active'] = '<a href="javascript:void(0)"><img class="deaktiv" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/bullet_red.png"></a>';
}
//TimeCheck
 if (strtotime($ids[$i]['last_update']) >= time() - (60 * 60 * 24 * 7)) {
 $date= '<font color=#008000>'.date("d-m-y H:i:s", strtotime ($ids[$i]['last_update'])).' <img width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/tick.png"></font>';
} else {
 $date= '<font color=#FF0000>'.date("d-m-y H:i:s", strtotime ($ids[$i]['last_update'])).' <img width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no_ok.png"></font>';
}

$URLList .= ' <tr class="zeile_'.$ids[$i]['id'].'">
    <td width="1%" align="center"><div id="aktiv">'.$ids[$i]['active'].'<input type="hidden" class="DBid" name="DBid" value="'.$ids[$i]['id'].'"></div></td>
	<td width="1%" align="center">'.$ids[$i]['id'].'</td>
	<td width="20%" align="center">'.$ids[$i]['type'].'</td>
    <td width="20%" align="center">'.$ids[$i]['channel'].'</td>
    <td width="20%" align="center">'.$ids[$i]['album'].'</td>
	<td width="20%" align="center">'.$ids[$i]['user_id'].'</td>
     <td width="20%" align="center"><div id="Time_'.$ids[$i]['id'].'">'.$date.'<div></td>
    <td width="5%" align="center"><div id="Links_'.$ids[$i]['id'].'">'.$ids[$i]['last_count'].'</div></td>
    <td width="10%" align="center"><div class="sync"><a href="javascript:void(0)"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/sync.png"><input type="hidden" class="DBid" name="DBid" value="'.$ids[$i]['id'].'"></a></div></td>
    <td width="10%" align="center"><div class="delete"><a href="javascript:void(0)"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no.png"><input type="hidden" class="DBid" name="DBid" value="'.$ids[$i]['id'].'"></a></div></td>
  </tr>';
$i++;
}

$query = mysql_query("SELECT max(last_update) FROM goesi_youtube");
$q = mysql_fetch_row($query);
 if (strtotime($q['0']) >= time() - (60 * 60 * 24 * 7)) {
 $LastCron= '<font color=#008000>'.date("d-m-y H:i:s", strtotime ($q['0'])).'</font><img src="<bx_icon_url:yes.png />">';
} else {
 $LastCron= '<font color=#FF0000>'.date("d-m-y H:i:s", strtotime ($q['0'])).'</font><img src="<bx_icon_url:no.png />">';
}

$query = mysql_query("SELECT `Caption` FROM `sys_albums` WHERE `Type`='bx_videos' AND `Owner`='".getLoggedId()."' ORDER BY `ID` DESC");

while ($value = mysql_fetch_assoc($query)) {
$AlbumOptions .='<option>'.$value['Caption'].'</option>';
}

//
$aVars = array (
            'module_url' 	=> BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(),
	     'AlbumOptions'	=> $AlbumOptions,	
	     'all'		=> $sResult,
	     'newurls'	=> $URLList,
	     'lastcron'	=> $LastCron,	
        );
        bx_import('BxDolAdminSettings'); // import class

        $mixedResult = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) { 
            $oSettings = new BxDolAdminSettings($cId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($cId); 
        $sResult = $oSettings->getForm();
                   
        if($mixedResult !== true && !empty($mixedResult)) 
            $sResult = $mixedResult . $sResult . header("Location: " . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() .'administration/');
	$sContent .= '<script type="text/javascript"> 
			var Bilderpfad = "'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/";
			var PostDir = "'.BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri().'administration/";
			</script>';
	$sContent .= '<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.js"></script>';
	$sContent .= $this -> _oTemplate  -> addJs('goesi_actions.js', true);
	$sContent .= $this->_oTemplate->parseHtmlByName ('admin', $aVars);


        echo $this->_oTemplate->adminBlock ($sContent, _t('Youtube Auto Importer'));

        echo DesignBoxAdmin (_t('_goesi_yout'), $sResult);
        
        $this->_oTemplate->pageCodeAdmin (_t('_goesi_yout'));
    }





//User Bereich
    function actionHome () {
        $this->_oTemplate->pageStart();
	$query = mysql_query("Select * from goesi_youtube WHERE `user_id`='".getLoggedId()."'");
	$iId = mysql_num_rows($query);
	$query = mysql_query("Select * from goesi_youtube WHERE `user_id`='".getLoggedId()."'");// get id
	//$ids = mysql_fetch_assoc($query);
//
require_once('GoesiYouImport.php');
$i = '0';
$URLList = '';


//Komplette Liste auslesen
while($ids = mysql_fetch_assoc($query)) 
 { 

 if ($ids['active'] == "yes") {
$ids['active'] = '<a href="javascript:void(0)"><img class="aktiv" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/bullet_green.png"></a>'; } 
else {
$ids['active'] = '<a href="javascript:void(0)"><img class="deaktiv" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/bullet_red.png"></a>';
}
//TimeCheck
 if (strtotime($ids['last_update']) >= time() - (60 * 60 * 24 * 7)) {
 $date= '<font color=#008000>'.date("d-m-y H:i:s", strtotime ($ids['last_update'])).' <img width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/tick.png"></font>';
} else {
 $date= '<font color=#FF0000>'.date("d-m-y H:i:s", strtotime ($ids['last_update'])).' <img width="12" src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no_ok.png"></font>';
}

$URLList .= ' <tr class="zeile_'.$ids['id'].'">
    <td width="1%" align="center"><div id="aktiv">'.$ids['active'].'<input type="hidden" class="DBid" name="DBid" value="'.$ids['id'].'"></div></td>
	<td width="1%" align="center">'.$ids['id'].'</td>
	<td width="20%" align="center">'.$ids['type'].'</td>
    <td width="20%" align="center">'.$ids['channel'].'</td>
    <td width="20%" align="center">'.$ids['album'].'</td>
	<td width="20%" align="center">'.$ids['user_id'].'</td>
     <td width="20%" align="center"><div id="Time_'.$ids['id'].'">'.$date.'<div></td>
    <td width="5%" align="center"><div id="Links_'.$ids['id'].'">'.$ids['last_count'].'</div></td>
    <td width="10%" align="center"><div class="sync"><a href="javascript:void(0)"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/sync.png"><input type="hidden" class="DBid" name="DBid" value="'.$ids['id'].'"></a></div></td>
    <td width="10%" align="center"><div class="delete"><a href="javascript:void(0)"><img src="'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/no.png"><input type="hidden" class="DBid" name="DBid" value="'.$ids['id'].'"></a></div></td>
  </tr>';
$i++;
}

$query = mysql_query("SELECT max(last_update) FROM goesi_youtube");
$q = mysql_fetch_row($query);
 if (strtotime($q['0']) >= time() - (60 * 60 * 24 * 7)) {
 $LastCron= '<font color=#008000>'.date("d-m-y H:i:s", strtotime ($q['0'])).'</font><img src="<bx_icon_url:yes.png />">';
} else {
 $LastCron= '<font color=#FF0000>'.date("d-m-y H:i:s", strtotime ($q['0'])).'</font><img src="<bx_icon_url:no.png />">';
}

$query = mysql_query("SELECT `Caption` FROM `sys_albums` WHERE `Type`='bx_videos' AND `Owner`='".getLoggedId()."' ORDER BY `ID` DESC");

while ($value = mysql_fetch_assoc($query)) {
$AlbumOptions .='<option>'.$value['Caption'].'</option>';
}
$aVars = array (
            'module_url' 	=> BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(),
	     'AlbumOptions'	=> $AlbumOptions,	
	     'all'		=> $sResult,
	     'newurls'	=> $URLList,
	     'lastcron'	=> $LastCron,	
        );
//
echo '<script type="text/javascript"> 
			var Bilderpfad = "'.BX_DOL_URL_MODULES.'goesi/youtube_auto-import/templates/base/images/icons/";
			var PostDir = "'.BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri().'";
			</script>';
	echo '<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.js"></script>';
	echo $this -> _oTemplate  -> addJs('goesi_actions.js', true);

        echo $this->_oTemplate->parseHtmlByName('main', $aVars);
        $this->_oTemplate->pageCode(_t('_goesi_yout'), true);
    }


}

?>
