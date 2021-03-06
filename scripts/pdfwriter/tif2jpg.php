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

include_once('./config.php');

if (isset($_REQUEST['img'])) {
    $img = urldecode($_REQUEST['img']);
} else {
    exit();
}
//#### END INIT ###########################################

$arrParts = explode('/', $img);
// file:// entfernen
if (strtolower($arrParts[0]) == 'file:') {
    array_shift($arrParts);
    array_shift($arrParts);
    $img = implode('/', $arrParts);
}
$start = microtime(true);
header('Content-type: image/jpg');
passthru($global['convert'] . ' -compress jpeg -quality 80 ' . urldecode($img) . ' JPG:-');
file_put_contents($global['logPath'] . 'tif2jpg.log', 'Compress ' . $arrParts[(count($arrParts) - 1)] . ' - ' . number_format((microtime(true) - $start), 2, ',', '.') . ' sec' . "\n", FILE_APPEND);
?>
