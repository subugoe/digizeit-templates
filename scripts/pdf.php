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

define('__DZROOT__', realpath(__DIR__ . '/../../../'));

$checkCommand = '/usr/bin/gs -q -dNOPAUSE -sDEVICE=nullpage -sOutputFile=/dev/null -dBATCH';

include_once(__DZROOT__.'/htdocs/typo3conf/localconf.php');
$basehref = 'http://www.digizeitschriften.de/';
$cachePath = '/storage/digizeit/cache/';
$pdfwriter = __DZROOT__.'/htdocs/fileadmin/scripts/pdfwriter/';
$logPath = __DZROOT__.'/logs/';

$connect = mysql_connect($typo_db_host,$typo_db_username,$typo_db_password);
mysql_select_db($typo_db,$connect);
mysql_query('set names utf8');


if(!isset($_REQUEST['RIP'])) {
    parse_str(base64_decode($_SERVER['QUERY_STRING']),$_REQUEST);
}
//file_put_contents('/srv/www/chroot/digizeit/digizeit/tmp/bla.log',json_encode($_REQUEST)."\n",FILE_APPEND);                        

// repair '&amp;' in URL
foreach($_REQUEST as $key => $val) {
    $_REQUEST[str_replace('amp;','',$key)] = $val;
}

//get ACL from user
$arrUserAcl = array();
$arrUserAcl[] = 'free';
if($_REQUEST['fes']) {
    $res = mysql_query('SELECT fe_groups.title
                        FROM fe_groups, fe_sessions, fe_users
                        WHERE fe_sessions.ses_id = "'.$_REQUEST['fes'].'"
                        AND fe_sessions.ses_name = "fe_typo_user"
                        AND fe_users.uid = fe_sessions.ses_userid
                        AND FIND_IN_SET( fe_groups.uid, fe_users.usergroup )');
    while($arr = mysql_fetch_assoc($res)) {
        $arrUserAcl[] = strtolower(trim($arr['title']));
    }
}
//file_put_contents('/srv/www/chroot/digizeit/digizeit/tmp/bla.log','USER: '.json_encode($arrUserAcl)."\n",FILE_APPEND);                        




//get ACL from Struct
$arrStructAcl = array();
if($_REQUEST['ACL']) {
    $arrStructAcl = _unserialize(base64_decode($_REQUEST['ACL']));
    foreach($arrStructAcl as $k => $v) {
        $arrStructAcl[$k] = strtolower(trim($v));
    }
}
//file_put_contents('/srv/www/chroot/digizeit/digizeit/tmp/bla.log','STRUCT: '.json_encode($arrStructAcl)."\n",FILE_APPEND);                        

$arrAccess = array_intersect($arrUserAcl, $arrStructAcl);

/*
print_r('<pre>');
print_r($_SERVER);
print_r($_REQUEST);
print_r($arrAccess);
print_r('</pre>');
exit();
*/
$status = '200';


if(count($arrAccess)) {

    if(substr(strtolower($_REQUEST['PPN']),0,3) !='ppn') {
        //################# Jochen's pdfwriter ######################################
        chdir($pdfwriter);
        //exit();
        if(!is_file($cachePath.'itext/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.xml')) {
            file_put_contents($logPath.'mets2itext_cmd.log','./mets2itext.php '.$basehref.'dms/metsresolver/?PPN='.$_REQUEST['PPN'].' '.$_REQUEST['logID']."\n",FILE_APPEND);
            $test = exec('./mets2itext.php '.$basehref.'dms/metsresolver/?PPN='.$_REQUEST['PPN'].' '.$_REQUEST['logID']);
        }
        //exit();
        if(!is_file($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf')) {
            file_put_contents($logPath.'itext2pdf_cmd.log','./itext2pdf.php '.$cachePath.'itext/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.xml'."\n",FILE_APPEND);
            exec('./itext2pdf.php '.$cachePath.'itext/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.xml');    
        }
        
        //file_put_contents($logPath.'bla.log',$_REQUEST['PPN'].'_'.$_REQUEST['logID'].'.pdf'."\n",FILE_APPEND);
        //############################################################################

    } else {
        //################# ContentServer ############################################
        if(!is_file($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf')) {
            mkdir($cachePath.'pdf/'.enc_str($_REQUEST['PPN']), 0775, true);
            file_put_contents($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf',file_get_contents('http://localhost:8080/gcs/gcs?action=pdf&metsFile='.$_REQUEST['PPN'].'&divID='.$_REQUEST['logID'].'&pdftitlepage='.urlencode($basehref).'%2Fdms%2Fpdf-titlepage%2F%3FmetsFile%3D'.$_REQUEST['PPN'].'%26divID%3D'.$_REQUEST['logID']));
            @exec('chmod -R g+w '.$cachePath.'pdf/'.enc_str($_REQUEST['PPN']));
            //check PDF
            $size = filesize($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf');

            if($size == 0) {
                @unlink($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf');
                @unlink($cachePath.'itext/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.xml');
                $status = '500';
            } else {
                $arrError = array();
                $error = exec($checkCommand.' '.str_replace('file://','',$cachePath).'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf 2>&1',$arrError);
                if(trim(implode("\n",$arrError))) {
                    @unlink($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf');
                    @unlink($cachePath.'itext/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.xml');
                    $status = '500';
                }
            }
        }
        //############################################################################
    }


    if($status == '200') {
        header("Expires: -1");
        header("Cache-Control: post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header('Content-type: application/pdf');
          // download
    //    header('Content-Disposition: attachment; filename="'.enc_str($_REQUEST['PPN']).'_'.enc_str($_REQUEST['logID']).'.pdf"');
        // inline
        header('Content-Disposition: inline; filename="'.enc_str($_REQUEST['PPN']).'_'.enc_str($_REQUEST['logID']).'.pdf"');
        header('Content-Length: '.filesize($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf'));  
        header("Content-Transfer-Encoding: binary");
        
        if(is_file($cachePath.'pdf/'.enc_str($_REQUEST['PPN']).'/'.enc_str($_REQUEST['logID']).'.pdf')) {
            $fpin = fopen($cachePath.'pdf/'.$_REQUEST['PPN'].'/'.$_REQUEST['logID'].'.pdf','r');
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
} else {
    $status = '401';
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
$logline = $_REQUEST['RIP'].' - ';
$logline .= $_REQUEST['fes'].' ';
$logline .= date('[d/M/Y:H:i:s O] ',$_SERVER['REQUEST_TIME']);        
$logline .= '"GET http://localhost:8080/gcs/gcs?action=pdf&metsFile='.$_REQUEST['PPN'].'&divID='.$_REQUEST['logID'].' HTTP/1.1" ';
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
file_put_contents($logPath.'digizeit-content_log',$logline."\n", FILE_APPEND);




function setNSprefix(&$xpath,$node=false) {
    if(!$node) {
	     $xqueryList = $xpath->evaluate('*[1]');
		if ($xqueryList->length) {
            setNSprefix($xpath,$xqueryList->item(0));
        }
    }
    if(is_object($node)) {
        if($node->prefix) {
            $xpath->registerNamespace(strtolower($node->prefix), $node->namespaceURI);
        }
        $xqueryList = $xpath->evaluate('following-sibling::*[name()!="'.$node->nodeName.'"][1]',$node);
        if ($xqueryList->length) {
            setNSprefix($xpath,$xqueryList->item(0));
        }
        if($node->firstChild) {
            setNSprefix($xpath,$node->firstChild);
        }
        if($node->attributes->length) {
            foreach($node->attributes as $attribute) {
                if($attribute->prefix && !$arrNS[strtolower($attribute->prefix)]) {
                    $xpath->registerNamespace(strtolower($attribute->prefix), $attribute->namespaceURI);
                }
            }
        }
    }
    unset($xqueryList);
    unset($node);
    unset($attribute);
}

function enc_str($str) {
    return str_replace('/', '|', trim($str));
}
function dec_str($str) {
    return str_replace('|', '/', trim($str));
}

/**
* [Describe function...]
* Helper function to switch from serialized fields to "jsonized" Fields in lucene index
*
* @param [string]  $str: serialized or jsonized string
* @return [type]  unserialized or unjsonized
*/
function _unserialize($str) {
    $ret = json_decode($str,true);
    if(!is_array($ret)) {
        $ret = unserialize($str);
    }
    return $ret;
}

?>
