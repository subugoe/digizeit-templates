<?php
/* **************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Niedersächsische Staats- und Universitätsbibliothek
 *  (c) 2010 Jochen Kothe (kothe@sub.uni-goettingen.de) (jk@profi-php.de)
 *  All rights reserved
 *
 *  This script is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(0);
$scriptPath = dirname(__FILE__);


$solrPhpsUrl = "http://localhost:8080/digizeit/select/?wt=phps&q=";

$strVolumeAboQuery = urlencode('ISWORK:1 AND ACL:gesamtabo AND NOT(ACL: free OR ACL:ubheidelberg OR ACL:ubtuebingen OR ACL:ubfrankfurt)');

if(isset($_GET['format'])) {
    $format = 'csv';
} else {
    $format = '';
}
$arrStruct = array();

$solrResult = file_get_contents($solrPhpsUrl.$strVolumeAboQuery.'&rows=99999&sort=BYTITLE+asc,CURRENTNOSORT+asc');
$arrSolr = unserialize($solrResult);
foreach($arrSolr['response']['docs'] as $key=>$val) {
    $_solrResult = file_get_contents($solrPhpsUrl.urlencode('IDPARENTDOC:"'.trim($val['PPN']).'"').'&rows=99999');
    $_arrSolr = unserialize($_solrResult);
    foreach($_arrSolr['response']['docs'] as $_key=>$_val) {
        $_val['ACL'] = _unserialize($_val['ACL']);
        if(in_array('free',$_val['ACL']) || in_array('gesperrt',$_val['ACL'])) {
            $_val['ACLZS'] = _unserialize($val['ACL']);
            $_val['STRUCTRUN'] = _unserialize($_val['STRUCTRUN']);         
            $arrStruct[] = $_val;  
        }
    }     
}

if($format == 'csv') {
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: inline; filename="'.date('Y-m-d',time()).'_ArticleByACL.csv"');
    echo 'URL'."\t";
    echo 'Titel'."\t";
    echo 'Autor'."\t";
    echo 'Lizenzen'."\t";
    echo 'Typ'."\t";
    echo 'Zeitschrift'."\t";
    echo 'Lizenzen Zeitschrift'."\t";
    echo 'Änderungsdatum'."\t";
    echo 'Importdatum'."\n";
    foreach($arrStruct as $struct) {
        $link = 'http://www.digizeitschriften.de/dms/img/?PPN='.trim($struct['STRUCTRUN'][1]['PPN']).'&DMDID='.$struct['STRUCTRUN'][count($struct['STRUCTRUN'])-1]['DMDID'];
        echo $link."\t";
        echo trim($struct['TITLE'])."\t";
        echo trim($struct['CREATOR'])."\t";
        echo implode(', ',$struct['ACL'])."\t";
        echo $struct['DOCSTRCT']."\t";
        echo trim($struct['STRUCTRUN'][1]['TITLE']).' '.trim($struct['STRUCTRUN'][1]['CURRENTNO'])."\t";
        echo implode(', ',$struct['ACLZS'])."\t";
        echo substr(trim($struct['DATEMODIFIED']),-2).'.'.substr(trim($struct['DATEMODIFIED']),2,-4).'.'.substr(trim($struct['DATEMODIFIED']),0,4)."\t";
        echo '<b>Importdatum: </b>'.substr(trim($struct['DATEINDEXED']),-2).'.'.substr(trim($struct['DATEINDEXED']),2,-4).'.'.substr(trim($struct['DATEINDEXED']),0,4)."\n";
    }
    exit();
} else {
    echo '<div id="mydigizeit_filter">'."\n";
    echo '<br /><hr />'."\n";
    foreach($arrStruct as $struct) {
        $link = 'http://www.digizeitschriften.de/dms/img/?PPN='.trim($struct['STRUCTRUN'][1]['PPN']).'&DMDID='.$struct['STRUCTRUN'][count($struct['STRUCTRUN'])-1]['DMDID'];
        echo '<li>'."\n";
        echo '<b>Titel: </b><a href="'.$link.'">'.trim($struct['TITLE']).'</a><br />'."\n";
        echo '<b>Autor: </b>'.trim($struct['CREATOR']).'<br />'."\n";
        echo '<b>Lizenzen: </b>'.implode(', ',$struct['ACL']).'<br />'."\n";
        echo '<b>Typ: </b><a href="'.$link.'">'.trim($struct['DOCSTRCT']).'</a><br />'."\n";
        echo '<b>Zeitschrift: </b>'.trim($struct['STRUCTRUN'][1]['TITLE']).' '.trim($struct['STRUCTRUN'][1]['CURRENTNO']).'<br />'."\n";
        echo '<b>Lizenzen Zeitschrift: </b>'.implode(', ',$struct['ACLZS']).'<br />'."\n";
        echo '<b>Änderungsdatum: </b>'.substr(trim($struct['DATEMODIFIED']),-2).'.'.substr(trim($struct['DATEMODIFIED']),2,-4).'.'.substr(trim($struct['DATEMODIFIED']),0,4).'<br />'."\n";
        echo '<b>Importdatum: </b>'.substr(trim($struct['DATEINDEXED']),-2).'.'.substr(trim($struct['DATEINDEXED']),2,-4).'.'.substr(trim($struct['DATEINDEXED']),0,4).'<br /><br />'."\n";
        echo '</li>'."\n";
        echo '<hr />'."\n";
    }
    echo '</div>'."\n";
}

#######################################################################
function _unserialize($str) {
    $ret = json_decode($str,true);
    if(!is_array($ret)) {
        $ret = unserialize($str);
    }
    return $ret;
}
########################################################################

?>
