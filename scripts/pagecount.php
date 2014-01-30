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

/*+++++++++++++++++++++++++++++++++++++++++++++*/
/*+++++ MyDigiZeit/VG Wort ++++++++++++++++++++*/
/*+++++++++++++++++++++++++++++++++++++++++++++*/

include_once ('class.mets.php');
include_once ('class.berkeley.php');
include_once ('class.excel.php');
class vgwort {
//####################################################################################
//## CONFIG ##########################################################################
//####################################################################################
	var $config = array(
            'start' => "20020730",
            'arrWall' => array('1925'),
            'ppnResolver' => 'http://resolver.sub.uni-goettingen.de/purl/?',
            'metsResolver' => 'http://www.digizeitschriften.de/dms/metsresolver/?PPN=',
            'solrPhpsUrl' => 'http://localhost:8080/digizeit/select/?wt=phps',
            'arrSerFields' => array('ACL', 'STRUCTRUN', 'PRE', 'SUC'),
        );
//####################################################################################
//## END CONFIG ######################################################################
//####################################################################################

//####################################################################################
//## MAIN ############################################################################
//####################################################################################
    function main () {

        mets::init();
        
        berkeley::init();



        $this->config['end'] = date("Ymd",time());

        $this->POST = t3lib_div::_POST();
//print_r($this->POST);
        $this->content .= '<table>'."\n";
        $this->content .= '<tr><td colspan="6">&nbsp;</td></tr>'."\n";

        $this->content .= '<form action="" method="post">'."\n";

        $this->content .= '<tr><td>Start:&nbsp;</td><td>'."\n";
        $this->getDateForm('start');
        $this->content .= '</td><td>Ende:&nbsp;</td><td>'."\n";
        $this->getDateForm('end');
        $this->content .= '</td><td colspan="2"></td></tr>'."\n";

        $this->content .= '<tr><td colspan="6">&nbsp;</td></tr>'."\n";

        $this->content .= '<tr><td valign="top">Kollektion:&nbsp;</td><td valign="top">'."\n";
        $this->getCollectionForm();

        $this->content .= '</td><td valign="top">Struktur:&nbsp;</td><td valign="top">'."\n";
        $this->getStructForm();

        $this->content .= '</td><td valign="top">Lizenz:&nbsp;</td><td valign="top">'."\n";
        $this->getLicenseForm();
        $this->content .= '</td></tr>'."\n";

        $this->content .= '<tr><td colspan="6">&nbsp;</td></tr>' . "\n";

        $this->content .= '<tr><td colspan="3">&nbsp;</td>' . "\n";
        $this->content .= '<td colspan="3" valign="center" align="center">' . "\n";
        $this->content .= '<input type="submit" name="submit" value="absenden und warten!"/>' . "\n";
        $this->content .= '</td></tr>' . "\n";
        $this->content .= '</form>' . "\n";

        
        $this->content .= '<tr><td colspan="6">&nbsp;</td></tr>' . "\n";

        $this->content .= '</table>' . "\n";

        //Formular wurde abgeschickt
        if (isset($this->POST['submit'])) {
            $arrQuery = array();
            //collections
            $arrCol = array();
            if (!in_array(0, $this->POST['collect'])) {
                foreach ($this->POST['collect'] as $collect) {
                    $arrCol[] = 'DC:"'.$collect.'"';
                }
                $arrQuery[] = '('.implode(' AND ', $arrCol).')';
            }

            //Licenses
            $arrAcl = array();
            if(!in_array('all', $this->POST['license'])) {
                foreach($this->POST['license'] as $license) {
                    $arrAcl[] = 'ACL:"'.$license.'"';
                }
                $arrQuery[] = '('.implode(' AND ',$arrAcl).')';
            }
            
            $this->start = $this->POST['start']['year'][0].$this->POST['start']['month'][0].$this->POST['start']['day'][0];
            $this->end = $this->POST['end']['year'][0].$this->POST['end']['month'][0].$this->POST['end']['day'][0];


            // volumes
            $volumeQuery = 'ISWORK:1 AND DATEINDEXED:['.$this->start.' TO '.$this->end.']'; 

            if(count($arrQuery)) {
                $q = implode(' AND ', $arrQuery).' AND '.$periodicalQuery;
            } else {
                $q = $periodicalQuery;
            }
            $arrParams = array(
                'q' => urlencode($q),
                'start' => 0,
                'rows' => 99999,
            );
            $arrVolumeSolr = $this->getSolrResult($arrParams);

            //get all periodicals from start!
            $periodicalQuery = 'DOCSTRCT:periodical AND DATEINDEXED:[000000 TO '.$this->end.']'; 

            if(count($arrQuery)) {
                $q = implode(' AND ', $arrQuery).' AND '.$periodicalQuery;
            } else {
                $q = $periodicalQuery;
            }
            $arrParams = array(
                'q' => urlencode($q),
                'start' => 0,
                'rows' => 9999,
//                'sort' => 'BYTITLE+asc'
            );
            $arrPeriodicalSolr = $this->getSolrResult($arrParams);
print_r('<pre>');
print_r(urlencode(implode(' AND ', $arrQuery).' AND '.$periodicalQuery).'<br />');
print_r('</pre>');

            $this->arrResult = array();
            $this->arrSuccessors = array();
            foreach($arrPeriodicalSolr['response']['docs'] as $periodical) {
print_r('<pre>');
print_r(urlencode(implode(' AND ', $arrQuery).' AND '.$periodicalQuery).'<br />');
print_r($periodical);
print_r('</pre>');
               if(!isset($periodical['PRE'])) {
                    $this->arrResult[$periodical['PPN']] = $periodical;
                } else {
                    $this->arrSuccessors[$periodical['PPN']] = $periodical;
                }
            }
print_r('<pre>');
print_r($this->arrSuccessors);
print_r('</pre>');
            
            foreach($this->arrResult as $ppn=>$periodical) {
                if(isset($periodical['SUC'])) {
                    foreach($periodical['SUC'] as $_ppn) {
                        $this->getSuccessors($ppn, $_ppn);
                    }
                }
            }
/*
            foreach($this->arrResult as $id=>$periodical) {
                $this->getInfoFromMets($this->arrResult[$id]);
                $this->getInfoFromCache($this->arrResult[$id]);
            }

            $this->resortPeriodicals();
            foreach($this->arrResult as $key=>$val) {
                $arrTitle[$key] = $val['TITLE'];
            }
            array_multisort($arrTitle,$this->arrResult);
 */
print_r('<pre>');
//print_r($this->arrResult);
print_r('</pre>');
            
//debug($this->arrResult);
        }
/*
            // create excel sheets
            foreach($this->POST['struct'] as $struct) {
                $rowCount = 0;
                $mainSheet = excel::createSheet($xls,$struct);
                $mainTable = excel::createNode(array('dom'=>$xls, 'name'=>'Table','parent'=>$mainSheet));
                // spaltenbreiten
                // Titel
                excel::createNode(array('dom'=>$xls, 'name'=>'Column','arrAttributes'=>array('ss:Width'=>'150.0000'),'parent'=>$mainTable));
                // PPN
                excel::createNode(array('dom'=>$xls, 'name'=>'Column','arrAttributes'=>array('ss:Width'=>'100.0000'),'parent'=>$mainTable));
                // Copyright
                excel::createNode(array('dom'=>$xls, 'name'=>'Column','arrAttributes'=>array('ss:Width'=>'120.0000'),'parent'=>$mainTable));
                // Zahlen Spalten
                excel::createNode(array('dom'=>$xls, 'name'=>'Column','arrAttributes'=>array('ss:Span'=>'7','ss:Width'=>'60.0000'),'parent'=>$mainTable));
                if($struct=='periodicalvolume') {
                    // Datumsspalten
                    excel::createNode(array('dom'=>$xls, 'name'=>'Column','arrAttributes'=>array('ss:Span'=>'1','ss:Width'=>'70.0000'),'parent'=>$mainTable));
                }

                $row = excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:Height'=>'20.0000'),'parent'=>$mainTable));
                $rowCount ++;
    
                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1'),'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Titel', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1'),'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'PPN', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1'),'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Copyright', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1','ss:MergeAcross'=>count($this->config['arrWall'])+1),'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Alle bis '.$this->dateFormat($this->end), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1','ss:MergeAcross'=>count($this->config['arrWall'])+1),'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Vom '.$this->dateFormat($this->start).' bis '.$this->dateFormat($this->end), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                if($struct=='periodicalvolume') {
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading1','ss:MergeAcross'=>'1'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Band Importe', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                }

                $row = excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:Height'=>'15.0000'),'parent'=>$mainTable));
                $rowCount++;
    
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
    
                for($i=0;$i<2;$i++) {
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'Seiten', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
        
                    foreach($this->config['arrWall'] as $index=>$wall) {
                        if($index==0) {
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim('vor '.$wall), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                        } else if($index>0) {
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($this->config['arrWall'][$index-1].' - '.$wall), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                        }
                    }
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'nach '.$wall, 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                }

                if($struct=='periodicalvolume') {
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'erster', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
        
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'Heading2'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>'letzter', 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                }
   
                // leerzeile
                excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:Height'=>'8.0000'),'parent'=>$mainTable));
                $rowCount++;
    
                $startSum = $rowCount;
                foreach($this->arrResult as $id=>$periodical) {
                    $row = excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:AutoFitHeight'=>'1', 'ss:Height'=>'15.0000'),'parent'=>$mainTable));
                    $rowCount++;
    
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['TITLE']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:HRef'=>trim($this->config['ppnResolver'].$periodical['PPN']),'ss:StyleID'=>'yellowbluelink'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['PPN']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['COPYRIGHT']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
    
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][0]['before']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                    
                    $diff = 0;
                    foreach($this->config['arrWall'] as $index=>$wall) {
                        $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                        excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][$wall]['before']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                        $diff += $periodical['struct'][$struct][$wall]['before'];
                    }
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][0]['before'] - $diff), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
    
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][0]['between']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
    
                    $diff = 0;
                    foreach($this->config['arrWall'] as $index=>$wall) {
                        $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                        excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][$wall]['between']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                        $diff += $periodical['struct'][$struct][$wall]['between'];
                    }
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowback'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($periodical['struct'][$struct][0]['between'] - $diff), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
    
                    if($struct=='periodicalvolume') {
                        $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowbackright'),'parent'=>$row));
                        excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($this->dateFormat($periodical['FIRSTIMPORT'])), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
        
                        $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'yellowbackright'),'parent'=>$row));
                        excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($this->dateFormat($periodical['LASTIMPORT'])), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                    }

                    // Vorgänger
                    if(is_array($periodical['PREDECESSOR'])) {
                        foreach($periodical['PREDECESSOR'] as $_id=>$_periodical) {
                            if(substr($_id,0,3)!='PPN') {
                                continue;
                            }
                            $row = excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:AutoFitHeight'=>'1', 'ss:Height'=>'15.0000'),'parent'=>$mainTable));
                            $rowCount++;
            
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'topwrap'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['TITLE']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
            
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:HRef'=>trim($this->config['ppnResolver'].$_periodical['PPN']),'ss:StyleID'=>'bluelink'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['PPN']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
            
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['COPYRIGHT']), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
            
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][0]['before']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                            
                            $diff = 0;
                            foreach($this->config['arrWall'] as $index=>$wall) {
                                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][$wall]['before']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                                $diff += $_periodical['struct'][$struct][$wall]['before'];
                            }
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][0]['before'] - $diff), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
            
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][0]['between']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
            
                            $diff = 0;
                            foreach($this->config['arrWall'] as $index=>$wall) {
                                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][$wall]['between']), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                                $diff += $_periodical['struct'][$struct][$wall]['between'];
                            }
                            $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'top'),'parent'=>$row));
                            excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($_periodical['struct'][$struct][0]['between'] - $diff), 'arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
    
                            if($struct=='periodicalvolume') {
                                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'right'),'parent'=>$row));
                                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($this->dateFormat($_periodical['FIRSTIMPORT'])), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                
                                $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'right'),'parent'=>$row));
                                excel::createNode(array('dom'=>$xls, 'name'=>'Data', 'value'=>trim($this->dateFormat($_periodical['LASTIMPORT'])), 'arrAttributes'=>array('ss:Type'=>'String'),'parent'=>$cell));
                            }
                        }
                    }
                    // leerzeile
//                    excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:Height'=>'8.0000'),'parent'=>$mainTable));
//                    $rowCount++;
                }
    
                // summen berechnen
                $row = excel::createNode(array('dom'=>$xls, 'name'=>'Row','arrAttributes'=>array('ss:Height'=>'15.0000'),'parent'=>$mainTable));
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
                excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'parent'=>$row));
    
                for($i=0; $i<(4+2*count($this->config['arrWall'])); $i++) { 
                    $cell = excel::createNode(array('dom'=>$xls, 'name'=>'Cell', 'arrAttributes'=>array('ss:StyleID'=>'redcharsbordertop','ss:Formula'=>'=SUM(R[-'.($rowCount-$startSum).']C:R[-1]C)'),'parent'=>$row));
                    excel::createNode(array('dom'=>$xls, 'name'=>'Data','arrAttributes'=>array('ss:Type'=>'Number'),'parent'=>$cell));
                }
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=DigiZeit_Seiten_'.$createDate_file.'.xml');
            echo $xls->saveXML();
            exit();
        }
*/
            
            
    }

//####################################################################################
//## end MAIN ########################################################################
//####################################################################################
    
    function getSuccessors($ppn, $_ppn) {
        $this->arrResult[$ppn]['SUCCESSOR'][$_ppn] = $this->arrSuccessors[$_ppn];
        if($this->arrSuccessors[$_ppn]['PRE']) {
            foreach($this->arrSuccessors[$_ppn]['PRE'] as $PPN) {
                $this->getSuccessors($ppn, $PPN);
            }
        }
    }
    
    function resortPeriodicals() {
        foreach($this->arrResult as $id=>$periodical) {
            //periodical has real successor
            if($periodical['SUCCESSOR'] && !($periodical['PREDECESSOR'] && count(array_intersect($periodical['SUCCESSOR'],$periodical['PREDECESSOR'])))) {
                //get main master
                $master = $this->getMainMaster($periodical);
                if(!$this->arrResult[$master['PPN']]) {
                    $this->arrResult[$master['PPN']] = $master;
                    $this->getInfoFromMets($this->arrResult[$master['PPN']]);                        
                    $this->getInfoFromCache($this->arrResult[$master['PPN']]);
                }
                $arrKey = array_keys($this->arrResult[$master['PPN']]['PREDECESSOR'],$id);
                if($arrKey[0]) {
                    $this->arrResult[$master['PPN']]['PREDECESSOR'][$id] = $periodical;
                    unset($this->arrResult[$master['PPN']]['PREDECESSOR'][$arrKey[0]]);
                } else {
                    $this->arrResult[$master['PPN']]['PREDECESSOR'][$id] = $periodical;
                }
                unset($this->arrResult[$id]);
            }
        }
    }

    function getMainMaster($periodical) {       
        if(!$periodical['SUC']|| ($periodical['SUC'] && $periodical['PRE'] && count(array_intersect($periodical['SUC'],$periodical['PRE'] )))) {
            return $periodical;
        } else {
            return $this->arrResult($periodical['SUC'][0]);
        }
    }

    function getInfoFromMets(&$arr) {
        $dom = mets::openMetsAsDom($arr['PPN']);
        if(!$dom) {
            return false;
        }
        $xpath = mets::openXPath($dom);
        if(!$xpath) {
            return false;
        }        
        //copyright
        $nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:accessCondition[@type="copyright"]');
        if($nodeList->length) {
            $arr['COPYRIGHT'] = trim($nodeList->item(0)->nodeValue);
        }    
    }

    function getInfoFromCache(&$arr) {
        $term = lucene::term('IDPARENTDOC',$arr['IDDOC']);
        $query = lucene::termQuery($term);   
        $sort = lucene::sort(array(array('field'=>'DATEINDEXED','order'=>false)));
        $ptr = lucene::search($query, null, false, $sort);
        $limit = lucene::length($ptr);
        
        
        $arrParams = array(
            'q' => urlencode('IDPARENTDOC:"'.$arr['IDDOC'].'"'),
            'start' => 0,
            'rows' => 9999,
            'sort' => 'DATEINDEXED asc',
        );
        $arrSolr = $this->getSolrResult($arrParams);

        
        if($arrSolr['response']['numFound']) {
            $arrResult = $arrSolr['response']['docs'];
            
            foreach($this->config['arrWall'] as $wall) {
                foreach($this->POST['struct'] as $struct) {
                    $arr[$struct][$wall]['between'] = 0;
                    $arr[$struct][$wall]['before'] = 0;
                }
            }
            foreach($arrResult as $volume) {
                $volume['YEARPUBLISH'] = str_replace(array('('.'{','[',']','}',')'),'',$volume['YEARPUBLISH']);
                $volume['YEARPUBLISH'] = intval(trim(array_shift(explode('/',$volume['YEARPUBLISH']))));
                $entry = berkeley::getDbaEntry($volume['PPN']);
                if($volume['DATEINDEXED']<=$this->end) {
                    foreach($this->POST['struct'] as $struct) {
                        $arr['struct'][$struct][0]['before'] += $entry['type'][$struct];
                    }
                }
                if($volume['DATEINDEXED']<=$this->end && $volume['DATEINDEXED']>=$this->start) {
                    foreach($this->POST['struct'] as $struct) {
                        $arr['struct'][$struct][0]['between'] += $entry['type'][$struct];
                    }
                }
                foreach($this->config['arrWall'] as $key=>$wall) {
                    if($key==0) {
                        $lower = 0;
                    } else {
                        $lower = $this->config['arrWall'][$key-1];
                    }
                    if($volume['YEARPUBLISH']>$lower && $volume['YEARPUBLISH']<=$wall) {
                        if($volume['DATEINDEXED']<=$this->end) {
                            foreach($this->POST['struct'] as $struct) {
                                $arr['struct'][$struct][$wall]['before'] += $entry['type'][$struct];
                            }
                        }
                        if($volume['DATEINDEXED']<=$this->end && $volume['DATEINDEXED']>=$this->start ) {
                            foreach($this->POST['struct'] as $struct) {
                                $arr['struct'][$struct][$wall]['between'] += $entry['type'][$struct];
                            }
                        }
                    }
                }
            }
            $arr['FIRSTIMPORT'] = $arrResult[0]['DATEINDEXED'];
            $arr['LASTIMPORT'] = $arrResult[$limit-1]['DATEINDEXED'];

        }        
    }

    function dateFormat($YYYYMMDD) {
        return  substr($YYYYMMDD,6,2).'.'.substr($YYYYMMDD,4,2).'.'.substr($YYYYMMDD,0,4);
    }

    function getCheckBox($key) {
            if(isset($this->POST[$key])) return 'checked';
    }

    function getLicenseForm() {
        $arrParams = array(
            'q' => urlencode('ACL:*'),
            'start' => 0,
            'rows' => 0,
            'facet' => 'on',
            'facet.field' => 'ACL',
            'facet.sort' => 'lexicographic',
        );
        $arrSolr = $this->getSolrResult($arrParams);
        $arrACL = $arrSolr['facet_counts']['facet_fields']['ACL'];
        $arrACL = array_merge(array('all'=>'All'), $arrACL);

        $i = 0;
        foreach($arrACL as $acl=>$count) {
            $license[$i]['item'] = $acl;
            if(isset($this->POST['license'])) {
                if(in_array($license[$i]['item'],$this->POST['license'])) {
                    $license[$i]['selected'] = 'selected="selected"';
                } else {
                    $license[$i]['selected'] = '';
                }
            } else {
                $license[$i]['selected'] = '';
            }
            $i++;
        }

        reset($license);
        $this->content .= '<select name="license[]" size="10" multiple>'."\n";
        foreach($license as $val) {
                $this->content .= '<option value="'.strtolower($val['item']).'" '.$val['selected'].'>'.$val['item'].'</option>'."\n";
        }
        $this->content .= '</select>'."\n";
    }

    function getCollectionForm() {
        $collect[0]['item'] = 'All';
        $collect[0]['value'] = 0;
        if(isset($this->POST['collect'])) {
            if(in_array($collect[0]['value'],$this->POST['collect'])) {
                $collect[0]['selected'] = 'selected="selected"';
            } else {
                $collect[0]['selected'] = '';
            }
        }  else {
            $collect[0]['selected'] = 'selected';
        }
        $i = 1;

        $arrParams = array(
            'q' => urlencode('DOCSTRCT:periodical'),
            'start' => 0,
            'rows' => 0,
            'facet' => 'on',
            'facet.field' => 'DC',
            'facet.sort' => 'lexicographic',
        );
        $arrSolr = $this->getSolrResult($arrParams);
        $arrFields = $arrSolr['facet_counts']['facet_fields']['DC'];

        foreach($arrFields as $field=>$count) {
            $collect[$i]['item'] = $field;
            $collect[$i]['value'] = $field;
            if(isset($this->POST['collect'])) {
                if(in_array($collect[$i]['value'],$this->POST['collect'])) {
                    $collect[$i]['selected'] = 'selected="selected"';
                } else { 
                    $collect[$i]['selected'] = '';
                }
            } else {
                $collect[$i]['selected'] = '';
            }
            $i++;
        }

        reset($collect);
        $this->content .= '<select name="collect[]" size="10" multiple>'."\n";
        foreach($collect as $val) {
            $this->content .= '<option value="'.$val['value'].'" '.$val['selected'].'>'.$val['item'].'</option>'."\n";
        }
        $this->content .= '</select>'."\n";
    }

        
        
    function getStructForm() {
        $struct[0]['item'] = 'All';
        $struct[0]['value'] = 'periodicalvolume';
        $struct[0]['selected'] = 'selected="selected"';

        $i = 1;
        $arrParams = array(
            'q' => urlencode('DOCSTRCT:*'),
            'start' => 0,
            'rows' => 0,
            'facet' => 'on',
            'facet.field' => 'DOCSTRCT',
            'facet.sort' => 'lexicographic',
        );
        $arrSolr = $this->getSolrResult($arrParams);
        $arrFields = $arrSolr['facet_counts']['facet_fields']['DOCSTRCT'];
        foreach($arrFields as $field=>$count) {
            $struct[$i]['item'] = $field;
            $struct[$i]['value'] = $field;
            if(isset($this->POST['struct'])) {
                if(in_array($struct[$i]['value'],$this->POST['struct'])) {
                    $struct[$i]['selected'] = 'selected="selected"';
                } else {
                    $struct[$i]['selected'] = '';
                }
            } else {
                $struct[$i]['selected'] = '';
            }
            $i++;
        }

        reset($struct);
        $this->content .= '<select name="struct[]" size="10" multiple>'."\n";
        foreach($struct as $val) {
            $this->content .= '<option value="'.$val['value'].'" '.$val['selected'].'>'.$val['item'].'</option>'."\n";
        }
        $this->content .= '</select>'."\n";
    }

    function getDateForm($name) {
        $this->content .= '<select name="'.$name.'[day][]" size="1">'."\n";
        for($day=1;$day<=31;$day++) {
            if(isset($this->POST[$name]['day'])) {
                if($this->POST[$name]['day'][0]==substr(('0'.$day),-2)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            } else {
                if(substr($this->config[$name],6,2)==substr(('0'.$day),-2)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            }
            $this->content .= '<option value="'.substr(('0'.$day),-2).'" '.$selected.'>'.substr(('0'.$day),-2).'</option>'."\n";
        }
        $this->content .= '</select>'."\n";

        $this->content .= '<select name="'.$name.'[month][]" size="1">'."\n";
        for($month=1;$month<=12;$month++) {
            if(isset($this->POST[$name]['month'])) {
                if($this->POST[$name]['month'][0]==substr(('0'.$month),-2)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            } else {
                if(substr($this->config[$name],4,2)==substr(('0'.$month),-2)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            }
            $this->content .= '<option value="'.substr(('0'.$month),-2).'" '.$selected.'>'.substr(('0'.$month),-2).'</option>'."\n";
        }
        $this->content .= '</select>'."\n";

        $this->content .= '<select name="'.$name.'[year][]" size="1">'."\n";
        for($year=substr($this->config['start'],0,4);$year<=substr($this->config['end'],0,4);$year++) {
            if(isset($this->POST[$name]['year'])) {
                if($this->POST[$name]['year'][0]==$year) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            } else {
                if(substr($this->config[$name],0,4)==$year) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            }
            $this->content .= '<option value="'.$year.'" '.$selected.'>'.$year.'</option>'."\n";
        }
        $this->content .= '</select>'."\n";
    }
    
    function getSolrResult($arr) {
        $strSolr = '';
        foreach($arr as $key=>$val) {
            $strSolr .='&'.$key.'='.$val;
        }

        $arrSolr = unserialize(file_get_contents($this->config['solrPhpsUrl'] . $strSolr));
        foreach($arrSolr['response']['docs'] as $key=>$val) {
            foreach($val as $k=>$v) {
                if(in_array($k, $this->config['arrSerFields'])) {
                    $arrSolr['response']['docs'][$key][$k] = $this->_unserialize($v);
                }
            }
        }
        return $arrSolr;
    }
    
    function _unserialize($str) {
        $ret = json_decode($str,true);
        if(!is_array($ret)) {
            $ret = unserialize($str);
        }
        return $ret;
    }
}

$vgwort = new vgwort;
$vgwort->main();
print_r($vgwort->content);
?>
