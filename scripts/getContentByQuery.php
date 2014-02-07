<?php
/* **************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Niedersächsische Staats- und Universitätsbibliothek
 *  (c) 2014 Jochen Kothe (kothe@sub.uni-goettingen.de) (jk@profi-php.de)
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

$solrPhpsUrl = 'http://localhost:8080/digizeit/select/?wt=phps';

if(!isset($q)) {
    exit();
}

if(isset($_GET['format'])) {
    $format = 'csv';
} else {
    $format = '';
}

$params = array();
$params['q'] = urlencode(htmlentities(trim($q), ENT_QUOTES, "UTF-8"));
if(isset($rows)) {
    $params['rows'] = htmlentities(trim($rows), ENT_QUOTES, "UTF-8");
}
if(isset($sort)) {
    $params['sort'] = urlencode(htmlentities(trim($sort), ENT_QUOTES, "UTF-8"));
}
$strSolr = '';
foreach ($params as $key => $val) {
    $strSolr .='&' . $key . '=' . $val;
}

if(isset($namepart)) {
    $namepart = str_replace('/','__',htmlentities(trim($namepart), ENT_QUOTES, "UTF-8"));
} else {
    $namepart = 'misc';
}

$arrStruct = array();

$solrResult = file_get_contents($solrPhpsUrl.$strSolr);
$arrSolr = unserialize($solrResult);
foreach($arrSolr['response']['docs'] as $key=>$val) {
    $val['ACL'] = _unserialize($val['ACL']);
    $val['STRUCTRUN'] = _unserialize($val['STRUCTRUN']);
    $val['PRE'] = _unserialize($val['PRE']);
    $val['SUC'] = _unserialize($val['SUC']);
    $arrStruct[] = $val;         
}
//print_r($arrStruct);
//exit;

if($format == 'csv') {
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: inline; filename="'.date('Y-m-d',time()).'_dz_'.$namepart.'.csv"');
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
        echo substr(trim($struct['DATEMODIFIED']),6,2).'.'.substr(trim($struct['DATEMODIFIED']),4,2).'.'.substr(trim($struct['DATEMODIFIED']),0,4)."\t";
        echo substr(trim($struct['DATEINDEXED']),6,2).'.'.substr(trim($struct['DATEINDEXED']),4,2).'.'.substr(trim($struct['DATEINDEXED']),0,4)."\n";
    }
    exit();
} else {
    echo '<div id="mydigizeit_filter">'."\n";
    echo '<br /><hr />'."\n";
    foreach($arrStruct as $struct) {
        $link = 'http://www.digizeitschriften.de/dms/img/?PPN='.trim($struct['PPN']);
        echo '<li>'."\n";
        echo '<b>Titel: </b><a href="'.$link.'">'.trim($struct['TITLE']).'</a><br />'."\n";
        echo '<b>Lizenzen: </b>'.implode(', ',$struct['ACL']).'<br />'."\n";
        echo '<b>Kollektion: </b>'.$struct['DC'].'<br />'."\n";
        echo '<b>Änderungsdatum: </b>'.substr(trim($struct['DATEMODIFIED']),6,2).'.'.substr(trim($struct['DATEMODIFIED']),4,2).'.'.substr(trim($struct['DATEMODIFIED']),0,4).'<br />'."\n";
        echo '<b>Importdatum: </b>'.substr(trim($struct['DATEINDEXED']),6,2).'.'.substr(trim($struct['DATEINDEXED']),4,2).'.'.substr(trim($struct['DATEINDEXED']),0,4).'<br /><br />'."\n";
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