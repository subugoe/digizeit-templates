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
class excel {
    static public $excelCharset = 'utf-8';
    static public $excelProcessingInstructionTarget = 'mso-application';
    static public $excelProcessingInstructionData = 'progid="Excel.Sheet"';
    static public $excelFormatXmlOutput = false;
    static public $excelWorkbookAttributes = array(
        'xmlns'=>'urn:schemas-microsoft-com:office:spreadsheet',
        'xmlns:x'=>'urn:schemas-microsoft-com:office:excel',
        'xmlns:ss'=>'urn:schemas-microsoft-com:office:spreadsheet',
        'xmlns:html'=>'http://www.w3.org/TR/REC-html40');

    static public $root;
    static public $xls;
    
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

    function createDocument() {        
        $dom = new DOMDocument('1.0', self::$excelCharset);
        $dom->formatOutput = self::$excelFormatXmlOutput;
        $dom->appendChild($dom->createProcessingInstruction(self::$excelProcessingInstructionTarget, self::$excelProcessingInstructionData));        
        return $dom;
    }

    function createBook($dom) {
        $arrArgs = array(
            'dom'=>$dom,
            'name'=>'Workbook',
            'arrAttributes'=>self::$excelWorkbookAttributes,
            'parent'=>$dom
        );
        self::$root = self::createNode($arrArgs);
    }

    function createSheet(&$dom,$name) {
        $arrArgs = array(
            'dom'=>$dom,
            'name'=>'ss:Worksheet',
            'arrAttributes'=>array('ss:Name'=>self::cleanSheetName($name))
        );
        return self::createNode($arrArgs);
    }

    function cleanSheetName($name) {
        return substr(preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $name),0,31);
    }


//   arrArgs(dom, name, value, arrAttributes, parent)
    function createNode($arrArgs) {
        $node = false;
        if(is_array($arrArgs) && $arrArgs['dom'] && $arrArgs['name']) {
            if($arrArgs['value']) {
                $node = $arrArgs['dom']->createElement($arrArgs['name'],$arrArgs['value']);
            } else {
                $node = $arrArgs['dom']->createElement($arrArgs['name']);
            }
            if(is_array($arrArgs['arrAttributes'])) {
                foreach($arrArgs['arrAttributes'] as $attribute=>$val) {
                    $node->setAttribute($attribute,$val);
                }
            }
            if($arrArgs['parent']) {
                $arrArgs['parent']->appendChild($node);
            } else {
                self::$root->appendChild($node);
            }
        }
        return $node;
    }

}
?>