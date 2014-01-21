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
//#### INIT ##############################################
$start = microtime(true);
include_once('./config.php');
include_once('./functions.php');

if (!trim($_SERVER['argv'][1])) {
    exit();
} else {
    $global['strMetsUrl'] = trim($_SERVER['argv'][1]);
}

if (trim($_SERVER['argv'][2])) {
    $global['logID'] = trim($_SERVER['argv'][2]);
} else {
    $global['logID'] = false;
}

if (trim($_SERVER['argv'][3])) {
    $global['imgpipe'] = true;
} else {
    $global['imgpipe'] = false;
}
//#### END INIT #############################################
//#### MAIN #################################################

$global['mets'] = new DOMDocument('1.0', 'UTF-8');
$test = $global['mets']->load($global['strMetsUrl']);
if (!$test) {
    exit();
}
$global['xpath'] = new DOMXpath($global['mets']);
//register namespaces
setNSprefix($global['xpath']);

foreach ($global['PIDquery'] as $pidquery) {
    if ($global['xpath']->evaluate($pidquery)->length) {
        $global['PID'] = trim($global['xpath']->evaluate($pidquery)->item(0)->nodeValue);
        $node = $global['xpath']->evaluate($pidquery)->item(0);
        while (is_object($node) && $node->nodeName != 'mets:dmdSec') {
            $node = $node->parentNode;
        }
        if (is_object($node)) {
            $global['arrWork']['dmdID'] = trim($node->getAttribute('ID'));
        }
        break;
    }
}


getWork();
if (!$global['arrWork']['physID']) {
    //no physSequence -> nothing to do
    exit();
}


$global['itextPath'] = $global['cachePath'] . 'itext/' . enc_str($global['PID']) . '/';
if (!is_dir($global['itextPath'])) {
    mkdir($global['itextPath'], 0775);
    exec('chmod 775 ' . $global['itextPath']);
}
if (!is_dir($global['itextPath'])) {
    exit();
}

if (!$global['logID'] || $global['logID'] == $global['PID']) {
    $global['logID'] = $global['arrWork']['logID'];
    $global['filename'] = $global['PID'] . '.xml';
} else {
    $global['filename'] = $global['logID'] . '.xml';
}

//get Parents
$global['arrItext'] = getParents($global['logID']);
//print_r($global['arrItext']);
//get Childs
$depth = count($global['arrItext']) - 1;
//$arrChilds = array();
getChilds($depth, $depth, $global['logID']);
//print_r($global['arrItext']);
//exit();

$global['iText'] = new DOMDocument('1.0', 'UTF-8');
$global['iText']->formatOutput = true;
$root = new DOMElement('iText');
$global['iText']->appendChild($root);
$root->setAttribute('xmlns:xi', 'http://www.w3.org/2001/XInclude');
if ($global['arrItext'][0]['title']) {
    $root->setAttribute('title', trim($global['arrItext'][0]['title']));
}
if ($global['arrItext'][0]['author']) {
    $root->setAttribute('author', trim($global['arrItext'][0]['author']));
}
$root->setAttribute('keywords', trim($global['PID'] . ' ' . $global['filename']));

$include = new DOMElement('xi:include', '', 'http://www.w3.org/2001/XInclude');
$root->appendChild($include);
$include->setAttribute('href', $global['baseurl'] . 'dms/pdfcover/?metsFile=' . $global['PID'] . '&divID=' . $global['logID']);

$chapter = new DOMElement('chapter');
$root->appendChild($chapter);
$chapter->setAttribute('numberdepth', '0');


//All sections 
$section = $chapter;
foreach ($global['arrItext'] as $key => $arr) {
    //the very first
    if ($key == 0) {
        $title = getTitle($arr['title']);
        $chapter->appendChild($title);
        $section = $chapter;
    } else {
        $title = getTitle($arr['title']);
        $section = getSection($section, $arr['depth'], $title);
    }
    if (is_array($arr['physID'])) {
        foreach ($arr['physID'] as $_key => $arrPhys) {
            if ($arrPhys['logID'] && is_array($arrPhys['logID'])) {
                foreach ($arrPhys['logID'] as $arrLogID) {
                    $title = getTitle($arrLogID['title']);
                    $section = getSection($section, $arrLogID['depth'], $title);
                }
            }
            //images
            $image = getImage($arrPhys['image']);
            $section->appendChild($image);
            $newpage = $global['iText']->createElement('newpage');
            $section->appendChild($newpage);
        }
    }
}

$end = microtime(true) - $start;
$logline .= date('[d/M/Y:H:i:s O] ', time());
$logline .= ' ' . $global['strMetsUrl'];
$logline .= ' ' . $global['logID'];
$logline .= ' ' . number_format($end, 2, ',', '.') . 'sec' . "\n";
file_put_contents($global['logPath'] . 'mets2itext.log', $logline, FILE_APPEND);

$global['iText']->save($global['itextPath'] . enc_str($global['filename']));

//print_r($global['iText']->saveXML());
//#### END MAIN #############################################
?>


