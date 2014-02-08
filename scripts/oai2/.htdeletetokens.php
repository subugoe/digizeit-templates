#!/usr/bin/php5
<?php
$path = dirname(__FILE__);
//$time = time()-10800; //3 Stunden
$time = time()-259200; //3 Tage
$oaiTokenDir = $path.'/temp/';
$d = dir($oaiTokenDir);
while (false !== ($entry = $d->read())) {
	if(is_file($oaiTokenDir.$entry)) {
		if(filemtime($oaiTokenDir.$entry)<$time) {
            unlink($oaiTokenDir.$entry);
        }
	}
}
$d->close();
?>
