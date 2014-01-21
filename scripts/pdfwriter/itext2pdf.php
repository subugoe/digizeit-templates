#!/usr/bin/php5
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
require_once('http://localhost:8080/myJBridge_20111123/java/Java.inc');
//#java_require('iText.jar');

$start = microtime(true);
include_once('./config.php');
include_once('./functions.php');

if (!$_SERVER['argv'][1]) {
    exit();
} else {
    $global['strXmlUrl'] = trim($_SERVER['argv'][1]);
    $arrTmp = parse_url($global['strXmlUrl']);
}
//#### END INIT #############################################
//#### MAIN #################################################
$iText = new DOMDocument('1.0', 'UTF-8');
$test = $iText->loadXML(file_get_contents($global['strXmlUrl']));

if ($test) {
    $iText->xinclude();

    $root = $iText->getElementsByTagName('iText')->item(0);
    if ($root->hasAttribute('keywords')) {
        $arrTmp = explode(' ', trim($root->getAttribute('keywords')));
        $global['pdfPath'] = $global['cachePath'] . 'pdf/' . enc_str(trim($arrTmp[0])) . '/';
        if (!is_dir($global['pdfPath'])) {
            mkdir($global['pdfPath'], 0775);
            exec('chmod 775 ' . $global['pdfPath']);
            exec('chgrp ' . $global['tomcatGrp'] . ' ' . $global['pdfPath']);
        }
        if (!is_dir($global['pdfPath'])) {
            exit();
        }
        $global['filename'] = trim(str_replace('.xml', '', enc_str($arrTmp[1]))) . '.pdf';
    } else {
        exit();
    }

//    $outputstream = new Java('java.io.ByteArrayOutputStream');
    $outputstream = new Java('java.io.FileOutputStream', $global['pdfPath'] . $global['filename']);
    $pdfdoc = new Java('com.lowagie.text.Document', new Java('com.lowagie.text.Rectangle', $global['pageX'], $global['pageY']), $global['marginLeft'], $global['marginRight'], $global['marginTop'], $global['marginBottom']);
    $xmlStringReader = new Java('java.io.StringReader', $iText->saveXML());
    $pdfWriterClass = new JavaClass('com.lowagie.text.pdf.PdfWriter');
    $pdfwriter = $pdfWriterClass->getInstance($pdfdoc, $outputstream);

    /*
      $BF = new Java('com.lowagie.text.pdf.BaseFont');
      $bfFreeSans = $BF->createFont('/home/kothe/Projekte/pdfwriter/FreeSans.ttf', $BF->CP1252, $BF->EMBEDDED);
      $FreeSans = new Java('com.lowagie.text.Font',$bfFreeSans);
      $bfFreeSansBold = $BF->createFont('/home/kothe/Projekte/pdfwriter/FreeSansBold.ttf', $BF->CP1252, $BF->EMBEDDED);
      $FreeSansBold = new Java('com.lowagie.text.Font',$bfFreeSansBold);
     */
//$bftest = $BF->createFont($BF->COURIER, $BF->CP1252, $BF->EMBEDDED);
//$test = new Java('com.lowagie.text.Font',$bftest);
//print_r(java_inspect($FreeSans));    
//print_r(java_values($bfFreeSans->getAllNameEntries()));    
//exit();    

    $pdfwriter->setPdfVersion($pdfWriterClass->PDF_VERSION_1_4);
//    $pdfwriter->setPdfXConformance($pdfWriterClass->PDFA1A);

    $xmlParser = new Java('com.lowagie.text.xml.XmlParser');
    $pdfdoc->open();
//    $pdfdoc->add(new Java('com.lowagie.text.Paragraph','The quick brown fox jumps over the lazy dog',$test));
//    $pdfdoc->add(new Java('com.lowagie.text.Paragraph','The quick brown fox jumps over the lazy dog',$FreeSansBold));
    $pdfdoc->addTitle(trim($root->getAttribute('title')));
    $pdfdoc->addAuthor(trim($root->getAttribute('author')));
    $pdfdoc->addSubject(trim($root->getAttribute('subject')));
    $pdfdoc->addKeywords(trim($root->getAttribute('keywords')));
    $pdfdoc->addCreationDate();
    $pdfdoc->addCreator('JK PDFWriter');
    try {
        $xmlParser->parse($pdfdoc, $xmlStringReader);
    } catch (JavaException $e) {
        // error handling;
    }

    $outputstream->flush();
    $pdfwriter->createXmpMetadata();
    $outputstream->flush();
    $pdfdoc->close();

    $end = microtime(true) - $start;
    $logline .= date('[d/M/Y:H:i:s O] ', time());
    $logline .= ' ' . $global['strXmlUrl'];
    $logline .= ' ' . number_format($end, 2, ',', '.') . 'sec' . "\n";
    file_put_contents($global['logPath'] . 'itext2pdf.log', $logline, FILE_APPEND);

    $outputstream->flush();
    $outputstream->close();

    unset($pdfwriter);
    unset($pdfdoc);
    unset($pdfWriterClass);
    unset($pdfwriter);
    unset($xmlParser);
    unset($outputstream);

//    echo java_values($outputstream->toByteArray());
}
//#### END MAIN #############################################
?>
