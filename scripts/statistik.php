<?php
//debug($GLOBALS['TSFE']->fe_user->user);
$userID = $GLOBALS['TSFE']->fe_user->user['uid'];
$strFe_groups = $GLOBALS['TSFE']->fe_user->user['usergroup'];
$arrFe_groups = explode(',',$strFe_groups);

$arrCollections = array('Anglistik',
                                    'Arts',
                                    'Economics',
                                    'Education',
                                    'Egyptology',
                                    'Geology',
                                    'Germanistik',
                                    'History',
                                    'Law',
                                    'Librarianship',
                                    'Mathematics',
                                    'Musicology',
                                    'Orientalistik',
                                    'Philology',
                                    'Philosophy',
                                    'Religion',
                                    'Romanistik',
                                    'Sciences',
                                    'Sociology');


//logPath
$logPath = realpath(PATH_site.'/../counter/logs').'/';

//Get al Years
$arrYear = array();
$d = dir($logPath);
while($entry = $d->read()) {
	if(is_dir($logPath.$entry) && substr($entry,0,1)!='.') {
		if (!in_array(substr($entry,0,4),$arrYear)) {
			array_push($arrYear,substr($entry,0,4));
		}
	}
}
sort($arrYear);
 
//date
$prevMonth = (date('n', time())-1) < 1 ? '12' : substr('0'.(date('n', time())-1),-2);
//$prevMonth = (date('n', time())-1) < 1 ? (date('Y',time())-1).'12' : date('Y',time()).(date('n', time())-1);

//digizeit Admin Group
$digizeitAdmin = 10;

//pid licences
$sysLicensesPID = 114;

//pid Institutiton
$sysInstitutionPID = 115;
$sysInstitutionAdminPID = 116;




//Gesamtstatistik
if(in_array($digizeitAdmin,$arrFe_groups)) {
	echo '<h3>DigiZeitschriften</h3>'."\n";
	echo '<table>'."\n";
    $arrAll = array('all'=>'Gesamt', '12'=>'Subskription', 'free'=>'Open Access');
	foreach($arrAll as $index=>$name) {
	    echo '<tr>'."\n";
	    echo '<td valign="top" width="250">'.$name.'</td>'."\n";
	    foreach($arrYear as $year) {
		    //get last xlsfile
		    for($k=12;$k>0;$k--) {
			    if(is_file($logPath.$year.substr('0'.$k,-2).'/xls/'.$index.'.xls')) {
				    $xlsFile = $logPath.$year.substr('0'.$k,-2).'/xls/'.$index.'.xls';
				    $xlsName = $year;
				    break;
			    } else if(is_file($logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$index.'.xls')) {
				    $xlsFile = $logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$index.'.xls';
				    $xlsName = $year;
				    break;
                }
		    }
		    if($xlsFile) {
			    echo '<td valign="top" align="right" width="40"><a href="/fileadmin/scripts/statistik_getxls.php?xlsfile='.$xlsFile.'&xlsname='.$xlsName.'_'.$index.'.xls'.'">'.$xlsName.'</td>'."\n";
		    } else {
			    echo '<td valign="top" align="right" width="40">&nbsp</td>'."\n";
		    }
		    unset($xlsFile);
		    unset($xlsName);
	    }
	    echo '</tr>';
    }
	echo '</table>'."\n";
}

//Collections
$strCollections = '"'.implode('","',$arrCollections).'"';
if(in_array($digizeitAdmin,$arrFe_groups)) {
	$where = '';
} else {
	$where = 'uid in ('.$strFe_groups.') and ';
}
$resPublisher =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title, description','fe_groups',$where.' title in ('.$strCollections.') and pid="'.$sysLicensesPID.'" and not deleted','','description,title');
//debug($where.' title in ('.$strCollections.') and pid="'.$sysLicensesPID.'" and not deleted','','description,title');
while($arrPublisher = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resPublisher)) {
	$publisher[$arrPublisher['uid']]['title'] = trim($arrPublisher['title']);
	$publisher[$arrPublisher['uid']]['description'] = trim($arrPublisher['description']) ? trim($arrPublisher['description']) : trim($arrPublisher['title']);
}
if(is_array($publisher)) {
	echo '<h3>Collection</h3>'."\n";
	echo '<table>'."\n";
	foreach($publisher as $uid=>$val) {
		echo '<tr>'."\n";
		echo '<td valign="top" width="250">'.$val['description'].'</td>'."\n";
		foreach($arrYear as $year) {
			//get last xlsfile
			for($k=12;$k>0;$k--) {
				if(is_file($logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$uid.'.xls')) {
					$xlsFile = $logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$uid.'.xls';
					$xlsName = $year;
					break;
				}
			}
			if($xlsFile) {
				echo '<td valign="top" align="right" width="40"><a href="/fileadmin/scripts/statistik_getxls.php?xlsfile='.$xlsFile.'&xlsname='.$xlsName.'_col'.$uid.'.xls">'.$xlsName.'</td>'."\n";
			} else {
				echo '<td valign="top" align="right" width="40">&nbsp</td>'."\n";
			}
			unset($xlsFile);
			unset($xlsName);
		}
		echo '</tr>';
        echo '<tr><td colspan="'.(count($arrYear)+1).'"><hr/></td></tr>'."\n";
	}
	echo '</table>'."\n";
}


//Verlage
unset($publisher);
$strExclude = '"'.implode('","',$arrCollections).'"';
$strExclude .= ',"MohrSiebeckSchnupper","Gesamtabo","gesperrt"';
//debug($strExclude);
if(in_array($digizeitAdmin,$arrFe_groups)) {
	$where = '';
} else {
	$where = 'uid in ('.$strFe_groups.') and ';
}
$resPublisher =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title, description','fe_groups',$where.' title not in ('.$strExclude.') and pid="'.$sysLicensesPID.'" and not deleted','','description,title');
while($arrPublisher = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resPublisher)) {
	$publisher[$arrPublisher['uid']]['title'] = trim($arrPublisher['title']);
	$publisher[$arrPublisher['uid']]['description'] = trim($arrPublisher['description']) ? trim($arrPublisher['description']) : trim($arrPublisher['title']);
}
//debug($publisher);
if(is_array($publisher)) {
	echo '<h3>Publisher</h3>'."\n";
	echo '<table>'."\n";
	foreach($publisher as $uid=>$val) {
		echo '<tr>'."\n";
		echo '<td valign="top" width="250">'.$val['description'].'</td>'."\n";
		foreach($arrYear as $year) {
			//get last xlsfile
			for($k=12;$k>0;$k--) {
				if(is_file($logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$uid.'.xls')) {
					$xlsFile = $logPath.$year.substr('0'.$k,-2).'/publisher/xls/'.$uid.'.xls';
					$xlsName = $year;
					break;
				}
			}
			if($xlsFile) {
				echo '<td valign="top" align="right" width="40"><a href="/fileadmin/scripts/statistik_getxls.php?xlsfile='.$xlsFile.'&xlsname='.$xlsName.'_pub'.$uid.'.xls">'.$xlsName.'</td>'."\n";
			} else {
				echo '<td valign="top" align="right" width="40">&nbsp</td>'."\n";
			}
			unset($xlsFile);
			unset($xlsName);
		}
		echo '</tr>';
        echo '<tr><td colspan="'.(count($arrYear)+1).'"><hr/></td></tr>'."\n";
	}
	echo '</table>'."\n";
}


//Institutionen
$strExclude = '"MohrSiebeck","SHirzelVerlag","FranzSteinerVerlag"';
if(in_array($digizeitAdmin,$arrFe_groups)) {
	$where = 'fe1.username=fe2.username and ';
} else {
	$where = 'fe1.username=fe2.username and fe1.uid="'.$userID.'" and ';
    //Benutzeranmeldung?
    $resUser =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','uid="'.$userID.'" and pid="'.$sysInstitutionAdminPID.'"');
    if($GLOBALS['TYPO3_DB']->sql_num_rows($resUser)) {
        //get grouped Institutions
        $resGroups = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_groups','pid="'.$sysInstitutionPID.'"');
        $tmpWhere = '';
        while($arrGroups = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resGroups)) {
            if(in_array($arrGroups['uid'],$arrFe_groups)) $tmpWhere .= 'FIND_IN_SET("'.$arrGroups['uid'].'",fe2.usergroup) or ';
        }
        if($tmpWhere) $where = 'fe1.username=fe2.username and ('.substr($tmpWhere,0,-3).') and ';
    }
}
$resInst =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe1.uid as adminID, fe2.uid as institutionID, fe1.username, fe1.company, fe1.name','fe_users as fe1, fe_users as fe2',$where.' fe1.pid="'.$sysInstitutionAdminPID.'" and fe2.pid="'.$sysInstitutionPID.'" and not fe2.disable and not fe2.deleted and not fe1.disable and not fe1.deleted and if(fe1.starttime,fe1.starttime<=UNIX_TIMESTAMP(),1) and if(fe1.endtime>0,fe1.endtime>=UNIX_TIMESTAMP(),1)','','fe1.username,fe1.company,fe1.name');
while($arrInst = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resInst)) {
	$institution[$arrInst['institutionID']]['username'] = trim($arrInst['username']);
	$institution[$arrInst['institutionID']]['company'] = trim($arrInst['company']) ? trim($arrInst['company']) : trim($arrInst['name']);
	$institution[$arrInst['institutionID']]['company'] = $institution[$arrInst['institutionID']]['company'] ? $institution[$arrInst['institutionID']]['company'] : trim($arrInst['username']);
}
//debug($institution);
if(is_array($institution)) {
	echo '<h3>Institution</h3>'."\n";
	echo '<table>'."\n";
	foreach($institution as $uid=>$val) {
		echo '<tr>'."\n";
		echo '<td valign="top" width="250">'.$val['company'].'</td>'."\n";
		foreach($arrYear as $year) {
			//get last xlsfile
			for($k=12;$k>0;$k--) {
				if(is_file($logPath.$year.substr('0'.$k,-2).'/abo/xls/'.$uid.'.xls')) {
					$xlsFile = $logPath.$year.substr('0'.$k,-2).'/abo/xls/'.$uid.'.xls';
					$xlsName = $year;
					break;
				}
			}
			if($xlsFile) {
				echo '<td valign="top" align="right" width="40"><a href="/fileadmin/scripts/statistik_getxls.php?xlsfile='.$xlsFile.'&xlsname='.$xlsName.'_abo'.$uid.'.xls">'.$xlsName.'</td>'."\n";
			} else {
				echo '<td valign="top" align="right" width="40">&nbsp</td>'."\n";
			}
			unset($xlsFile);
			unset($xlsName);
		}
		echo '</tr>';
        echo '<tr><td colspan="'.(count($arrYear)+1).'"><hr/></td></tr>'."\n";
	}
	echo '</table>'."\n";
}

?>
