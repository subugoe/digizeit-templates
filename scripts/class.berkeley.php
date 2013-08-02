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
class berkeley {
    static public $scriptPath = '.';
    static public $dbaFile = '/structcache.db4';

    static public $timeLimit = 0;
    static public $memoryLimit = '1024M';

    static $debug = false;


    function init() {
        self::$scriptPath = dirname(__FILE__);
        self::$dbaFile =  realpath(self::$scriptPath.'/../../uploads/tx_goobit3').self::$dbaFile;

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

    function openDba($file=false,$modus='c',$handler='db4') {
        if(!$file) {
            $file = self::$dbaFile;
        }
        return @dba_open($file,$modus,$handler);
    }

    function closeDba($dba) {
      dba_close($dba);
    }

    function getDbaEntry($id,$mtime=false,$serialized=true,$file=false,$modus='c',$handler='db4') {
        $dba = self::openDba($file,$modus,$handler);
        if(!$dba) {
            return false;
        }
         if(dba_exists($id,$dba)) {
            if($serialized) {
                $val = unserialize(dba_fetch($id,$dba)); 
            } else {
                $val = dba_fetch($id,$dba); 
            }
           if(!$val) {
                self::closeDba($dba);
                return false;
            }
            if($mtime && $val['mtime']<$mtime) {
                $val = false;
                dba_delete($id,$dba);
            }
        } else {
            self::closeDba($dba);
            return false;
        }
        self::closeDba($dba);
        return $val;
    }

    function updDbaEntry($id,$val,$serialized=true,$file=false,$modus='c',$handler='db4') {
        if(!$val) {
            return false;
        }
        $dba = self::openDba($file,$modus,$handler);
        if(!$dba) {
            return false;
        }        
        if($serialized) {
            $val = serialize($val);
        }
        if(dba_exists($id,$dba)) {
            dba_delete($id,$dba);
        }
        if(dba_insert($id,$val,$dba)) {
            dba_sync($dba);
            self::closeDba($dba);
            return true;
        } else {
            self::closeDba($dba);
            return false;    
        }
    }



}
?>
