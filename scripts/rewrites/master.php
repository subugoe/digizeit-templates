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
define(__DZROOT__, realpath(__DIR__ . '/../../../../'));
include(__DZROOT__ . '/.hosteurope.cloud.secrets');

//debug
//file_put_contents(__DZROOT__.'/tmp/debug.log','key: '.$key."\n".'secret: '.$secret."\n",FILE_APPEND);
//sample call with rewrite: http://www.digizeitschriften.de/master/PPN129323640_0001/00000001.tif
//sample call without rewrite: http://www.digizeitschriften.de/fileadmin/scripts/rewrites/master.php?PPN129323640_0001/00000001.tif
//debug
//file_put_contents(__DZROOT__.'/tmp/debug.log',$_SERVER['QUERY_STRING']."\n",FILE_APPEND);

$arrQuery = explode('/', htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8"));
;

$ppn = array_shift($arrQuery);
$img = array_shift($arrQuery);

$file = '/digizeit/tiff/' . trim($ppn) . '/' . trim($img);
$expire = time() + 60;
$string = 'GET' . "\n\n\n" . $expire . "\n" . $file;

$signature = urlencode(base64_encode(hash_hmac('sha1', $string, $secret, true)));

$URL = 'http://digizeit.cs.hosteurope.de/tiff/' . trim($ppn) . '/' . trim($img) . '?AWSAccessKeyId=' . $key . '&Expires=' . $expire . '&Signature=' . $signature;

//debug
//file_put_contents(__DZROOT__.'tmp/debug.log',$URL."\n",FILE_APPEND);
// Stupid but without that brake ContentServer an OpenVZ are overfloated
usleep(50);

header('location: ' . $URL);
exit();
?>