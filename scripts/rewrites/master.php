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
define('__DZROOT__', realpath(__DIR__ . '/../../../../'));
//S3 dunkel.de
include(__DZROOT__ . '/.dunkel.cloud.secrets');

//debug
//file_put_contents(__DZROOT__.'/tmp/debug.log','key: '.$key."\n".'secret: '.$secret."\n",FILE_APPEND);
//sample call with rewrite: http://www.digizeitschriften.de/master/PPN129323640_0001/00000001.tif
//sample call without rewrite: http://www.digizeitschriften.de/fileadmin/scripts/rewrites/master.php?PPN129323640_0001/00000001.tif
//debug
//file_put_contents(__DZROOT__.'/tmp/debug.log',$_SERVER['QUERY_STRING']."\n",FILE_APPEND);

$arrQuery = explode('/', htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8"));

$ppn = array_shift($arrQuery);
$img = array_shift($arrQuery);

$file = '/digizeit/tiff/' . trim($ppn) . '/' . trim($img);
$expire = time() + 60;
$string = 'GET' . "\n\n\n" . $expire . "\n" . $file;
$signature = urlencode(base64_encode(hash_hmac('sha1', $string, $secret, true)));

//S3 dunkel.de
$URL1 = 'http://digizeit.dcs.dunkel.de/tiff/' . trim($ppn) . '/' . trim($img) . '?AWSAccessKeyId=' . $key . '&Expires=' . $expire . '&Signature=' . $signature;
//GWDG subtypo3
$URL2 = 'http://www.gwdg.de/~subtypo3/digizeit/tiff/' . trim($ppn) . '/' . trim($img);

$arrTest = get_headers($URL1, 1);
if(strpos($arrTest[0],'200')!==false) {
    if($arrTest['Content-Type'] != 'image/tiff') {
        $URL = $URL2;
        file_put_contents(__DZROOT__.'/tmp/todo.log',trim($ppn) . '/' . trim($img)."\n",FILE_APPEND);
    } else {
        $URL = $URL1;
    }
} else {
    $URL = $URL2;
}
        
//debug
//file_put_contents(__DZROOT__.'/tmp/debug.log',$URL."\n",FILE_APPEND);
//file_put_contents(__DZROOT__.'/tmp/debug.log',$URL1."\n",FILE_APPEND);
// Stupid but without that brake ContentServer an OpenVZ are overfloated
//usleep(30);

header('location: ' . $URL);
exit();

?>