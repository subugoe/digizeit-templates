<?php
$strOCRPath = '/srv/www/chroot/digizeit/digizeit/htdocs/gdzocr/';
#$strOCRPath = '/srv/www/chroot/dzdev/dzdev/htdocs/gdzocr/';
$strDZPath = '/storage_lokal/indexed_mets/';

#$strOCRPath = '/hidrive/sftp/users/digizeit/gdzocr/';
#$strDZPath = '/hidrive/sftp/users/digizeit/mets_repository/indexed_mets/';

$arrOCR = scandir($strOCRPath);
foreach($arrOCR as $k=>$v) {
    $arrOCR[$k] = trim($v);
}

$arrDZ = scandir($strDZPath);
foreach($arrDZ as $k=>$v) {
    $ppn = trim(str_replace('.xml','',trim($v)));
    if(substr($ppn,0,3)!='PPN' || strlen($ppn)<=12) {
        unset($arrDZ[$k]);
    } else {
        $arrDZ[$k] = $ppn;
    } 
}

$arr = array_values(array_diff($arrDZ,$arrOCR));

echo(json_encode($arr));
?>
