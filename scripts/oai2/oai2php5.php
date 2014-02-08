<?php

/* * *************************************************************************
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
 * ************************************************************************* */

class oai2 {

    var $conf;

//####################################################################################
//### MAIN ###########################################################################
//####################################################################################
    function oai2($conf) {
        $this->conf = $conf;
        error_reporting($this->conf['MAIN']['error_reporting']);
        // get temp diretory
        if(!trim($this->conf['MAIN']['tmpDir']) || !is_dir(trim($this->conf['MAIN']['tmpDir']))) {
            $this->conf['MAIN']['tmpDir'] = sys_get_temp_dir().'/';
        }
        // connect to lucene
        if ($this->conf['DB']['engine'] == 'lucene') {
            $this->analyzer = oailucene::analyzer();
            if (!$this->analyzer)
                print_r('ERROR: No analyzer' . "\n");
            $this->parser = oailucene::parser();
            if (!$this->parser)
                print_r('ERROR: No parser' . "\n");
            $this->searcher = oailucene::searcher();
            if (!$this->searcher)
                print_r('ERROR: No searcher' . "\n");
        }

        $noerror = TRUE;

        // create XML-DOM
        $this->oai = new DOMDocument('1.0', 'UTF-8');

        //nice output format (linebreaks and tabs)
        $this->oai->formatOutput = $this->conf['MAIN']['formatOutput'];

        //insert xsl
        $this->oai->appendChild($this->oai->createProcessingInstruction('xml-stylesheet', 'href="' . $this->conf['MAIN']['xsl'] . '" type="text/xsl"'));

        //Create the root-element
        $this->oai_pmh = $this->oai->createElement('OAI-PMH');
        foreach ($this->conf['OAI-PMH'] as $attribute => $value) {
            $this->oai_pmh->setAttribute($attribute, $value);
        }

        $this->oai->appendChild($this->oai_pmh);


        $responseDate = $this->oai->createElement('responseDate', gmdate('Y-m-d\TH:i:s\Z', time()));
        $this->oai_pmh->appendChild($responseDate);

        $this->request = $this->oai->createElement('request', $this->conf['Identify_tags']['baseURL']);

        //######################################################################################
        //### Parse the GET- and POSTVARS ######################################################
        //######################################################################################
        $prevkey = '';
        $arrArgs = array_merge($_GET, $_POST);
        unset($arrArgs['id']);

        //prepare answer
        if (is_array($arrArgs)) {
            foreach ($arrArgs as $key => $val) {
                if ($key == 'from' || $key == 'until') {
                    if (strlen($val) != 10)
                        continue;
                    $test = date_parse($val);
                    if (!$test || count($test['errors']))
                        continue;
                }
                if (array_key_exists($key, $this->conf['requestAttributes'])) {
                    if ($key == 'verb' && !array_key_exists($val, $this->conf['verbs']))
                        continue;
                    $this->request->setAttribute($key, $val);
                }
                if (array_key_exists($val, $this->conf['verbs'])) {
                    $this->request->setAttribute($key, $val);
                }
            }
        }
        $this->oai_pmh->appendChild($this->request);


        //same argument
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
            $strQuery = $_SERVER['QUERY_STRING'];
        else if ($_SERVER['REQUEST_METHOD'] == 'POST')
            $strQuery = file_get_contents('php://input');
        else
            $strQuery = '';
        $arrTmp = explode('&', $strQuery);
        if (isset($arrTmp) && count($arrTmp) > 1) {
            if (count($arrTmp) != count($GLOBALS['_' . $_SERVER['REQUEST_METHOD']])) {
//				$noerror = $this->oai_error('sameArgument') && $noerror;
                foreach ($GLOBALS['_' . $_SERVER['REQUEST_METHOD']] as $key => $val) {
                    $arrKey = array_search($key . '=' . $val, $arrTmp);
                    if ($arrKey !== false)
                        unset($arrTmp[$arrKey]);
                }
                foreach ($arrTmp as $val) {
                    $_arrTmp = explode('=', $val);
                    $arrErr[$_arrTmp[0]] = $_arrTmp[1];
                    $noerror = $this->oai_error('badArgument', $arrErr) && $noerror;
                }
            }
        }

        //No verb
        if (count($arrArgs) == 0 || !isset($arrArgs['verb'])) {
            $noerror = $this->oai_error('badVerb', array('NOVERB' => '')) && $noerror;
        }

        //resumptionToken is an exclusive argument, so get all necessary args from token
        //or stop all other action
        if (is_array($arrArgs) && isset($arrArgs['resumptionToken'])) {
            if ((count($arrArgs) == 2 && !isset($arrArgs['verb'])) || count($arrArgs) > 2) {
                $arrTmp = $arrArgs;
                unset($arrTmp['resumptionToken']);
                $noerror = $this->oai_error('badArgument', $arrTmp) && $noerror;
                unset($arrTmp);
            }
            $noerror = $this->restoreArgs($arrArgs) && $noerror;
        }
        if (isset($arrArgs['verb'])) {
            if (!array_key_exists($arrArgs['verb'], $this->conf['verbs'])) {
                $noerror = $this->oai_error('badVerb', array($arrArgs['verb'] => '')) && $noerror;
            }
        }
        //######################################################################################
        //### end parse the GET- and POSTVARS ##################################################
        //######################################################################################
        //if isset arrArgs['start'] no more checks!
        if (!isset($arrArgs['start']) && isset($arrArgs['verb'])) {
            if (isset($this->conf[$arrArgs['verb']]['requiredArguments']))
                $noerror = ($this->errorRequiredArguments($arrArgs['verb'], $arrArgs) && $noerror);
            $noerror = ($this->errorAllowedArguments($arrArgs['verb'], $arrArgs) && $noerror);
        }

        if (!isset($arrArgs['from']) && isset($arrArgs['until'])) {
            if (isset($this->conf[$arrArgs['verb']]['requiredArguments']))
                $noerror = ($this->errorRequiredArguments($arrArgs['verb'], $arrArgs) && $noerror);
            $noerror = ($this->errorAllowedArguments($arrArgs['verb'], $arrArgs) && $noerror);
        }

//#######################################################################################
//#### Identity #########################################################################
//#######################################################################################
        if ($arrArgs['verb'] == 'Identify') {
            if (!$noerror)
                return;
            $Identify = $this->oai->createElement('Identify');
            $this->oai_pmh->appendChild($Identify);
            foreach ($this->conf['Identify_tags'] as $key => $val) {
                $$key = $this->oai->createElement($key, $val);
                $Identify->appendChild($$key);
                //insert node before node: 'adminEmail'
                if ($key == 'adminEmail') {
                    //get earliest datestamp
                    $earliestDatestamp = $this->oai->createElement('earliestDatestamp', oailib::getDatestamp());
                    $Identify->appendChild($earliestDatestamp);
                }
            }
            if (isset($this->conf['oai-identifier'])) {
                $desc = $this->oai->createElement('description');
                $Identify->appendChild($desc);
                $oai_id = $this->oai->createElement('oai-identifier');
                if (isset($this->conf['oai-identifier']['xmlns'])) {
                    $oai_id->setAttribute('xmlns', trim($this->conf['oai-identifier']['xmlns']));
                    unset($this->conf['oai-identifier']['xmlns']);
                }
                if (isset($this->conf['oai-identifier']['xmlns:xsi'])) {
                    $oai_id->setAttribute('xmlns:xsi', trim($this->conf['oai-identifier']['xmlns:xsi']));
                    unset($this->conf['oai-identifier']['xmlns:xsi']);
                }
                if (isset($this->conf['oai-identifier']['xsi:schemaLocation'])) {
                    $oai_id->setAttribute('xsi:schemaLocation', trim($this->conf['oai-identifier']['xsi:schemaLocation']));
                    unset($this->conf['oai-identifier']['xsi:schemaLocation']);
                }
                $desc->appendChild($oai_id);
//                $Identify->appendChild($oai_id);
                foreach ($this->conf['oai-identifier'] as $key => $val) {
                    $$key = $this->oai->createElement($key, trim($val));
                    $oai_id->appendChild($$key);
                }
            }
            $noerror = FALSE;
        }
//#######################################################################################
//#### END Identity #####################################################################
//#######################################################################################
//#######################################################################################
//######## ListMetadataFormats ##########################################################
//#######################################################################################
        if ($arrArgs['verb'] == 'ListMetadataFormats') {

            if (isset($arrArgs['identifier'])) {
                $arrFormats = oailib::getMetadataFormats($arrArgs['identifier']);
                if (!$arrFormats)
                    return;
                $ListMetadataFormats = $this->oai->createElement('ListMetadataFormats');
                $this->oai_pmh->appendChild($ListMetadataFormats);
                foreach ($arrFormats as $format) {
                    $metadataFormat = $this->oai->createElement('metadataFormat');
                    $ListMetadataFormats->appendChild($metadataFormat);
                    $node = $this->oai->createElement('metadataPrefix', $format);
                    $metadataFormat->appendChild($node);
                    foreach ($this->conf[$format] as $key => $val) {
                        $$key = $this->oai->createElement($key, $val);
                        $metadataFormat->appendChild($$key);
                    }
                }
            } else {
                if (!$noerror)
                    return;
                $ListMetadataFormats = $this->oai->createElement('ListMetadataFormats');
                $this->oai_pmh->appendChild($ListMetadataFormats);
                foreach ($this->conf['metadataFormats'] as $format => $v) {
                    $metadataFormat = $this->oai->createElement('metadataFormat');
                    $ListMetadataFormats->appendChild($metadataFormat);
                    $node = $this->oai->createElement('metadataPrefix', $format);
                    $metadataFormat->appendChild($node);
                    foreach ($this->conf[$format] as $key => $val) {
                        $$key = $this->oai->createElement($key, $val);
                        $metadataFormat->appendChild($$key);
                    }
                }
            }
        }
//#######################################################################################
//#### END ListMetadataFormats ##########################################################
//#######################################################################################
//#######################################################################################
//######## ListSets #####################################################################
//#######################################################################################
        if ($arrArgs['verb'] == 'ListSets') {
            $ListSets = $this->oai->createElement('ListSets');
            $this->oai_pmh->appendChild($ListSets);

            if (is_array($this->conf['sets'])) {
                foreach ($this->conf['sets'] as $key => $val) {
                    $set = $this->oai->createElement('set');
                    $ListSets->appendChild($set);
                    $node = $this->oai->createElement('setSpec', $key);
                    $set->appendChild($node);
                    $node = $this->oai->createElement('setName', $val);
                    $set->appendChild($node);
                }
            } else {
                $this->oai_error('noSetHierarchy');
            }
            return;
        }
//#######################################################################################
//#### END ListSets #####################################################################
//#######################################################################################
//#######################################################################################
//#### GetRecord ########################################################################
//#######################################################################################
        if ($arrArgs['verb'] == 'GetRecord') {
            //error handling
            $noerror = ($noerror && $this->errorMetadataPrefix($arrArgs));

            oailib::getRecords($arrArgs, $arrResult);

            if (!$arrResult['hits']) {
                $noerror = $this->oai_error('noRecordsMatch') && $noerror;
            }
            if (!$noerror)
                return;
            $this->ListRecords = $this->oai->createElement($arrArgs['verb']);
            $this->oai_pmh->appendChild($this->ListRecords);
            foreach ($arrResult['header'] as $key => $val) {
                $this->record = $this->oai->createElement('record');
                $this->ListRecords->appendChild($this->record);
                $this->head = $this->oai->createElement('header');
                $this->record->appendChild($this->head);
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $_v) {
                            $this->node = $this->oai->createElement($k);
                            $this->node->appendChild(new DOMText($_v));
                            $this->head->appendChild($this->node);
                        }
                    } else {
                        $this->node = $this->oai->createElement($k);
                        $this->node->appendChild(new DOMText($v));
                        $this->head->appendChild($this->node);
                    }
                }
                $this->metadata = $this->oai->createElement('metadata');
                $this->record->appendChild($this->metadata);

                switch ($arrArgs['metadataPrefix']) {
                    case 'oai_dc':
                        $this->oai_dc = $this->oai->createElement('oai_dc:dc');
                        foreach ($this->conf['oai_dc:dc'] as $attribute => $value) {
                            $this->oai_dc->setAttribute($attribute, $value);
                        }
                        $this->metadata->appendChild($this->oai_dc);
                        foreach ($arrResult['metadata'][$key] as $k => $v) {
                            foreach ($v as $_v) {
                                if ($_v) {
                                    if ($k == 'dc:description') {
                                        $data = $this->oai->createCDATASection($_v);
                                    } else {
                                        $data = new DOMText($_v);
                                    }
                                    $this->node = $this->oai->createElement($k);
                                    $this->node->appendChild($data);
                                    $this->oai_dc->appendChild($this->node);
                                }
                            }
                        }
                        break;
                    case 'mets':
                        foreach ($arrResult['metadata'][$key] as $k => $v) {
                            $tmp = new DOMDocument();
                            $test = $tmp->loadXML($v);
                            if ($test) {
                                $mets = $tmp->getElementsByTagName('mets')->item(0);
                                $import = $this->oai->importNode($mets, true);
                                $this->metadata->appendChild($import);
                            }
                        }
                        break;
                }
            }

            //getRecord
        }
//#######################################################################################
//#### END GetRecord ####################################################################
//#######################################################################################
//#######################################################################################
//#### ListRecords / LstIdentifiers #####################################################
//#######################################################################################
        if ($arrArgs['verb'] == 'ListRecords' || $arrArgs['verb'] == 'ListIdentifiers') {
            //error handling
            $noerror = ($this->errorDate($arrArgs) && $noerror);
//			$noerror = ($this->errorSet($arrArgs) && $noerror);
            $noerror = ($this->errorMetadataPrefix($arrArgs) && $noerror);
            $noerror = ($this->errorFromUntil($arrArgs) && $noerror);
            oailib::getRecords($arrArgs, $arrResult);
            if (!$arrResult['hits']) {
                $noerror = $this->oai_error('noRecordsMatch') && $noerror;
            }
            if (!$noerror)
                return;

            $this->ListRecords = $this->oai->createElement($arrArgs['verb']);
            $this->oai_pmh->appendChild($this->ListRecords);
            foreach ($arrResult['header'] as $key => $val) {
                if ($arrArgs['verb'] == 'ListRecords') {
                    $this->record = $this->oai->createElement('record');
                    $this->ListRecords->appendChild($this->record);
                    $this->head = $this->oai->createElement('header');
                    $this->record->appendChild($this->head);
                } else {
                    $this->head = $this->oai->createElement('header');
                    $this->ListRecords->appendChild($this->head);
                }
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $_v) {
                            $this->node = $this->oai->createElement($k);
                            $this->node->appendChild(new DOMText($_v));
                            $this->head->appendChild($this->node);
                        }
                    } else {
                        $this->node = $this->oai->createElement($k);
                        $this->node->appendChild(new DOMText($v));
                        $this->head->appendChild($this->node);
                    }
                }
                if ($arrArgs['verb'] == 'ListRecords') {
                    $this->metadata = $this->oai->createElement('metadata');
                    $this->record->appendChild($this->metadata);
                    switch ($arrArgs['metadataPrefix']) {
                        case 'oai_dc':
                            $this->oai_dc = $this->oai->createElement('oai_dc:dc');
                            foreach ($this->conf['oai_dc:dc'] as $attribute => $value) {
                                $this->oai_dc->setAttribute($attribute, $value);
                            }
                            $this->metadata->appendChild($this->oai_dc);
                            foreach ($arrResult['metadata'][$key] as $k => $v) {
                                foreach ($v as $_v) {
                                    if ($_v) {
                                        if ($k == 'dc:description') {
                                            $data = $this->oai->createCDATASection($_v);
                                        } else {
                                            $data = new DOMText($_v);
                                        }
                                        $this->node = $this->oai->createElement($k);
                                        $this->node->appendChild($data);
                                        $this->oai_dc->appendChild($this->node);
                                    }
                                }
                            }
                            break;
                        case 'mets':
                            foreach ($arrResult['metadata'][$key] as $k => $v) {
                                $tmp = new DOMDocument();
                                $test = $tmp->loadXML($v);
                                if ($test) {
                                    $mets = $tmp->getElementsByTagName('mets')->item(0);
                                    $import = $this->oai->importNode($mets, true);
                                    $this->metadata->appendChild($import);
                                }
                            }
                            break;
                    }
                }
            }
            //we need a resumptionToken?
            if (isset($arrResult['token'])) {
                $resumptionToken = $this->oai->createElement('resumptionToken', $arrResult['token']);
                $resumptionToken->setAttribute('expirationDate', (gmdate('Y-m-d\TH:i:s\Z', (time() + $this->conf['MAIN']['expirationDate']))));
                $resumptionToken->setAttribute('completeListSize', $arrResult['hits']);
                $resumptionToken->setAttribute('cursor', $arrArgs['start']);
                $this->ListRecords->appendChild($resumptionToken);
            } else {
                //return an empty resumptionToken?
                $resumptionToken = $this->oai->createElement('resumptionToken', '');
                $resumptionToken->setAttribute('expirationDate', (gmdate('Y-m-d\TH:i:s\Z', (time() + $this->conf['MAIN']['expirationDate']))));
                $resumptionToken->setAttribute('completeListSize', $arrResult['hits']);
                $resumptionToken->setAttribute('cursor', $arrArgs['start']);
                $this->ListRecords->appendChild($resumptionToken);
            }
        }
//#######################################################################################
//#### END ListRecords ##################################################################
//#######################################################################################
    }

//####################################################################################
//## END MAIN ########################################################################
//####################################################################################
//####################################################################################
//### ERROR Handling #################################################################
//####################################################################################

    function errorDate(&$arr) {
        //Array $regs :
        //[0] => YYYY-MM-DDTHH:MM:SSZ
        //[1] => YYYY
        //[2] => MM
        //[3] => DD
        //[4] => THH:MM:SSZ
        //[5] => T
        //[6] => HH
        //[7] => MM
        //[8] => SS
        //[9] => Z
        $arrDates = array('from' => '00:00:00', 'until' => '23:59:59');
        $noerror = TRUE;
        foreach ($arrDates as $key => $val) {
            if (isset($arr[$key])) {
                ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})(([T]{1})([0-9]{2}):([0-9]{2}):([0-9]{2})([Z]{1}){1})?", $arr[$key], $regs);
                if ($regs[1] != '' && $regs[4] != '')
                    $arr['DB' . $key] = $regs[1] . '-' . $regs[2] . '-' . $regs[3] . ' ' . $regs[6] . ':' . $regs[7] . ':' . $regs[8];
                else if ($regs[1] != '' && strlen($arr[$key]) == 10)
                    $arr['DB' . $key] = $regs[1] . '-' . $regs[2] . '-' . $regs[3] . ' ' . $val;
                else {
                    $noerror = FALSE;
                    $this->oai_error('badArgument', array($key => $arr[$key]));
                }
            }
        }
        return $noerror;
    }

    function errorFromUntil(&$arr) {
        $noerror = TRUE;
        if (isset($arr['from']) && isset($arr['until'])) {
            if ((strlen($arr['from'])) != (strlen($arr['until']))) {
                $this->oai_error('badArgument', array('from' => $arr['from'], 'until' => $arr['until']));
                $noerror = FALSE;
            } else if (($arr['from']) > ($arr['until'])) {
                $this->oai_error('badArgument', array('from' => $arr['from'], 'until' => $arr['until']));
                $noerror = FALSE;
            }
        } else if (isset($arr['from'])) {
            if (strLen($arr['from']) > strLen($this->conf['Identify_tags']['granularity'])) {
                $this->oai_error('badArgument', array('from' => $arr['from']));
                $noerror = FALSE;
            }
        } else if (isset($arr['until'])) {
            if (strLen($arr['until']) > strLen($this->conf['Identify_tags']['granularity'])) {
                $this->oai_error('badArgument', array('until' => $arr['until']));
                $noerror = FALSE;
            }
        }
        return $noerror;
    }

    function errorMetaDataPrefix(&$arr) {
        $noerror = TRUE;
        if (isset($arr['metadataPrefix'])) {
            if (!array_key_exists($arr['metadataPrefix'], $this->conf['metadataFormats'])) {
                $noerror = FALSE;
                $this->oai_error('badArgument', array('metadataPrefix' => $arr['metadataPrefix']));
            }
        } else {
            $noerror = FALSE;
            $this->oai_error('badArgument', array('metadataPrefix' => ''));
        }
        return $noerror;
    }

    function errorSet(&$arr) {
        $noerror = TRUE;
        if (isset($arr['set'])) {
            if (!count($this->conf['sets'])) {
                $this->oai_error('noSetHierarchy');
            } else if (!array_key_exists($arr['set'], $this->conf['sets'])) {
                $noerror = FALSE;
                $this->oai_error('badArgument', array('set' => $arr['set']));
            }
        }
        return $noerror;
    }

    function errorAllowedArguments($verb, $arr) {
        $noerror = TRUE;
        foreach ($arr as $key => $val) {
            if ($key != 'verb' && !@in_array($key, explode(',', $this->conf[$verb]['allowedArguments']))) {
                $noerror = FALSE;
                $this->oai_error('badArgument', array($key => $val));
            }
        }
        return $noerror;
    }

    function errorRequiredArguments($verb, $arr) {
        $arrTmp = explode(',', $this->conf[$verb]['requiredArguments']);
        unset($arr['verb']);
        foreach ($arrTmp as $key => $val) {
            if (isset($arr[$val])) {
                unset($arrTmp[$key]);
                reset($arrTmp);
            }
        }
        if (count($arrTmp)) {
            $noerror = FALSE;
            foreach ($arrTmp as $val) {
                $this->oai_error('badArgument', array($val => ''));
            }
        } else
            $noerror = TRUE;
        return $noerror;
    }

    function oai_error($err, $arr = array()) {
        $i = 0;
        foreach ($arr as $arg => $val) {
            $strError = str_replace(array('_ARG' . $i . '_', '_VAL' . $i . '_'), array($arg, $val), $this->conf['errors'][$err]);
            $i++;
        }
        if (!isset($strError))
            $strError = $this->conf['errors'][$err];
        $strError = str_replace('_ERR_', $err, $strError);
        $error = $this->oai->createElement('error', $strError);
        $error->setAttribute('code', $err);
        $this->oai_pmh->appendChild($error);
        return false;
    }

//####################################################################################
//### ERROR Handling #################################################################
//####################################################################################
//########################################################################################
//####################################################################################
//### functions ######################################################################
//####################################################################################
    function trimexplode($delimiter, $str) {
        $arrTmp = explode($delimiter, $str);
        foreach ($arrTmp as $k => $v) {
            $arrTmp[$k] = trim($v);
        }
        return $arrTmp;
    }

    function restoreArgs(&$arr) {
        $strToken = @file_get_contents($this->conf['MAIN']['tmpDir'] . $arr['resumptionToken']);
        if ($strToken != '') {
            parse_str($strToken, $arrToken);
            $arr = array_merge($arr, $arrToken);
            unset($arr['resumptionToken']);
            return TRUE;
        } else {
            $this->noerror = FALSE;
            $this->oai_error('badResumptionToken', array($arr['resumptionToken']));
            return FALSE;
        }
    }

//####################################################################################
//### end functions ##################################################################
//####################################################################################
//########################################################################################
}

?>
