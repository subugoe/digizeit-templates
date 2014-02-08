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
// tomcat

class oailucene {

        
	function escStr($str) {
		return str_replace(array('\\','+','-','&&','||','!','(',')','{','}','[',']','^','"','~','*','?',':'),array('\\\\','\+','\-','\&&','\||','\!','\(','\)','\{','\}','\[','\]','\^','\"','\~','\*','\?','\:'),$str);
	}

    //PPN:(+urn +nbn +de +bvb +12-bsb00076345-2)
    function escPPN($ppn) {
        return oailucene::escStr($ppn);
    }

    function num_rows($res) {
        return $res['response']['numFound'];
    }

	function query($query,$sort=DEFAULTORDERFIELD,$reverse=false,$arr=array()) {
       $urlQuery = '&q='.urlencode(trim($query));
        if(isset($arr['maxresults'])) {
            $urlQuery .= '&rows='.$arr['maxresults'];
        }
        if(isset($arr['start'])) {
            $urlQuery .= '&start='.$arr['start'];
        }
        if($reverse) {
            $sort .= ' desc';
        } else {
            $sort .= ' asc';
        }
        if($sort) {
            $urlQuery .= '&sort='.urlencode(trim($sort));
        }
        $solrResult = file_get_contents($this->conf['DB']['solrPhpsUrl'].$urlQuery);
        $arrSolr = unserialize($solrResult);
        foreach($arrSolr['response']['docs'] as $key=>$val) {
            foreach($val as $field=>$_val) {
                if(is_array($_val)) {
                    $arrSolr['response']['docs'][$key][$field] = $_val[0];
                }
            } 
            $arrTmp = explode(',',$this->conf['DB']['serialized']);
            foreach($arrTmp as $field) {
                if(isset($arrSolr['response']['docs'][$key][$field])) {
                    $arrSolr['response']['docs'][$key][$field] = oailucene::_unserialize($arrSolr['response']['docs'][$key][$field]);
                }
            }
        }
 		return $arrSolr;
    }


    
    function fetch_assoc(&$iterator) {
        if($arr=current($iterator['response']['docs'])) {
            next($iterator['response']['docs']);
            return $arr;
        }  else {
            return false;
        }
    }

    function data_seek(&$iterator, $start) {
    }

    /**
    * [Describe function...]
    * Helper function to switch from serialized fields to "jsonized" Fields in lucene index
    *
    * @param [string]  $str: serialized or jsonized string
    * @return [type]  unserialized or unjsonized
    */
    function _unserialize($str) {
        $ret = json_decode($str,true);
        if(!is_array($ret)) {
            $ret = unserialize($str);
        }
        return $ret;
    }

}
?>
