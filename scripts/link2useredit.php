<?php
/*
$FEuserUid = t3lib_div::_GP('rU');
$FEuserForm = t3lib_div::_GP('FE');
if(!isset($FEuserUid) && !isset($FEuserForm)) {
	//debug($GLOBALS['TSFE']->fe_user->user['uid']);
	//debug($GLOBALS['TSFE']->page);
	$url = t3lib_div::linkThisScript(array('id'=>$GLOBALS['TSFE']->page['uid'],'rU'=>$GLOBALS['TSFE']->fe_user->user['uid'],'cmd'=>'edit'));
//	$url = t3lib_div::linkThisScript(array('id'=>$GLOBALS['TSFE']->page['uid'],'cmd'=>'edit'));	
	debug($url);
	header('location: '.$url);
}
*/

$cmd = t3lib_div::_GP('cmd');
if(!isset($cmd)) {
	$url = t3lib_div::linkThisScript(array('id'=>$GLOBALS['TSFE']->page['uid'],'cmd'=>'edit'));	
//	debug($url);
	header('location: '.$url);
}
	

?>