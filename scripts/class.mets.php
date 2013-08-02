<?php
    /***************************************************************
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
    ***************************************************************/
class mets {
    static public $metsResolver = 'http://www.digizeitschriften.de/dms/metsresolver/?PPN=';
    static public $metsStructMapping = array( 
                            'abstract'=>'Abstract',
                            'advertising'=>'Werbung',
                            'appendix'=>'Appendix',
                            'article'=>'Artikel',
                            'bibliography'=>'Bibliographie',
                            'chapter'=>'Kapitel',
                            'comment'=>'Kommentar',
                            'courtdecision'=>'Rechtsentscheidung',
                            'cover'=>'Einband',
                            'curriculumvitae'=>'Lebenslauf',
                            'dedication'=>'Widmung',
                            'epilogue'=>'Epilogue',
                            'errata'=>'Errata',
                            'figure'=>'Abbildung',
                            'imprint'=>'Impressum',
                            'index'=>'Index',
                            'indexabbreviations'=>'Abkürzungsverzeichnis',
                            'indexauthors'=>'Autorenverzeichnis',
                            'indexlocations'=>'Ortsregister',
                            'indexnames'=>'Namensregister',
                            'indexspecial'=>'Spezialverzeichnis',
                            'introduction'=>'Einleitung',
                            'issue'=>'Zeitschriftenheft',
                            'legalcomment'=>'Rechtskommentar',
                            'legalnorm'=>'Rechtsnorm',
                            'letter'=>'Brief',
                            'list'=>'Liste',
                            'listofpublications'=>'Publikationsverzeichnis',
                            'map'=>'Karte',
                            'miscella'=>'Miszelle',
                            'notes'=>'Noten',
                            'obituary'=>'Nachruf',
                            'other'=>'Sonstiges',
                            'periodical'=>'Zeitschrift',
                            'periodicalissue'=>'Zeitschriftenheft',
                            'periodicalpart'=>'Zeitschriftenteil',
                            'periodicalvolume'=>'Zeitschriftenband',
                            'poem'=>'Gedicht',
                            'preface'=>'Vorwort',
                            'prepage'=>'Deckblatt',
                            'remarks'=>'Bemerkungen',
                            'review'=>'Rezension',
                            'supplement'=>'Beilage',
                            'table'=>'Tabelle',
                            'tableofcontents'=>'Inhaltsverzeichnis',
                            'tableofliteraturerefs'=>'Literaturverzeichnis',
                            'theses'=>'Dissertation',
                            'titlepage'=>'Titelseite',
                            'volume'=>'Zeitschriftenband'
                        );
    static public $metsStructReplace = array(
                            'volume'=>'periodicalvolume',
                            'issue'=>'periodicalissue'
                        );

    static public $timeLimit = 0;
    static public $memoryLimit = '1024M';

    static $debug = false;


    function init() {
        $timeLimit = ini_get('max_execution_time');
        if(self::$timeLimit==0 || self::$timeLimit>$timeLimit) {
            set_time_limit(self::$timeLimit);
        }
        $memoryLimit = ini_get('memory_limit');
        $postfix = strtolower(substr($memoryLimit,-1));
        if(isset(self::$$postfix)) {
            $memoryLimit = $memoryLimit * self::$$postfix;
        }
        $memoryLimit = ini_get('memory_limit');
        $postfix = strtolower(substr(self::$memoryLimit,-1));
        if(isset(self::$$postfix)) {
            self::$memoryLimit = self::$memoryLimit * self::$$postfix;
        }
        if(self::$memoryLimit>$memoryLimit) {
            ini_set('memory_limit', self::$memoryLimit);
        }
    }

    function setNSprefix(&$xpath,$node=false) {
        if(!$node) {
                $xqueryList = $xpath->evaluate('*[1]');
            if ($xqueryList->length) {
                self::setNSprefix($xpath,$xqueryList->item(0));
            }
        }
        if(is_object($node)) {
            if($node->prefix) {
                $xpath->registerNamespace(strtolower($node->prefix), $node->namespaceURI);
            }
            $xqueryList = $xpath->evaluate('following-sibling::*[name()!="'.$node->nodeName.'"][1]',$node);
            if ($xqueryList->length) {
                self::setNSprefix($xpath,$xqueryList->item(0));
            }
            if($node->firstChild) {
                self::setNSprefix($xpath,$node->firstChild);
            }
            if($node->attributes->length) {
                foreach($node->attributes as $attribute) {
                    if($attribute->prefix && !$arrNS[strtolower($attribute->prefix)]) {
                        $xpath->registerNamespace(strtolower($attribute->prefix), $attribute->namespaceURI);
                    }
                }
            }
        }
        unset($xqueryList);
        unset($node);
        unset($attribute);
    }

    function openMetsAsDom($id,$charset='utf-8') {
        $strXml = file_get_contents(self::$metsResolver.$id);
        if($strXml) {
            $dom = new DOMDocument('1.0',$charset);
            $test = $dom->loadXML($strXml);
            if($test) {
                return $dom;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function openXPath($dom) {
        $xpath = new DOMXPath($dom);
        self::setNSprefix($xpath);
        return $xpath;
    }

    function makeCacheEntry($id,$mtime) {
        $dom = self::openMetsAsDom($id);
        if(!$dom) {
            return false;
        }
        $xpath = self::openXPath($dom);
        if(!$xpath) {
            return false;
        }
        $val = array();
        $divList = $xpath->evaluate('*//mets:div',$xpath->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
        if ($divList->length) {
            foreach($divList as $div) {
                if($div->hasAttribute('TYPE')) {
                    $logid = $div->getAttribute('ID');
                    if($div->hasAttribute('DMDID')) {
                        $val['dmd2log'][$div->getAttribute('DMDID')] = $logid;
                    }
                    $linkList = $xpath->evaluate('/mets:mets/mets:structLink/mets:smLink[@xlink:from="'.$logid.'"]');
                    $val['struct'][$logid] = $linkList->length;
                    if(array_key_exists(strtolower($div->getAttribute('TYPE')),self::$metsStructReplace)) {
                        $struct = trim(self::$metsStructReplace[strtolower($div->getAttribute('TYPE'))]);
                    } else if(array_key_exists(strtolower($div->getAttribute('TYPE')),self::$metsStructMapping)) {
                        $struct = trim(strtolower($div->getAttribute('TYPE')));
                    }
                    if(!trim($struct)) {
                        $struct = 'other';
                    }

                    foreach($linkList as $link) {
                        $arrLink[$struct][trim($link->getAttribute('xlink:to'))] = 1;
                    }
                    if(!trim($div->getAttribute('TYPE'))) {
                        $arrNoType[$val['PPN']] = 1;
                    }
                }
            }
            foreach($arrLink as $struct=>$arr) {
                if(is_array($arr)) {
                    $arrStruct[$struct]['images'] += count($arr);
                    $val['type'][$struct] = count($arr);
                }
            }
            $val['mtime'] = $mtime;
            return $val;
        } else {
            return false;
        }
    }


}
?>
