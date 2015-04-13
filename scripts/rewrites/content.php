<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Niedersächsische Staats- und Universitätsbibliothek
 *  (c) 2009 Jochen Kothe (kothe@sub.uni-goettingen.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
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
ignore_user_abort(true);
define('__DZROOT__', realpath(__DIR__ . '/../../../../'));

//file_put_contents(__DZROOT__.'/tmp/bla.log', json_encode($_COOKIE)."\n", FILE_APPEND);
//file_put_contents(__DZROOT__.'/tmp/bla1.log', json_encode($GLOBALS)."\n", FILE_APPEND);

//file_put_contents(__DZROOT__.'/tmp/bla1.log', $_SERVER['HTTP_X_FORWARDED_HOST'].' - '.$_SERVER['HTTP_X_FORWARDED_FOR'].' - '.$_SERVER['REMOTE_ADDR']."\n", FILE_APPEND);

error_reporting(0);
$serverUrl = $_SERVER['HTTPS'] ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
$scriptPath = dirname(__FILE__);
$logFile = __DZROOT__.'/logs/digizeit-content_log';

$csBaseUrl = 'http://localhost:8080/gcs/cs';
$restrictImg = $serverUrl . '/fileadmin/images/restrict.png';
$authServer = $serverUrl . '/dms/authserver/?';
$imgCachePath = '/storage/digizeit/cache/jpg/';


if($logFile) {
    if (is_writable($logFile)) {
        $logging = true;
    } else {
        $logging = false;
    }
}


$arrQuery['action'] = 'image';

//sample call with rewrite: http://www.digizeitschriften.de/content/PPN342672002_0007/150/180/00000101.jpg
//sample call without rewrite: http://www.digizeitschriften.de/fileadmin/scripts/rewrites/content.php?PPN342672002_0007/150/180/00000101.jpg
//Beispiel:
// &format=jpg
// &sourcepath=PPN246196289/00000001.tif
// &scale=0.3
// &rotate=90
// &width=200
// &highlight=10,50,80,150|60,80,160,200  (nicht umgesetzt!!!)

// get highlight paramter 
$arrTmp = explode('?', $_SERVER['REQUEST_URI']);
$strTmp = array_pop($arrTmp);
parse_str($strTmp);
$arrQuery['highlight'] = htmlentities($highlight, ENT_QUOTES, "UTF-8");
if(!trim($arrQuery['highlight'])) {
    unset($arrQuery['highlight']);
}
unset($arrTmp);

$strUrlQuery = htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8");

$arrTmp = explode('/', $strUrlQuery);


// remove highlight parameter
$pos = strpos($arrTmp[3],'?');
if($pos !== false) {
    $arrTmp[3] = substr($arrTmp[3],0,$pos);
}

//format
$arrQuery['format'] = substr($arrTmp[3], -3);


################################################################################
// es werden nur URIs mit folgendem Aufbau verarbeitet
// <PPN>/<width in Pixeln>/<Rotation in Grad (0 bis 360)>/<image nummer wie im entsprechenden TIF Verzeichnis>.<Dateiendung (jpg,png,gif)>
// Beispiel: PPN341861871/800/0/00000001.jpg
// ###############################################################################
if (count($arrTmp) != 4) {
    exit();
} else {

    //##############################################################################
    // Hier Zugriffskontrolle einbauen wenn nötig.
    // z.B. über IP-Adresse oder die Typo3 Sessions aus  $_SERVER['HTTP_COOKIE']
    // dazu muss sichergestellt werden das die $csBaseUrl nicht direkt erreichbar ist sondern nur von diesem Server!
    //##############################################################################
    $acl = 0;
    $imagenumber = intval($arrTmp[(count($arrTmp) - 1)]);


    if($_SERVER['HTTP_X_FORWARDED_HOST']=='www.digizeitschriften.de') {
        $_arrTmp = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
        $remoteIP = trim(array_pop($_arrTmp));
        unset($_arrTmp);
        $acl = file_get_contents($authServer . 'PPN=' . $arrTmp[0] . '&imagenumber=' . $imagenumber . '&ipaddress=' . $remoteIP.'&fe_typo_user='.$_COOKIE['fe_typo_user']);
    } else {
        $acl = file_get_contents($authServer . 'PPN=' . $arrTmp[0] . '&imagenumber=' . $imagenumber . '&ipaddress=' . $_SERVER['REMOTE_ADDR'].'&fe_typo_user='.$_COOKIE['fe_typo_user']);
    }

    if (!$acl) {
        $arrInfo = getimagesize($restrictImg);
        $img = file_get_contents($restrictImg);
        header('Content-type: ' . $arrInfo['mime']);
        header('HTTP/1.0 401 Unauthorized');
        echo $img;
        exit();
    }

    //##############################################################################
    // fuer ein separates Logging der Zugriffe auf Images (macht spätere Auswertungwn einfacher),
    // folgendes in die Apache-Konfiguration ggf. im <VirtualHost> Container eintragen
    // Dieselben Zeilen findet man aber auch im Apache-log - zwischen den vielen anderen ;-))
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // # Contentserver logs
    // SetEnvIf Request_URI "^(/content/.*jpg)$" contentdir
    // # Combined Log Format definieren
    // CustomLog /logs/content_log combined env=contentdir
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //###############################################################################

    //sourcepath
    
    $arrQuery['sourcepath'] = $arrTmp[0] . '/' . substr($arrTmp[3], 0, -3) . 'tif';

    //width
    $arrTmp[1] = intval($arrTmp[1]);
    if ($arrTmp[1] > 1) {
        $arrQuery['width'] = $arrTmp[1];
    }

    //rotate
    $arrTmp[2] = intval($arrTmp[2]);
    if ($arrTmp[2] > 1) {
        $arrQuery['rotate'] = ($arrTmp[2] % 360 + 360) % 360;
    }
    $strQuery = '';
    foreach ($arrQuery as $k => $v) {
        $strQuery .= $k . '=' . $v . '&';
    }
    
    $imgURL = $csBaseUrl . '?' . $strQuery;

    //clear corrupt cache files
    if(is_file($imgCachePath . $strUrlQuery)) {
        if (filesize($imgCachePath . $strUrlQuery) < 4096) {
            unlink($imgCachePath . $strUrlQuery);
        } else if (!getimagesize($imgCachePath . $strUrlQuery)) {
            unlink($imgCachePath . $strUrlQuery);
        }
    }

    if(is_file($imgCachePath . $strUrlQuery) && !trim($arrQuery['highlight'])) {
//file_put_contents(__DZROOT__.'/tmp/bla.log','Cache: '.$imgCachePath . $strUrlQuery."\n",FILE_APPEND);
        header('Content-type: image/' . $arrQuery['format']);
        echo(file_get_contents($imgCachePath . $strUrlQuery));
    } else {
//file_put_contents(__DZROOT__.'/tmp/bla.log','CS: '.$imgURL."\n",FILE_APPEND);

        $img = file_get_contents($imgURL);

        //write cache
        @mkdir(dirname($imgCachePath . $strUrlQuery), 0775, true);
        
        if(!trim($arrQuery['highlight'])) {
            file_put_contents($imgCachePath . $strUrlQuery, $img);
        }
        
        header('Content-type: image/' . $arrQuery['format']);
        echo($img);
    }

    //Content Logging
    //http://localhost:8080/gcs/gcs?action=metsImage&format=jpg&metsFile=PPN366382810_1993_0068&divID=phys344&width=800&rotate=0
    if($logging) {
        if(trim($remoteIP)) {
            $log['remote_addr'] = $remoteIP;
        } else {
            $log['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        }
        $log['auth_passwd'] = '-';
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $log['auth_user'] = $_SERVER['PHP_AUTH_USER'];
        } else {
            $log['auth_user'] = '-';
        }
        $log['date'] = date('[d/M/Y:H:i:s O] ', $_SERVER['REQUEST_TIME']);
        $log['request'] = '"GET ';
        $log['request'] .= $imgURL . ' ';
        $log['request'] .= $_SERVER['SERVER_PROTOCOL'] . '"';
        $log['redirect_status'] = $_SERVER['REDIRECT_STATUS'];
        $log['filesize'] = 0;
        if (isset($_SERVER['HTTP_REFERER'])) {
            $log['referrer'] = '"' . $_SERVER['HTTP_REFERER'] . '"';
        } else {
            $log['referrer'] = '""';
        }
        $log ['user_agent'] = '"' . $_SERVER['HTTP_USER_AGENT'] . '"';
        file_put_contents($logFile,implode(' ',$log)."\n",FILE_APPEND);
    }
    exit();
}

function id2name($id) {
    return str_replace('/', '___', trim($id));
}


?>
