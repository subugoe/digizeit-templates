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
//####################################################################
//### SAMPLES ########################################################
//####################################################################
//alt mit Heft!
//<URL>/issn/year/volume/issue/page
//http://www.digizeitschriften.de/link/0028-1042/1922/10/13/296 Gesamtabo
//http://www.digizeitschriften.de/link/1619-3989/1846/7/4/55    free
//neu ohne Heft!
//<URL>/issn/year/volume/page
//http://www.digizeitschriften.de/link/0028-1042/1922/10/296    Gesamtabo
//http://www.digizeitschriften.de/link/1619-3989/1846/7/55      free
//
    // Debug
//http://www.digizeitschriften.de/fileadmin/scripts/rewrites/issnlink.php?1619-3989/1846/7/55
//
    //####################################################################
//### END SAMPLES ####################################################
//####################################################################
//####################################################################
//### INIT ###########################################################
//####################################################################

$solrPhpsUrl = 'http://localhost:8080/digizeit/select/?wt=phps';
$metsPath = '/storage/digizeit/mets_repository/indexed_mets/';

$arrQuery = explode('/', htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8"));

/*
  echo '<pre>';
  print_r($arrQuery);
  echo '</pre>';
  exit();
 */

if (count($arrQuery) < 5) {
    $arrIndex = array('ISSN', 'YEAR', 'VOLUME', 'PAGE');
} else {
    $arrIndex = array('ISSN', 'YEAR', 'VOLUME', 'ISSUE', 'PAGE');
}
foreach ($arrIndex as $key => $val) {
    if (isset($arrQuery[$key]))
        $arr[$val] = $arrQuery[$key];
}
if (array_key_exists('ISSUE', $arr) && !$arr['ISSUE'])
    unset($arr['ISSUE']);

//####################################################################
//### END INIT ########################################################
//####################################################################
//####################################################################
//### FUNCTIONS ######################################################
//####################################################################
function _unserialize($str) {
    $ret = json_decode($str, true);
    if (!is_array($ret)) {
        $ret = unserialize($str);
    }
    return $ret;
}

function getPhysID($ppn, $page) {
    global $metsPath;
    global $metsxpath;
    global $DMDID;
    global $PHYSID;
    $mets = new DOMDocument('1.0', 'UTF-8');
    $test = $mets->load($metsPath . $ppn . '.xml');
    if ($test) {
        $metsxpath = new DOMXpath($mets);
        setNSprefix($metsxpath);
        $divList = $metsxpath->evaluate('/mets:mets/mets:structMap[@TYPE="PHYSICAL"]/mets:div/mets:div[@ORDERLABEL="' . trim($page) . '"]');
        if ($divList->length) {
            if ($divList->item(0)->hasAttribute('ID')) {
                $PHYSID = $divList->item(0)->getAttribute('ID');
                $DMDID = getDmdToPhys($PHYSID);
                return true;
            } else {
                return false;
            }
        }
    }
}

function getDmdToPhys($physid) {
    global $metsxpath;
    $idList = $metsxpath->evaluate('/mets:mets/mets:structLink/mets:smLink[@xlink:to="' . $physid . '"]/attribute::xlink:from');
    if ($idList->length) {
        $tmpID = getDmdToLog($idList->item(($idList->length - 1))->nodeValue);
        if ($tmpID) {
            return $tmpID;
        } else {
            return $idList->item(($idList->length - 1))->nodeValue;
        }
    }
}

function getDmdToLog($logid) {
    global $metsxpath;
    $idList = $metsxpath->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="' . $logid . '"]/attribute::DMDID');
    if ($idList->length) {
        return $idList->item(0)->nodeValue;
    } else {
        return false;
    }
}

function setNSprefix(&$xpath, $node = false) {
    if (!$node) {
        $xqueryList = $xpath->evaluate('*[1]');
        if ($xqueryList->length) {
            setNSprefix($xpath, $xqueryList->item(0));
        }
    }
    if (is_object($node)) {
        if ($node->prefix) {
            $xpath->registerNamespace(strtolower($node->prefix), $node->namespaceURI);
        }
        $xqueryList = $xpath->evaluate('following-sibling::*[name()!="' . $node->nodeName . '"][1]', $node);
        if ($xqueryList->length) {
            setNSprefix($xpath, $xqueryList->item(0));
        }
        if ($node->firstChild) {
            setNSprefix($xpath, $node->firstChild);
        }
        if(is_object($node)) {
            if ($node->attributes) {
                foreach ($node->attributes as $attribute) {
//                    if ($attribute->prefix && !$arrNS[strtolower($attribute->prefix)]) {
                    if ($attribute->prefix) {
                        $xpath->registerNamespace(strtolower($attribute->prefix), $attribute->namespaceURI);
                    }
                }
            }
        }
    }
    unset($xqueryList);
    unset($node);
    unset($attribute);
}

//####################################################################
//### END FUNCTIONS ##################################################
//####################################################################
//####################################################################
//### MAIN ###########################################################
//####################################################################

$URL = '/dms/ssearch/';

//get Journal
if ($arr['ISSN']) {
    $arr['ISSN'] = trim(str_replace('-', '', $arr['ISSN']));
    $arr['ISSN'] = trim(str_replace('X', '?', $arr['ISSN']));
    /*
      echo '<pre>';
      print_r($arr);
      exit();
     */
    $arrSolr = unserialize(file_get_contents($solrPhpsUrl . '&q=' . urlencode('ISSN:' . $arr['ISSN']) . '&rows=1'));
    if ($arrSolr['response']['numFound']) {
        $arrTmp = $arrSolr['response']['docs'];
        $arr['Journal'] = $arrTmp[0];
        $PPN = $arr['Journal']['PPN'];
        $URL = '/dms/img/?PPN=' . $PPN;
        unset($arrTmp);

        //get Volume from VOLUME
        if ($arr['VOLUME']) {
            $arrSolr = unserialize(file_get_contents($solrPhpsUrl . '&q=' . urlencode('IDPARENTDOC:' . trim($arr['Journal']['PPN']) . ' AND CURRENTNO:"' . trim($arr['VOLUME']) . '"') . '&rows=1'));
            if ($arrSolr['response']['numFound']) {
                $arrTmp = $arrSolr['response']['docs'];
                $arrTmp[0]['STRUCTRUN'] = _unserialize($arrTmp[0]['STRUCTRUN']);
                $arr['Volume'] = $arrTmp[0];
                $DMDID = $arr['Volume']['STRUCTRUN'][(count($arr['Volume']['STRUCTRUN']) - 1)]['DMDID'];
                $PPN = $arr['Volume']['PPN'];
                $URL = '/dms/img/?PPN=' . $PPN;
                unset($arrTmp);
            }
        }
        //get Volume from YEAR
        if (!isset($arr['Volume']) && $arr['YEAR']) {
            $arrSolr = unserialize(file_get_contents($solrPhpsUrl . '&q=' . urlencode('IDPARENTDOC:' . trim($arr['Journal']['PPN']) . ' AND YEARPUBLISH:"' . trim($arr['YEAR']) . '"') . '&rows=1'));
            if ($arrSolr['response']['numFound']) {
                $arrTmp = $arrSolr['response']['docs'];
                $arrTmp[0]['STRUCTRUN'] = _unserialize($arrTmp[0]['STRUCTRUN']);
                $arr['Volume'] = $arrTmp[0];
                $DMDID = $arr['Volume']['STRUCTRUN'][(count($arr['Volume']['STRUCTRUN']) - 1)]['DMDID'];
                $PPN = $arr['Volume']['PPN'];
                $URL = '/dms/img/?PPN=' . $PPN;
                unset($arrTmp);
            }
        }
        if ($arr['Volume']) {
            if ($arr['PAGE']) {
                if (getPhysID($PPN, $arr['PAGE'])) {
                    $URL = '/dms/img/?PPN=' . $PPN . '&DMDID=' . $DMDID . '&PHYSID=' . $PHYSID;
                }
            }
        }
    }
}

/*
  echo '<pre>';
  print_r($arr);
  print_r('PPN '.$PPN."\n");
  print_r('DMDID '.$DMDID."\n");
  print_r('PHYSID '.$PHYSID."\n");
  echo '</pre>';
  echo $URL;
  exit();
 */
header('location: ' . $URL);
//####################################################################
//### END MAIN #######################################################
//####################################################################
?>
