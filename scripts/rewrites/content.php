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
define(__DZROOT__, realpath(__DIR__ . '/../../../../'));

error_reporting(0);
$serverUrl = $_SERVER['HTTPS'] ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
$scriptPath = dirname(__FILE__);
$logPath = realpath($scriptPath . '/../../logs/');

$csBaseUrl = 'http://localhost:8080/gcs/cs';
$restrictImg = $serverUrl . '/fileadmin/images/restrict.png';
$authServer = $serverUrl . '/dms/authserver/?';
$imgCachePath = '/storage/digizeit/cache/jpg/';

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

$strUrlQuery = htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8");

$arrTmp = explode('/', $strQuery);

//format
$arrQuery['format'] = substr($arrTmp[3], -3);

if(is_file($imgCachePath.$strUrlQuery)) {
    header('Content-type: image/' . $arrQuery['format']);
    echo(file_get_contents($imgCachePath.$strUrlQuery));
    exit();
}

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
    $acl = file_get_contents($authServer . 'PPN=' . $arrTmp[0] . '&imagenumber=' . $imagenumber . '&ipaddress=' . $_SERVER['REMOTE_ADDR']);

    if (!$acl) {
        $arrInfo = getimagesize($restrictImg);
        $img = file_get_contents($restrictImg);
        header('Content-type: ' . $arrInfo['mime']);
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
    $img = file_get_contents($csBaseUrl . '?' . $strQuery);


    //write cache
    @mkdir(dirname($imgCachePath.$strUrlQuery), 0775 , true);
    //file_put_contents(__DZROOT__.'/tmp/bla.log', $imgCachePath.$strQuery."\n", FILE_APPEND);
    file_put_contents($imgCachePath.$strUrlQuery, $img);
    
    header('Content-type: image/' . $arrQuery['format']);
    echo($img);
    exit();
}


function id2name($id) {
    return str_replace('/', '___', trim($id));
}
?>
