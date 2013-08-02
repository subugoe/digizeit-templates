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
class lucene {
    static public $javaBridge = 'http://localhost:8080/myJBridge_20111123/java/Java.inc';
#    static public $luceneJar = 'lucene.jar';
    static public $luceneMAXDOCS;
    static public $scriptPath = '.';
    static public $luceneStopwordFile = '/stopwords_java.txt';
    static public $luceneIndex = '/lucene';
    static public $luceneSerialize = array('STRUCTRUN','ACL');
    static public $luceneDefault = 'DEFAULT';
    static public $analyzer = 'org.apache.lucene.analysis.standard.StandardAnalyzer';
    static public $searcher;
    static public $reader;
    static public $parser;

    static public $timeLimit = 0;
    static public $memoryLimit = '1024M';

    static $g = 1073741824;
    static $m = 1048576;
    static $k = 1024;
    static $debug = true;

    static public $incLicense;
    static public $incStruct;

    function init() {
        require_once(self::$javaBridge);

        self::$scriptPath = dirname(__FILE__);
        self::$luceneStopwordFile =  self::$scriptPath.self::$luceneStopwordFile;
        self::$luceneIndex =  realpath(self::$scriptPath.'/../../../'.self::$luceneIndex);
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

        self::$searcher = new Java('org.apache.lucene.search.IndexSearcher',self::$luceneIndex);
        
        self::$luceneMAXDOCS = java_values(self::$searcher->maxDoc());
        
        self::$reader = self::$searcher->getIndexReader();

        if(is_file(self::$luceneStopwordFile)) {
            $file = new Java('java.io.File',self::$stopwordFile);
            $analyzer = new Java(self::$analyzer, $file);
        } else {
            $analyzer = new Java(self::$analyzer);
        }

        self::$parser = new Java('org.apache.lucene.queryParser.QueryParser',self::$luceneDefault, $analyzer);
//        self::$parser->setDefaultOperator(self::$parser->AND_OPERATOR);

        // no limits
        $query = new Java('org.apache.lucene.search.BooleanQuery');
        $query->setMaxClauseCount(self::$luceneMAXDOCS);

    }



    function search($query, $filter=null, $maxdocs=false, $sort=false) {
    if(!$maxdocs) {
        $maxdocs = self::$luceneMAXDOCS;
    }
    if(!$sort) {
            $sort = new Java('org.apache.lucene.search.Sort');
        }
        try {
            $ptr = self::$searcher->search($query, $filter, intval($maxdocs), $sort);
        } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }
        return $ptr;
    }

    function length($ptr) {
        if(is_object($ptr)) {
            return java_values($ptr->totalHits);
        } else {
            return false;
        }
    }

    function doc($ptr,$number=0) {
        if(is_object($ptr)) {
            return self::$searcher->doc($ptr->scoreDocs[$number]->doc);
        } else {
            return false;
        }
    }

    function sort($arrFields) {
        $jList = new java("java.util.LinkedList",$arrFields);
        $jListSize = java_values($jList->size());
        $jSortFieldList = new Java("java.util.LinkedList");
        while($jListSize--) {
            $jMap = $jList->remove();
            $jSort = new Java('org.apache.lucene.search.SortField',(string)$jMap->get('field'), $jMap->get('order'));
            $jSortFieldList[] = $jSort;
        }
        try {
            $sort = new Java('org.apache.lucene.search.Sort');
            $jSortFieldListSize = java_values($jSortFieldList->size());
    
            while($jSortFieldListSize--) {
                $sort->setSort($jSortFieldList->remove());
            }
        } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }
        return $sort;
    }

    function term($field,$text='') {
        if($text) {
            try {
                $term = new Java('org.apache.lucene.index.Term',$field,$text);
            } catch (JavaException $e) {
                if (self::$debug) {
                    print_r('Exception occured: '.$e,__FILE__,__LINE__);
                } else {
                    return false;
                }
            }
            
        } else {
            try {
                $term = new Java('org.apache.lucene.index.Term',$field);
            } catch (JavaException $e) {
                if (self::$debug) {
                    print_r('Exception occured: '.$e,__FILE__,__LINE__);
                } else {
                    return false;
                }
            }               
        }
        return $term;
    }

    function termQuery($term) {
        if(is_object($term)) {
           try {
                $termQuery = new Java('org.apache.lucene.search.TermQuery',$term);
            } catch (JavaException $e) {
                if (self::$debug) {
                    print_r('Exception occured: '.$e,__FILE__,__LINE__);
                } else {
                    return false;
                }
            }               
        } else {
            return false;   
        }
        return $termQuery;
    }

    function parseQuery($strQuery) {
        if(is_string($strQuery)) {
            try {
                $termQuery = self::$parser->parse($strQuery);
            } catch (JavaException $e) {
                if (self::$debug) {
                    print_r('Exception occured: '.$e,__FILE__,__LINE__);
                } else {
                    return false;
                }
            }
        } else {
            return false;   
        }
        return $termQuery;
    }

    function rangeQuery($lowerTerm,$upperTerm,$inclusive=true) {
        try {
            $rangeQuery = new Java('org.apache.lucene.search.RangeQuery',$lowerTerm,$upperTerm,$inclusive);
        } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }
        return $rangeQuery;
    }

    function booleanQuery($arrTerm, $OCCUR='SHOULD') {
        try {
            $occur = new JavaClass('org.apache.lucene.search.BooleanClause$Occur');
       } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }               
        try {               
            $booleanQuery = new Java('org.apache.lucene.search.BooleanQuery');
            if(java_values($booleanQuery->getMaxClauseCount())<count($arrTerm)) {
                $booleanQuery->setMaxClauseCount(count($arrTerm));
            }
        } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }                           
        if(is_array($arrTerm)) {
            foreach($arrTerm as $term) {
               if(is_object($term)) {
                    $termQuery = self::termQuery($term);
                    try {
                        $booleanQuery->add($termQuery, $occur->$OCCUR);
                    } catch (JavaException $e) {
                        if (self::$debug) {
                            print_r('Exception occured: '.$e,__FILE__,__LINE__);
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        return $booleanQuery;
    }

    function addQuery($query, $booleanQuery=false, $OCCUR='SHOULD') {
        if(!is_object($booleanQuery)) {
                try {
                $booleanQuery = new Java('org.apache.lucene.search.BooleanQuery');
            } catch (JavaException $e) {
                if (self::$debug) {
                    print_r('Exception occured: '.$e,__FILE__,__LINE__);
                } else {
                    return false;
                }
            }
        }
        try {
            $occur = new Java('org.apache.lucene.search.BooleanClause$Occur');
        } catch (JavaException $e) {
            if (self::$debug) {
                print_r('Exception occured: '.$e,__FILE__,__LINE__);
            } else {
                return false;
            }
        }
        if(is_object($query)) {
            $booleanQuery->add($query, $occur->$OCCUR);
            return $booleanQuery;
        }  else {
            return false;
        }
    }

    function getResults($start, $limit, $ptr, $field=false) {
        $jResultList = new Java("java.util.LinkedList");
        $jStrField = new Java('java.lang.String');
        for($i = $start; $i < $start+$limit; $i++) {
            $jNextDoc = lucene::doc($ptr,$i);
            $jHash = new Java('java.util.HashMap');
            if(is_string($field)) {
                $jStrField = $field;
                $jField = $jNextDoc->getField($jStrField);
                $jHash[$jField->name()] = $jField->stringValue();
            } else if(is_array($field)) {
                foreach($field as $f) {
                    $jStrField = $f;
                    $jField = $jNextDoc->getField($jStrField);
                    $jHash[$jField->name()] = $jField->stringValue();
                }
            } else {
                $jFieldList = $jNextDoc->getFields();
                $jFieldListIter = $jFieldList->iterator();
                $jFieldListSize = java_values($jFieldList->size());
                while($jFieldListSize--) {
                    $jNextField = $jFieldListIter->next();
                    $jHash[$jNextField->name()] = $jNextField->stringValue();
                }
            }
            $jResultList[] = $jHash;            
        }
        $arrResult = java_values($jResultList);
        foreach($arrResult as $key=>$val) {
            foreach(self::$luceneSerialize as $field) {
                if($val[$field]) {
                    $arrResult[$key][$field] = self::_unserialize($val[$field]);
                }
            }
        }
        return $arrResult;
    }

    function getResult($doc) {
        $jHash = new Java('java.util.HashMap');
        $jFieldList = $doc->getFields();
        $jFieldListIter = $jFieldList->iterator();
        $jFieldListSize = java_values($jFieldList->size());
        while($jFieldListSize--) {
            $jNextField = $jFieldListIter->next();
            $jHash[$jNextField->name()] = $jNextField->stringValue();            
        }
        $arrResult = java_values($jHash);
        foreach(self::$luceneSerialize as $field) {
            if($arrResult[$field]) {
                $arrResult[$field] = self::_unserialize($arrResult[$field]);
            }
        }
        return $arrResult;
    }


    function getFieldList($field) {
        $jFieldList = new Java('java.util.LinkedList');
        $jTerm = new Java('org.apache.lucene.index.Term', $field, '');
        $jEnum = self::$reader->terms($jTerm);
        $jterm = $jEnum->term();
        while(java_values($jEnum->next())) {
            if(java_values($jterm->field()) == $field) {
                $jFieldList[] = $jterm->text();
                $jterm = $jEnum->term();
            } else {
                break;
            }
        }
        return java_values($jFieldList);
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
