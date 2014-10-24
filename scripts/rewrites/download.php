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
//error_reporting(E_ALL);
error_reporting(0);

define('__DZROOT__', realpath(__DIR__ . '/../../../../'));

$checkCommand = '/usr/bin/gs -q -dNOPAUSE -sDEVICE=nullpage -sOutputFile=/dev/null -dBATCH';

$serverUrl = $_SERVER['HTTPS'] ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
$scriptPath = dirname(__FILE__);
$logFile = __DZROOT__.'/logs/digizeit-content_log';

$gcsBaseUrl = 'http://localhost:8080/gcs/gcs?action=pdf&';
$pdfTitelPageUrl = $serverUrl . '/dms/pdf-titlepage/?';
$restrictPdf = $serverUrl . '/fileadmin/images/restrict.pdf';
$authServer = $serverUrl . '/dms/authserver/?';
$pdfCachePath = '/storage/digizeit/cache/pdf/';

$pdfwriter = __DZROOT__.'/htdocs/fileadmin/scripts/pdfwriter/';

if($logFile) {
    if (is_writable($logFile)) {
        $logging = true;
    } else {
        $logging = false;
    }
}

//sample call with rewrite: http://www.digizeitschriften.de/download/PPN342672002_0007/log10.pdf
//sample call without rewrite: http://www.digizeitschriften.de/fileadmin/scripts/rewrites/download.php?PPN342672002_0007/log10.pdf
//Beispiel:
//http://localhost:8080/gcs/gcs?action=pdf&metsFile='.$_REQUEST['PPN'].'&divID='.$_REQUEST['logID'].'&pdftitlepage='.urlencode($basehref).'%2Fdms%2Fpdf-titlepage%2F%3FmetsFile%3D'.$_REQUEST['PPN'].'%26divID%3D'.$_REQUEST['logID']));
// &metsFile=PPN342672002_0007
// &divID=log10
// &pdftitlepage=urlencode($pdfTitelPageUrl.'metsFile=PPN342672002_0007&divID=log10')


/*
print_r('<pre>');
print_r($_SERVER);
print_r($_REQUEST);
print_r('</pre>');
exit();
*/
$strUrlQuery = htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8");

$arrTmp = explode('/', $strUrlQuery);

################################################################################
// es werden nur URIs mit folgendem Aufbau verarbeitet
// <PPN>/<LOGID>.pdf
// Beispiel: PPN342672002_0007/log10.pdf
// ###############################################################################
if (count($arrTmp) != 2) {
    exit();
} 

$metsFile = trim($arrTmp[0]);

$_arrTmp = explode('.',$arrTmp[1]);

$divID = trim($_arrTmp[0]);

$pdftitlepage = urlencode($pdfTitelPageUrl.'metsFile='.$metsFile.'&divID='.$divID);

unset($arrTmp);
unset($_arrTmp);

//##############################################################################
// Hier Zugriffskontrolle einbauen wenn nötig.
// z.B. über IP-Adresse oder die Typo3 Sessions aus  $_SERVER['HTTP_COOKIE']
// dazu muss sichergestellt werden das die $csBaseUrl nicht direkt erreichbar ist sondern nur von diesem Server!
//##############################################################################
$acl = 0;
$acl = file_get_contents($authServer . 'PPN=' . $metsFile . '&DMDID=' . $divID . '&ipaddress=' . $_SERVER['REMOTE_ADDR'].'&fe_typo_user='.$_COOKIE['fe_typo_user']);

if (!$acl) {
    $pdf = file_get_contents($restrictPdf);
    header('Content-type: application/pdf');
    header('HTTP/1.0 401 Unauthorized');
    echo $pdf;
    exit();
}

$status = '200';

//kaputte Cachefiles loeschen
if(is_file($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf')) {
    if (filesize($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf') < 20480) {
        unlink($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf');
    }
}

//file_put_contents(__DZROOT__.'/tmp/bla.log', $gcsBaseUrl.'metsFile='.$metsFile.'&divID='.$divID.'&pdftitlepage='.$pdftitlepage."\n", FILE_APPEND);
//exit();
//################# ContentServer ############################################
if(!is_file($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf')) {
    mkdir($pdfCachePath.enc_str($metsFile), 0775, true);
//    file_put_contents($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf', file_get_contents($gcsBaseUrl.'metsFile='.$metsFile.'&divID='.$divID.'&pdftitlepage='.$pdftitlepage));

$tmp = file_get_contents($gcsBaseUrl.'metsFile='.$metsFile.'&divID='.$divID.'&pdftitlepage='.$pdftitlepage);    
$test =file_put_contents($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf', $tmp);
    
file_put_contents(__DZROOT__.'/tmp/bla.log', $test."\n", FILE_APPEND);
file_put_contents(__DZROOT__.'/tmp/bla.log', filesize($pdfCachePath.enc_str(metsFile).'/'.enc_str($divID).'.pdf').' - '.$pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf'."\n", FILE_APPEND);
//exit();
    
    @exec('chmod -R g+w '.$pdfCachePath.enc_str($metsFile));
    //check PDF
    $size = filesize($pdfCachePath.enc_str(metsFile).'/'.enc_str($divID).'.pdf');

    if($size == 0) {
        @unlink($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf');
        $status = '500';
    } else {
        $arrError = array();
        $error = exec($checkCommand.' '.str_replace('file://','',$cachePath).'pdf/'.enc_str($metsFile).'/'.enc_str($divID).'.pdf 2>&1',$arrError);
        if(trim(implode("\n",$arrError))) {
            @unlink($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf');
            $status = '500';
        }
    }
}
//############################################################################

if($status == '200') {
    header("Expires: -1");
    header("Cache-Control: post-check=0, pre-check=0");
    header("Pragma: no-cache");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header('Content-type: application/pdf');
    // download
    //header('Content-Disposition: attachment; filename="'.enc_str($metsFile).'_'.enc_str($divID).'.pdf"');
    // inline
    header('Content-Disposition: inline; filename="'.enc_str($metsFile).'_'.enc_str($divID).'.pdf"');
    header('Content-Length: '.filesize($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf'));  
    header("Content-Transfer-Encoding: binary");

    if(is_file($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf')) {
        $fpin = fopen($pdfCachePath.enc_str($metsFile).'/'.enc_str($divID).'.pdf','r');
        while(!feof($fpin)) {
            echo(fread($fpin, 8192));
            ob_flush();
            flush();
        }
        fclose($fpin);
    }
} else {
    //ERRORHANDLING;
}

//schreibe Contentserver kompatibles log -> ToDo Counter Auswertung verbessern!
//129.125.129.128 - 
//- 
//[01/Jun/2011:14:48:02 +0200]  
//"GET http://localhost:8080/gcs/gcs?action=pdf&metsFile=PPN345204425_0046&divID=log11... HTTP/1.1" 200 0 
//"http://www.digizeitschriften.de/dms/img/?PPN=PPN345204425_0046&DMDID=dmdlog11&PHYSID=phys85" 
//"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11 ( .NET CLR 3.5.30729; .NET4.0E)"
//print_r($_SERVER);
//$logline = $_SERVER['REMOTE_ADDR'].' - ';
$logline = $_SERVER['REMOTE_ADDR'].' - ';
$logline .= $_COOKIE['fe_typo_user'].' ';
$logline .= date('[d/M/Y:H:i:s O] ',$_SERVER['REQUEST_TIME']);        
$logline .= '"GET '.$gcsBaseUrl.'metsFile='.$metsFile.'&divID='.$divID.' HTTP/1.1" ';
$logline .= $status.' 0 - ';
if(isset($_SERVER['HTTP_REFERER'])) {        
    $logline .= '"'.$_SERVER['HTTP_REFERER'].'" ';
} else {
    $logline .= '"" ';
}
if(isset($_SERVER['HTTP_USER_AGENT'])) {
    $logline .= '"'.$_SERVER['HTTP_USER_AGENT'].'" ';
} else {
    $logline .= '"" ';
}
//print_r($logline);
file_put_contents($logFile, $logline."\n", FILE_APPEND);


function enc_str($str) {
    return str_replace('/', '|', trim($str));
}
function dec_str($str) {
    return str_replace('|', '/', trim($str));
}

?>
