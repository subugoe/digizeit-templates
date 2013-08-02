<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages_language_overlay', 'pid='.intval($GLOBALS['TSFE']->id).$GLOBALS['TSFE']->sys_page->enableFields('pages_language_overlay'), 'sys_language_uid');

$langArr = array();
while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
        $langArr[$row['sys_language_uid']] = $row['title'];
}
//debug($GLOBALS['_SERVER'],__FILE__,__LINE__);

$strQuery = str_replace('&L='.$GLOBALS['TSFE']->sys_language_uid,'',$GLOBALS['_SERVER']['QUERY_STRING']);
$strQuery = str_replace('L='.$GLOBALS['TSFE']->sys_language_uid.'&','',$strQuery);
$strQuery = str_replace('L='.$GLOBALS['TSFE']->sys_language_uid,'',$strQuery);
parse_str($strQuery,$arrTmp);
if($strQuery=='') {
	$strQuery = 'id='.$GLOBALS['TSFE']->id;
	$arrTmp['id'] = $GLOBALS['TSFE']->id;
}
if(!isset($arrTmp['id'])) $strQuery = '?id='.$GLOBALS['TSFE']->id.'&'.$strQuery.'&L=';
else $strQuery = '?'.$strQuery.'&L=';
$link = 'index.php'.$strQuery;


if ($GLOBALS['TSFE']->sys_language_uid==0)
	$content = '<a href="'.$link.'2" target="_top">english</a>';
//	$content = '<a href="'.$link.'2" target="_top"><img src="/fileadmin/layout/gb.gif" width="20" height="12" border="0" /></a>';
	//$content = '<a href="'.htmlspecialchars('index.php?id='.$GLOBALS['TSFE']->id.'&L=2').'" target="_top"><img src="fileadmin/digizeit/images/gb.gif" width="20" height="12" border="0" alt="" /></a>';
elseif ($GLOBALS['TSFE']->sys_language_uid==2)
	$content = '<a href="'.$link.'0" target="_top">deutsch</a>';
//	$content = '<a href="'.$link.'0" target="_top"><img src="/fileadmin/layout/de.gif" width="20" height="12" border="0" /></a>';
	//$content = '<a href="'.htmlspecialchars('index.php?id='.$GLOBALS['TSFE']->id.'&L=0').'" target="_top"><img src="fileadmin/digizeit/images/de.gif" width="20" height="12" border="0" alt="" /></a>';
?>