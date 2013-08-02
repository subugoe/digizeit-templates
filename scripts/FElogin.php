<?php
class user_FElogin {
    var $cObj;// The backReference to the mother cObj object set at call time
    /**
    * Call it from a USER cObject with 'userFunc = user_FElogin->main'
    */
    function main($content,$conf){
        $arrGP = array_merge($GLOBALS['_GET'],$GLOBALS['_POST']);
        if(!isset($GLOBALS['TSFE']->lang)) $lang = 'de';
        else $lang = $GLOBALS['TSFE']->lang;
        //kein Frontenduser angemeldet
        if(!$GLOBALS['TSFE']->fe_user->user || $arrGP['logintype']=='logout') {
            $content = '
            <form action="'.str_replace('http://','https://',strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'))).'" name="login" method="post" onSubmit="">
                <table id="logintable">
                    <tr>
                        <td><p id="introtxt">Login</p></td>
                        <td colspan="2" align="right"><nobr><a id="lostpass" href="'.$lang.'/login/?tx_felogin_pi1%5Bforgot%5D=1">'.$conf['item_lostpass.'][$lang].'</a></nobr></td>
                    </tr>
                    <tr>
                        <td align="right"><label for="user">'.$conf['item_username.'][$lang].':&nbsp;</label></td>
                        <td><input name="user" id="user" type="text" value=""  /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td align="right"><label for="Pass">'.$conf['item_password.'][$lang].':&nbsp;</label></td>
                        <td><input type="password" id="pass" name ="pass" value="" /></td>
                        <td><input type="submit" id="loginsubmit" value="login" /></td>
                    </tr>
                </table>
                <input type="hidden" name="logintype" value="login" />
                <input type="hidden" name="pid" value="'.$conf['storagePid'].'" />
                <input type="hidden" name="redirect_url" value="" />
            </form>
            ';
//<input type="hidden" name="redirect_url" value="http://'.t3lib_div::getThisUrl().t3lib_div::linkThisScript().'" />

        } else {
            if(strpos(strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')),'id=139') || strpos(strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')),'mydigizeit')) {
                $action = 'https://www.digizeitschriften.de/';
            } else {
                $action = str_replace('http://','https://',strtolower(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')));
            }
            $content = '
            <form action="'.$action.'" name="login" method="post">
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
