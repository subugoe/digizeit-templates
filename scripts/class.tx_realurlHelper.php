<?php
// wird in typo3conf/realurl_conf.php verwendet
class tx_drwiki_realurlHelper {
    // character to use instead of spaces
    var $spaceReplacement = '_';

    function main($params, $ref) {
        if ($params['decodeAlias']) {
            return $this->alias2id($params['value']);
        } else {
            return $this->id2alias($params['value']);
        }
    }

    function id2alias($value) {
        return str_replace(' ', $this->spaceReplacement, $value);
    }

    function alias2id($value) {
        return str_replace($this->spaceReplacement , ' ', $value);
    }
}
?>
