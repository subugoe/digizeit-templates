<?php
/* **************************************************************
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
define('__DZROOT__', realpath(__DIR__ . '/../../../'));

set_time_limit(0);
error_reporting(E_ALL);
//error_reporting(0);

$serverUrl = $_SERVER['HTTPS'] ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
$scriptPath = dirname(__FILE__);

$restrictImg = $serverUrl . '/fileadmin/images/restrict.png';

$authServer = $serverUrl . '/dms/authserver/?';

$strUrlQuery = htmlentities(trim($_SERVER['QUERY_STRING']), ENT_QUOTES, "UTF-8");

parse_str($strUrlQuery);

$imgurl = urldecode($imgurl);
$ppn = urldecode($ppn);
$physid = urldecode($physid);

print_r($authServer . 'PPN=' . $ppn . '&PHYSID=' . $physid . '&ipaddress=' . $_SERVER['REMOTE_ADDR']);
exit();


$acl = 0;
$imagenumber = intval($arrTmp[(count($arrTmp) - 1)]);
$acl = file_get_contents($authServer . 'PPN=' . $ppn . '&PHYSID=' . $physid . '&ipaddress=' . $_SERVER['REMOTE_ADDR']);


if (!$acl) {
    $arrInfo = getimagesize($restrictImg);
    $img = file_get_contents($restrictImg);
    header('Content-type: ' . $arrInfo['mime']);
    header('HTTP/1.0 401 Unauthorized');
    echo $img;
    exit();
}

$strTmpName = tempnam(sys_get_temp_dir(),'TMP');
file_put_contents($strTmpName,file_get_contents($imgurl));
header('Content-type: image/jpg');
passthru('/usr/bin/convert -rotate '.$rotate.' '.$strTmpName.' JPG:-'."\n".'rm -rf '.$strTmpName);

?>

