#!/usr/bin/php5
<?php
/* * *************************************************************
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
error_reporting(E_ALL);
//error_reporting(0);
$scriptPath = dirname(__FILE__);

$dateindexed = '[20120101 TO 20121231]';

$arrJournals = array('PPN522563589', 'PPN385984421', 'PPN687982782', 'PPN687986613', 'PPN523132190', 'PPN391118072', 'PPN509215866', 'PPN345858735', 'PPN597796971', 'PPN598187510', 'PPN602167701', 'PPN602167337', 'PPN602167531', 'PPN522563147', 'PPN522562264', 'PPN522561411', 'PPN523137087', 'PPN523137001', 'PPN523137222', 'PPN523137214', 'PPN523137273', 'PPN48768561X', 'PPN487700287', 'PPN487857054', 'PPN523137710', 'PPN487859057', 'PPN513009361', 'PPN513009817', 'PPN34520381X', 'PPN385984391', 'PPN391365657', 'PPN391365711', 'PPN366382810', 'PPN345571509', 'PPN513339353', 'PPN345204123', 'PPN578671514', 'PPN345575296', 'PPN509092632', 'PPN513613439', 'PPN513613897', 'PPN513614184', 'PPN513648887', 'PPN510319696', 'PPN345572629', 'PPN338185704', 'PPN511864582', 'PPN338182551', 'PPN338286934', 'PPN338281509', 'PPN338288422', 'PPN635360098', 'PPN598188037', 'PPN598190155', 'PPN598188592', 'PPN345616359', 'PPN598191607', 'PPN345575229', 'PPN365362204', 'PPN365339741', 'PPN523141513', 'PPN523141572', 'PPN668632747', 'PPN514293268', 'PPN345574893', 'PPN597796831', 'PPN345574613', 'PPN345616367', 'PPN598186565', 'PPN34561688X', 'PPN345616871', 'PPN345574974', 'PPN345574966', 'PPN598192565', 'PPN490492916', 'PPN507831411', 'PPN51334117X', 'PPN51032052X', 'PPN338212566', 'PPN345203690', 'PPN345203720', 'PPN345572572', 'PPN331411849', 'PPN514432020', 'PPN345203674', 'PPN503543292', 'PPN503542318', 'PPN503540463', 'PPN345572319', 'PPN345572211', 'PPN34557219X', 'PPN34557155X', 'PPN385489110', 'PPN345572157', 'PPN509862098', 'PPN345204425', 'PPN345858352', 'PPN345617002', 'PPN483856525', 'PPN51145063X', 'PPN511450877', 'PPN523131127', 'PPN558786588', 'PPN558786499');

$arrYearpublish = array('[1926 TO 1995]', '[1996 TO 9999]');

$solrPhpsUrl = "http://localhost:8080/digizeit/select/?wt=phps&q=";

if (is_file('./' . date('Ymd', time()) . '_vgwortpagecount.phps')) {
    $arrResult = unserialize(file_get_contents('./' . date('Ymd', time()) . '_vgwortpagecount.phps'));
} else {
    $arrResult = array();
    foreach ($arrJournals as $journal) {
        print_r('.');
        foreach ($arrYearpublish as $yearpublish) {
            $strQuery = urlencode('IDPARENTDOC:' . $journal . ' AND ISWORK:1 AND DATEINDEXED:' . $dateindexed . ' AND YEARPUBLISH:' . $yearpublish);
            $solrResult = file_get_contents($solrPhpsUrl . $strQuery . '&rows=99999');
            $arrSolr = unserialize($solrResult);
            foreach ($arrSolr['response']['docs'] as $key => $val) {
                $arrResult[$journal][$yearpublish][$key] = $val;
                $arrResult[$journal][$yearpublish][$key]['pages'] = exec('ls /storage/digizeit/tiff/' . $arrResult[$journal][$yearpublish][$key]['PPN'] . '/0*.tif|wc -w');
            }
        }
    }
    file_put_contents('./' . date('Ymd', time()) . '_vgwortpagecount.phps', serialize($arrResult));
}

foreach ($arrResult as $ppn => $journal) {
    $_strQuery = urlencode('IDPARENTDOC:' . $ppn . ' AND ISWORK:1 AND DATEINDEXED:[00000000 TO 20121231]');
    $_solrResult = file_get_contents($solrPhpsUrl . $_strQuery . '&rows=1&sort=DATEINDEXED+desc');
    $_arrSolr = unserialize($_solrResult);
    if ($_arrSolr['response']['numFound']) {
//		print_r($_arrSolr['response']['docs'][0]['DATEINDEXED'].'|');
        $date = substr($_arrSolr['response']['docs'][0]['DATEINDEXED'], 6, 2) . '.' . substr($_arrSolr['response']['docs'][0]['DATEINDEXED'], 4, 2) . '.' . substr($_arrSolr['response']['docs'][0]['DATEINDEXED'], 0, 4);
    } else {
        $_strQuery = urlencode('IDPARENTDOC:' . $ppn . ' AND ISWORK:1 AND DATEMODIFIED:[00000000 TO 20121231]');
        $_solrResult = file_get_contents($solrPhpsUrl . $_strQuery . '&rows=1&DATEMODIFIED+desc');
        $_arrSolr = unserialize($_solrResult);
        if ($_arrSolr['response']['numFound']) {
//			print_r('M_'.$_arrSolr['response']['docs'][0]['DATEMODIFIED'].'|');
            $date = substr($_arrSolr['response']['docs'][0]['DATEMODIFIED'], 6, 2) . '.' . substr($_arrSolr['response']['docs'][0]['DATEMODIFIED'], 4, 2) . '.' . substr($_arrSolr['response']['docs'][0]['DATEMODIFIED'], 0, 4);
        } else {
            print_r('|');
        }
    }
//	if(substr($arrSolr['response']['docs'][0]['DATEINDEXED'],0,4)=='2012') {
    $strQuery = urlencode('PPN:' . $ppn);
    $solrResult = file_get_contents($solrPhpsUrl . $strQuery . '&rows=1');
    $arrSolr = unserialize($solrResult);
    print_r(trim($arrSolr['response']['docs'][0]['TITLE']) . '|');
    print_r($ppn . '|');
    if (isset($arrSolr['response']['docs'][0]['PUBLISHER'])) {
        print_r(trim($arrSolr['response']['docs'][0]['PUBLISHER']) . '|');
    } else {
        print_r('|');
    }
    foreach ($arrYearpublish as $yearpublish) {
        $pages = 0;
        if (isset($arrResult[$ppn][$yearpublish])) {
            if (is_array($arrResult[$ppn][$yearpublish])) {
                foreach ($arrResult[$ppn][$yearpublish] as $volume) {
                    $pages += $volume['pages'];
                }
            }
        }
        print_r($pages . '|');
    }
    print_r('|');
    print_r($date . "\n");
//	}	
}


/*

  foreach($arrSolr['response']['docs'] as $key=>$val) {
  $val['ACL'] = _unserialize($val['ACL']);
  $val['STRUCTRUN'] = _unserialize($val['STRUCTRUN']);
  $arrStruct[] = $val;
  }
 */

//print_r($arrResult);
//exit;
#######################################################################
function _unserialize($str) {
    $ret = json_decode($str, true);
    if (!is_array($ret)) {
        $ret = unserialize($str);
    }
    return $ret;
}

########################################################################
?>