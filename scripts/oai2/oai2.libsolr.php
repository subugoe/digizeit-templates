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

	
class oailib {
	//####################################################################################
	//### DATA functions #################################################################
	//####################################################################################

	function getMetadataFormats($identifier) {
		$arrFormats = array();
		$arrArgs['verb'] = 'getRecord';
		$arrArgs['identifier'] = $identifier;
		foreach($this->conf['metadataFormats'] as $key=>$val) {
			$arrArgs['metadataPrefix'] = $key;
			$arrResult = array();
			oailib::getRecords($arrArgs,$arrResult);
			if($arrResult['hits']) array_push($arrFormats,$key);
		}
		if(!count($arrFormats)) {
			$arrFormats = FALSE;
			$this->oai_error('idDoesNotExist',array($identifier));
			$this->oai_error('badArgument',array('identifier'=>$identifier));
		}
		return $arrFormats;
	}



	function getRecords(&$arr,&$arrResult) {
		$arrResult = array();
        $direction = true;
        $arr['maxresults'] = $this->conf[$arr['metadataPrefix'].':'.$arr['verb'].':max']['results'];

        if($arr['metadataPrefix']) {
            $mPrefix = $arr['metadataPrefix'];
        } else {
            $mPrefix = 'oai_dc';
        }

		if(!isset($arr['start'])) {
            $arr['start'] = 0;
		} else {
            $arr['start'] = $arr['start']+$arr['maxresults'];
        }
		$arrResult['header'] = array();
		$arrResult['records'] = array();

		if(isset($arr['identifier'])) {
            $identifier = str_replace($this->conf['oai-identifier']['scheme'].$this->conf['oai-identifier']['delimiter'].$this->conf['oai-identifier']['repositoryIdentifier'].$this->conf['oai-identifier']['delimiter'],'',trim($arr['identifier']));
			$addWhere = ' (PPN:'.oailucene::escStr($identifier).')';

            $arr['rows'] = 1;

            // try idWork / DMDID
            $_res = oailucene::query($this->conf['query'][$mPrefix].$addWhere,$this->conf['DB']['datefield'],$direction,$arr);
            if(!oailucene::num_rows($_res)) {
                $arrIdentifier = explode('/',$identifier);
                $dmdid = array_pop($arrIdentifier);
                $identifier = implode('/',$arrIdentifier);
	        	$addWhere = ' IDPARENTDOC:'.oailucene::escStr($identifier).' DMDID:'.oailucene::escStr($dmdid);
            }

		} else {
            $addWhere = '';
			if(isset($arr['from']) || isset($arr['until'])) {
				if(!isset($arr['from'])) {
                    $from = '1970-01-01';
                    $direction = true;
                } else {
                    $from = $arr['from'];
                    $direction = false;
                }
				if(!isset($arr['until'])) {
                    $until = '9999-12-31';
                } else {
                    $until = $arr['until'];
                    $direction = false;
                }
				$addWhere .= ' ('.$this->conf['DB']['datefield'].':['.str_replace('-','',$from).' TO '.str_replace('-','',$until).'])';
			}

			if(isset($arr['set'])) {
                if(isset($this->conf['setqueries'][trim($arr['set'])])) {
				    $addWhere .= ' '.$this->conf['setqueries'][trim($arr['set'])];
                } else {
                    $arrTmp = explode('_',trim($arr['set']));
                    for($i=1;$i<count($arrTmp);$i=$i+2) {
				        $addWhere .= ' ('.$arrTmp[$i-1].':'.$arrTmp[$i].')';
                    }
                    unset($arrTmp);
                }
			}
		}
		
        if(isset($this->conf['HIDECOLLECTIONS'])) {
            $addWhere .= ''; 
            foreach($this->conf['HIDECOLLECTIONS'] as $dc) {
				$addWhere .= ' NOT(DC:'.$dc.')';
            }
            $addWhere .= ''; 
        }
        
		$noerror = TRUE;

        $res = oailucene::query($this->conf['query'][$mPrefix].$addWhere,$this->conf['DB']['datefield'],$direction,$arr);

        $arrResult['hits'] = oailucene::num_rows($res);

        if(!$arrResult['hits']) {
            if($arr['verb']=='GetRecord') {
                $this->oai_error('idDoesNotExist',array($identifier));
                $this->oai_error('badArgument',array('identifier'=>$identifier));
                $noerror = FALSE;
            } else {
                $noerror = FALSE;
            }
            return $noerror;
        }


        for($i=0;$i<min($arrResult['hits'],$arr['maxresults']);$i++) {
            if(!($arrData = oailucene::fetch_assoc($res))) break;

            if(trim($arrData['PPN'])) {
                $arrResult['header'][$i]['identifier'] = $this->conf['oai-identifier']['scheme'].$this->conf['oai-identifier']['delimiter'].$this->conf['oai-identifier']['repositoryIdentifier'].$this->conf['oai-identifier']['delimiter'].trim($arrData['PPN']);
            } else {
                $arrResult['header'][$i]['identifier'] = $this->conf['oai-identifier']['scheme'].$this->conf['oai-identifier']['delimiter'].$this->conf['oai-identifier']['repositoryIdentifier'].$this->conf['oai-identifier']['delimiter'].trim($arrData['STRUCTRUN'][1]['PPN'].'/'.$arrData['STRUCTRUN'][count($arrData['STRUCTRUN'])-1]['DMDID']);
            }
            if(trim($arrData[$this->conf['DB']['datefield']])) {
                $arrResult['header'][$i]['datestamp'] = trim(substr($arrData[$this->conf['DB']['datefield']],0,4).'-'.substr($arrData[$this->conf['DB']['datefield']],4,2).'-'.substr($arrData[$this->conf['DB']['datefield']],6,2));
            } else {
                $arrResult['header'][$i]['datestamp'] = $this->conf['MAIN']['datestamp'];
            }

            $arrResult['header'][$i]['datestamp'] = trim(substr($arrData[$this->conf['DB']['datefield']],0,4).'-'.substr($arrData[$this->conf['DB']['datefield']],4,2).'-'.substr($arrData[$this->conf['DB']['datefield']],6,2));

            $arrSetSpec = explode(' ',strtolower($arrData['DC']));
            foreach($arrSetSpec as $setSpec) {
                $setSpec = trim($setSpec);
                if($setSpec) {
                    if($this->conf['sets']['DC_'.$setSpec]) {
                        $arrResult['header'][$i]['setSpec'][] = 'DC_'.$setSpec;
                        if($arrData['ACL']['free']) {
                            $arrResult['header'][$i]['setSpec'][] = 'DC_'.$setSpec.'_ACL_free';
                        } else if($arrData['ACL']['Gesamtabo']) {
                            $arrResult['header'][$i]['setSpec'][] = 'DC_'.$setSpec.'_ACL_gesamtabo';
                        }
                    }
                }
            }

            if($arr['verb']=='ListRecords' || $arr['verb']=='GetRecord') {
                switch($arr['metadataPrefix']) {
                    case 'oai_dc':
                        if(trim($arrData['IDPARENTDOC'])) {
                            $arrResult['metadata'][$i]['dc:relation'][0] = oailib::getRelation($arrData);
                        }
						$arrResult['metadata'][$i]['dc:title'][0] = trim(trim($arrData['TITLE'])).' '.trim($arrData['CURRENTNO']);
						$arrResult['metadata'][$i]['dc:creator'] = explode(';',$arrData['CREATOR']);
						$arrResult['metadata'][$i]['dc:subject'] = explode(' ',$arrData['DC']);
//						$arrResult['metadata'][$i]['dc:description'][0] = 
						$arrResult['metadata'][$i]['dc:publisher'][0] = trim($arrData['PUBLISHER']);
//						$arrResult['metadata'][$i]['dc:subject'] = array();
						$arrResult['metadata'][$i]['dc:date'][0] = trim($arrData['YEARPUBLISH']);
						$arrResult['metadata'][$i]['dc:type'][0] = $this->conf['oai_dc:docstrct'][$arrData['DOCSTRCT']];
						$arrResult['metadata'][$i]['dc:type'][1] = $this->conf['oai_dc:default']['dc:type'];
						$arrResult['metadata'][$i]['dc:format'][0] = 'image/jpeg';
						$arrResult['metadata'][$i]['dc:format'][1] = 'application/pdf';

                        if(trim($arrData['PPN'])) {
                            $arrResult['metadata'][$i]['dc:identifier'][0] = $this->conf['MAIN']['resolver'].trim($arrData['PPN']);
                            if(count($arrData['STRUCTRUN'])>=2) {
                                $arrResult['metadata'][$i]['dc:identifier'][1] = $this->conf['MAIN']['resolver'].trim($arrData['STRUCTRUN'][1]['PPN'].'/'.$arrData['STRUCTRUN'][count($arrData['STRUCTRUN'])-1]['DMDID']);
                                $arrResult['metadata'][$i]['dc:identifier'][2] = 'DigiZeit: '.trim($arrData['STRUCTRUN'][1]['PPN'].'/'.$arrData['STRUCTRUN'][count($arrData['STRUCTRUN'])-1]['DMDID']);
                                }
                        } else {
                            if(count($arrData['STRUCTRUN'])>=2) {
                                $arrResult['metadata'][$i]['dc:identifier'][0] = $this->conf['MAIN']['resolver'].trim($arrData['STRUCTRUN'][1]['PPN'].'/'.$arrData['STRUCTRUN'][count($arrData['STRUCTRUN'])-1]['DMDID']);
                                $arrResult['metadata'][$i]['dc:identifier'][1] = 'DigiZeit: '.trim($arrData['STRUCTRUN'][1]['PPN'].'/'.$arrData['STRUCTRUN'][count($arrData['STRUCTRUN'])-1]['DMDID']);
                            }
                        }

                        foreach($this->conf['oai_dc:identifier'] as $key=>$val) {
                            if(trim($arrData[$key])) {
                                array_push($arrResult['metadata'][$i]['dc:identifier'], trim($val).': '.trim($arrData[$key]));
                            }
                        }

                        //Zeitschriftenband
                        // dc:source Publisher: Titel. Ort Erscheinungsjahr.
                        if(count($arrData['STRUCTRUN'])==2) {
                            $arrResult['metadata'][$i]['dc:source'][0] = trim(trim($arrData['PUBLISHER']).': '.trim($arrData['TITLE']).'. '.trim($arrData['PLACEPUBLISH']).' '.trim($arrData['YEARPUBLISH']));
                        } else if(count($arrData['STRUCTRUN'])>=2) {
                            // dc:source Autor: Zeitschrift. Band Erscheinungsjahr.
                            $arrResult['metadata'][$i]['dc:source'][0] = trim(trim($arrData['CREATOR']).': '.trim($arrData['STRUCTRUN'][1]['TITLE']).'. '.trim($arrData['STRUCTRUN'][1]['CURRENTNO']).' '.trim($arrData['STRUCTRUN'][1]['YEARPUBLISH']));
                        }

                        if($arrData['ACL']) {
                            $arrRights = oailucene::_unserialize($arrData['ACL']);
                            foreach($arrRights as $val) {
                                if($this->conf['oai_dc:rights'][$val]) {
                                    $arrResult['metadata'][$i]['dc:rights'][] = trim($this->conf['oai_dc:rights'][$val]);
                                } else {
                                    $arrResult['metadata'][$i]['dc:rights'][] = trim($val);
                                }
                            }
                        }
                    break;
                    case 'mets':
						$arrResult['metadata'][$i]['mets:mets']= file_get_contents($this->conf['MAIN']['metsresolver'].trim($arrData['PPN']));
                    break;
				}
            }
		}


		//new ResumtionToken ?
		if($arr['verb']=='ListRecords' || $arr['verb']=='ListIdentifiers') {
			if(($arrResult['hits']-$arr['start'])>$arr['maxresults']) {
				$arrResult['token'] = 'oai_'.md5(uniqid(rand(), true));
				$strToken = '';
				//allowed keys
				$arrAllowed = array('from','until','metadataPrefix','set','resumptionToken','start');
				foreach($arr as $key=>$val) {
					if(in_array($key,$arrAllowed)) {
					    $strToken .= $key.'='.$val.'&';
				    }
				}
				$strToken .= 'hits='.$arrResult['hits'];
				$fp = fopen($this->conf['MAIN']['tmpDir'].$arrResult['token'],'w');
				fwrite($fp,$strToken);
				fclose($fp);
			} else {
                unset($arrResult['token']);
            }
		}
		return;


	}

	function getDatestamp() {

        $res = oailucene::query($this->conf['DB']['datefield'].':{00000000 TO 99999999}',$this->conf['DB']['datefield'],false,array('rows'=>1));
        $arr = oailucene::fetch_assoc($res);

        $arr[$this->conf['DB']['datefield']] = substr($arr[$this->conf['DB']['datefield']],0,4).'-'.substr($arr[$this->conf['DB']['datefield']],4,2).'-'.substr($arr[$this->conf['DB']['datefield']],6,2);
 
       return $arr[$this->conf['DB']['datefield']];
	}

    function getRelation(&$arr) {
        $res = oailucene::query('IDDOC:'.trim($arr['IDPARENTDOC']),array('rows'=>1));
        $strReturn = '';
        if(oailucene::num_rows($res)) {
            $arrRes = oailucene::fetch_assoc($res);
            if(trim($arrRes['CREATOR'])) $strReturn .= trim($arrRes['CREATOR']).': ';
            if(trim($arrRes['TITLE'])) $strReturn .= trim($arrRes['TITLE']).'. ';
            if(trim($arrRes['PLACEPUBLISH'])) $strReturn .= trim($arrRes['PLACEPUBLISH']).' ';
            if(trim($arrRes['YEARPUBLISH'])) $strReturn .= trim($arrRes['YEARPUBLISH']);

            if(!trim($arr['CREATOR'])) $arr['CREATOR'] = $arrRes['CREATOR'];
            if(!trim($arr['TITLE'])) $arr['TITLE'] = $arrRes['TITLE'];
            if(!trim($arr['PLACEPUBLISH'])) $arr['PLACEPUBLISH'] = $arrRes['PLACEPUBLISH'];
            if(!trim($arr['YEARPUBLISH'])) $arr['YEARPUBLISH'] = $arrRes['YEARPUBLISH'];
        }
        return trim($strReturn);
    }

	//####################################################################################
	//### end DATA functions #############################################################
	//####################################################################################

}
