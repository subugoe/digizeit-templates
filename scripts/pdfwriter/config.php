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

ini_set('memory_limit','1024M');
set_time_limit(0);
error_reporting(0);
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

define('__DZROOT__', realpath(__DIR__ . '/../../../../'));

$global = array(
    // 72 dpi
    // DIN A4 = 595px x 842px
    // 28px = 1 cm 
    'pageX'=>595, // A4 595d/72dpi+2,54cm/i = 21 cm
    'pageY'=>842, // A4 842d/72dpi+2,54cm/i = 29,7 cm
    'marginLeft' => 0,
    'marginRight' => 0,
    'marginTop' => 0,
    'marginBottom' => 0,
    'marginImage' => 0,
    'errorImage' => 'file:///storage/digizeit/tiff/damage.tif',
    'cachePath' => '/storage/digizeit/cache/',
    'strFileGrp' => 'PRESENTATION',
    'logPath' => __DZROOT__.'/logs/',
    'convert' => '/usr/bin/convert',
    'tomcatGrp' => 'tomcat6',
    'baseurl' => 'http://www.digizeitschriften.de/',

    'arrStruct' => array(
        'AnnouncementAdvertisement'=>'Anzeige',
        'Appendix'=>'Anhang',
        'Chapter'=>'Kapitel',
        'ContainedWork'=>'Anhängendes Werk',
        'Cover'=>'Einband',
        'DedicationForewordIntro'=>'Einleitung',
        'Illustration'=>'Illustration',
        'ImprintColophon'=>'Erscheinungsvermerk',
        'Index'=>'Index',
        'Journal'=>'Periodica',
        'Monograph'=>'Monographie',
        'MultivolumeWork'=>'Mehrbändiges Werk',
        'Other'=>'Sonstiges',
        'PeriodicalSupplement'=>'Beilage',
        'TableList'=>'Tabelle, Liste',
        'TableOfContents'=>'Inhaltsverzeichnis',
        'TextSection'=>'Textabschnitt',
        'TitlePage'=>'Titelblatt',
        'Unit'=>'Bereich',
        'Volume'=>'Band',
    ),

    'PIDquery' => array(
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:identifier[@type="urn" or @type="URN"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:identifier[@type="gbv-ppn"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:recordInfo/mods:recordIdentifier[@source="zdb-id"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:identifier[@type="ppn" or @type="PPN"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:recordInfo/mods:recordIdentifier[@source="gbv-ppn"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:identifier[@type="oai"]',
        '/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:recordInfo/mods:recordIdentifier',        
        ),
    'authorQuery' => array(
        'concat(
            string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:displayForm/child::text()),
            substring("; ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:displayForm/child::text()))))
        )',
        'concat(
            string(mets:xmlData/mods:mods/mods:name/mods:displayForm/child::text()),
            substring("; ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name/mods:displayForm/child::text()))))
        )',
        //der Ausdruck liefert NACHNAME, VORNAME des ersten gefundenen Knotens - dabei wird das KOMMA nur in Abhängigkeit von NACH- und VORNAME gesetzt!
        'normalize-space(
            concat(
                string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart[@type="family"]/child::text()),
                substring(", ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart[@type="family"]/child::text())))),
                string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart[@type="given"]/child::text()),
                substring("; ",1,string-length(normalize-space(concat(string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart[@type="family"]/child::text()),string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart[@type="given"]/child::text())))))
            )
        )',
        'normalize-space(
            concat(
                string(mets:xmlData/mods:mods/mods:name/mods:namePart[@type="family"]/child::text()),
                substring(", ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name/mods:namePart[@type="family"]/child::text())))),
                string(mets:xmlData/mods:mods/mods:name/mods:namePart[@type="given"]/child::text()),
                substring("; ",1,string-length(normalize-space(concat(string(mets:xmlData/mods:mods/mods:name/mods:namePart[@type="family"]/child::text()),string(mets:xmlData/mods:mods/mods:name/mods:namePart[@type="given"]/child::text())))))
            )
        )',
        'concat(
            string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart/child::text()),
            substring("; ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name[@type="personal"]/mods:namePart/child::text()))))
        )',
        'concat(
            string(mets:xmlData/mods:mods/mods:name/mods:namePart/child::text()),
            substring("; ",1,string-length(normalize-space(string(mets:xmlData/mods:mods/mods:name/mods:namePart/child::text()))))
        )',
    ),
);  

?>
