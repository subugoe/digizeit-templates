<?php
set_time_limit(0);
error_reporting(E_ALL);
//error_reporting(0);
$strTmpName = tempnam(sys_get_temp_dir(),'TMP');
file_put_contents($strTmpName,file_get_contents(urldecode($_GET['url'])));
header('Content-type: image/jpg');
passthru('/usr/bin/convert '.$strTmpName.' JPG:-'."\n".'rm -rf '.$strTmpName);

?>

