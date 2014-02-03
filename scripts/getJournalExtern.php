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

$strVolumeAboQuery = urlencode('DOCSTRCT:periodical AND (ACL:ubheidelberg OR ACL:ubtuebingen OR ACL:ubfrankfurt)');
//$strVolumeAboQuery = urlencode('PPN:PPN522562264_0052');

$arrStruct = array();

$solrResult = file_get_contents($solrPhpsUrl.$strVolumeAboQuery.'&rows=99999&sort=BYTITLE+asc,DC+asc');
$arrSolr = unserialize($solrResult);
foreach($arrSolr['response']['docs'] as $key=>$val) {
    $val['ACL'] = _unserialize($val['ACL']);
    $val['STRUCTRUN'] = _unserialize($val['STRUCTRUN']);
    $arrStruct[] = $val;         
}
//print_r($arrStruct);
//exit;

if($_GET['format'] == 'csv') {
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: inline; filename="'.date('Y-m-d',time()).'_JournalExtern.csv"');
    echo 'URL'."\t";
    echo 'Titel'."\t";
    echo 'Lizenzen'."\t";
    echo 'Kollektion'."\t";
    echo 'Änderungsdatum'."\t";
    echo 'Importdatum'."\n";
    foreach($arrStruct as $struct) {
        echo'http://www.digizeitschriften.de/dms/img/?PPN='.trim($struct['PPN'])."\t";
        echo trim($struct['TITLE'])."\t";
        echo implode(', ',$struct['ACL'])."\t";
        echo $struct['DC']."\t";
        echo substr(trim($struct['DATEMODIFIED']),-2).'.'.substr(trim($struct['DATEMODIFIED']),2,-4).'.'.substr(trim($struct['DATEMODIFIED']),0,4)."\t";
        echo substr(trim($struct['DATEINDEXED']),-2).'.'.substr(trim($struct['DATEINDEXED']),2,-4).'.'.substr(trim($struct['DATEINDEXED']),0,4)."\n";
    }
} else {
    echo '<div id="mydigizeit_filter">'."\n";
    echo '<br /><hr />'."\n";
    foreach($arrStruct as $struct) {
        $link = 'http://www.digizeitschriften.de/dms/img/?PPN='.trim($struct['PPN']);
        echo '<li>'."\n";
        echo '<b>Titel: </b><a href="'.$link.'">'.trim($struct['TITLE']).'</a><br />'."\n";
        echo '<b>Lizenzen: </b>'.implode(', ',$struct['ACL']).'<br />'."\n";
        echo '<b>Kollektion: </b>'.$struct['DC'].'<br />'."\n";
        echo '<b>Änderungsdatum: </b>'.substr(trim($struct['DATEMODIFIED']),-2).'.'.substr(trim($struct['DATEMODIFIED']),2,-4).'.'.substr(trim($struct['DATEMODIFIED']),0,4).'<br />'."\n";
        echo '<b>Importdatum: </b>'.substr(trim($struct['DATEINDEXED']),-2).'.'.substr(trim($struct['DATEINDEXED']),2,-4).'.'.substr(trim($struct['DATEINDEXED']),0,4).'<br /><br />'."\n";
        echo '</li>'."\n";
        echo '<hr />'."\n";
    }
    echo '</div>'."\n";

}
?>