<?php
class user_Md5login {
    var $cObj;// The backReference to the mother cObj object set at call time
    /**
    * Call it from a USER cObject with 'userFunc = user_randomImage->main_randomImage'
    */
    function main($content,$conf){
        $arrGP = array_merge($GLOBALS['_GET'],$GLOBALS['_POST']);
        if(!isset($GLOBALS['TSFE']->lang)) $lang = 'de';
        else $lang = $GLOBALS['TSFE']->lang;
        //kein Frontenduser angemeldet
        if(!$GLOBALS['TSFE']->fe_user->user || $arrGP['logintype']=='logout') {
            $js = 'function superchallenge_pass(form) {
                    var pass = form.real_pass.value;
                    if (pass) {
                        var enc_pass = MD5(pass);
                        var str = form.user.value+":"+enc_pass+":"+form.challenge.value;
                        form.pass.value = MD5(str);
                        return true;
                    } else {
                        return false;
                    }
                }';
            $GLOBALS['TSFE']->JSCode .= $js;
            $GLOBALS['TSFE']->additionalHeaderData['JSmd5'] = '<script language="JavaScript" type="text/javascript" src="typo3/md5.js"></script>';
            //generate challange
            $chal_val = md5(time().getmypid());
            $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kbmd5fepw_challenge', array('challenge' => $chal_val, 'tstamp' => time()));

            $content = '
            <form action="'.str_replace('http://','https://',strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'))).'" name="login" enctype="multipart/form-data" method="post" onSubmit="superchallenge_pass(this); return true;">
                <p id="introtxt">Login</p>
                <table id="logintable">
                    <tr>
                        <td align="right"><label for="user">'.$conf['item_username.'][$lang].':&nbsp;</label></td>
                        <td><input name="user" id="user" type="text" value=""  /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td align="right"><label for="Pass">'.$conf['item_password.'][$lang].':&nbsp;</label></td>
                        <td><input type="password" id="real_pass" name ="real_pass" value="" /></td>
                        <td><input type="submit" id="loginsubmit" value="login" /></td>
                    </tr>
                </table>
                <input type="hidden" name="pass" value="">
                <input type="hidden" name="logintype" value="login" />
                <input type="hidden" name="pid" value="'.$conf['storagePid'].'" />
                <input type="hidden" name="redirect_url" value="" />
                <input type="hidden" name="challenge" value="'.$chal_val.'">
            </form>';
//<input type="hidden" name="redirect_url" value="http://'.t3lib_div::getThisUrl().t3lib_div::linkThisScript().'" />

        } else {
            if(strpos(strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')),'id=139') || strpos(strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')),'mydigizeit')) {
                $action = 'https://www.digizeitschriften.de/';
            } else {
                $action = str_replace('http://','https://',strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')));
            }
            $content = '
            <form action="'.$action.'" name="login" method="post"">
                <p id="introtxt">Loginstatus:</p>
                <table id="logintable">
                    <tr>
                        <td colspan="3"><div id="loginstatus" ><a href="mydigizeit" title="'.$GLOBALS['TSFE']->fe_user->user['name'].'"><nobr>'.$GLOBALS['TSFE']->fe_user->user['name'].'</nobr><a></div></td>
                    </tr>
                    <tr>
                        <td width="157">&nbsp;</td>
                        <td id="logout"><input type="submit" id="loginsubmit" value="logout" /></td>
                    </tr>
                </table>
                <input type="hidden" name="logintype" value="logout" />
                <input type="hidden" name="pid" value="'.$conf['storagePid'].'" />
                <input type="hidden" name="redirect_url" value="" />
                </form>';
        }
        return $content;
    }
}
?>
