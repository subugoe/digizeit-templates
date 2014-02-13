<?php
/***************************************************************************
 *   Copyright (C) 2007 by Jochen Kothe                                    *
 *   jkothe@proi-php.de                                                    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

//############################################################################
//### INIT ###################################################################
//############################################################################

//error_reporting(0);

// read configuration
$conf = parse_ini_file('./.htoai2.ini.php', TRUE);

set_time_limit($conf['MAIN']['time_limit']);
ini_set('memory_limit',$conf['MAIN']['memory_limit']);


// include libs
include('./oai2.'.$conf['DB']['engine'].'.php');
include('./oai2.lib'.$conf['DB']['engine'].'.php');
include('./oai2php5.php');

//############################################################################
//### END INIT ###############################################################
//############################################################################

$xml = new oai2($conf);
header("Content-type: text/xml");
print_r($xml->oai->saveXML());

// delete expired resumption tokens
$time = time() - $xml->conf['MAIN']['expirationDate'];
$d = dir($xml->conf['MAIN']['tmpDir']);
while (false !== ($entry = $d->read())) {
    if (is_file($xml->conf['MAIN']['tmpDir'] . $entry) && substr($entry,0,4) == 'oai_') {
        if (filemtime($xml->conf['MAIN']['tmpDir'] . $entry) < $time) {
            unlink($xml->conf['MAIN']['tmpDir'] . $entry);
        }
    }
}
$d->close();
?>