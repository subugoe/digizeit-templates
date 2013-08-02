<?php
set_time_limit(0);
error_reporting(E_ALL);
//error_reporting(0);
$scriptPath = dirname(__FILE__);

#######################################################################
function _unserialize($str) {
    $ret = json_decode($str,true);
    if(!is_array($ret)) {
        $ret = unserialize($str);
    }
    return $ret;
}
########################################################################

$solrPhpsUrl = "http://localhost:8080/digizeit/select/?wt=phps&q=";

$strVolumeAboQuery = urlencode('ISWORK:1 AND ACL:gesamtabo AND NOT(ACL: free OR ACL:ubheidelberg OR ACL:ubtuebingen OR ACL:ubfrankfurt)');
//$strVolumeAboQuery = urlencode('PPN:PPN522562264_0052');

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

if($_GET['format'] == 'csv') {
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
?>
