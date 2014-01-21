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

//#### FUNCTIONS #############################################

function getWork() {
    global $global;
    $divList = $global['xpath']->evaluate('.//mets:div[@TYPE="physSequence"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="PHYSICAL"]')->item(0));
    if ($divList->length) {
        if ($divList->item(0)->hasAttribute('ID')) {
            $global['arrWork']['physID'] = trim($divList->item(0)->getAttribute('ID'));
        }
        $global['arrWork']['logID'] = getLog2Dmd($global['arrWork']['dmdID']);
    }
    return;
}

function getAuthor2Dmd($dmdID) {
    global $global;
    $author = '';
    $modsList = $global['xpath']->evaluate('/mets:mets/mets:dmdSec[@ID="' . $dmdID . '"]/mets:mdWrap[@MDTYPE="MODS"]');
    if ($modsList->length) {
        foreach ($global['authorQuery'] as $query) {
            $author = $global['xpath']->evaluate($query, $modsList->item(0));
            $author = trim($author);
            if ($author) {
                if (substr($author, -1) == ';') {
                    return(substr($author, 0, -1));
                } else {
                    return $author;
                }
            }
        }
    }
    return false;
}

function getLog2Dmd($dmdID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('.//mets:div[@DMDID="' . $dmdID . '"]/@ID', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
    if ($nodeList->length) {
        return trim($nodeList->item(0)->nodeValue);
    } else {
        return false;
    }
}

function getDmd2Log($logID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . $logID . '"]/@DMDID', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
    if ($nodeList->length) {
        return trim($nodeList->item(0)->nodeValue);
    } else {
        return false;
    }
}

function getPhys2Log($logID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('/mets:mets/mets:structLink/mets:smLink[@xlink:from="' . $logID . '"]/@xlink:to');
    if ($nodeList->length) {
        $arrPhys = array();
        foreach ($nodeList as $node) {
            $_nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . trim($node->nodeValue) . '"][@TYPE="page"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="PHYSICAL"]')->item(0));
            if ($_nodeList->length) {
                if ($_nodeList->item(0)->hasAttribute('ORDER')) {
                    $arrPhys[trim($_nodeList->item(0)->getAttribute('ORDER'))]['physID'] = trim($node->nodeValue);
                    $arrPhys[trim($_nodeList->item(0)->getAttribute('ORDER'))]['imgnumber'] = trim($_nodeList->item(0)->getAttribute('ORDER'));
                }
                if ($_nodeList->item(0)->hasAttribute('ORDERLABEL')) {
                    $arrPhys[trim($_nodeList->item(0)->getAttribute('ORDER'))]['page'] = trim($_nodeList->item(0)->getAttribute('ORDERLABEL'));
                } else {
                    $arrPhys[trim($_nodeList->item(0)->getAttribute('ORDER'))]['page'] = '-';
                }
                $__nodeList = $global['xpath']->evaluate('mets:fptr/@FILEID', $_nodeList->item(0));
                if ($__nodeList->length) {
                    foreach ($__nodeList as $__node) {
                        $fileList = $global['xpath']->evaluate('/mets:mets/mets:fileSec/mets:fileGrp[@USE="' . $global['strFileGrp'] . '"]/mets:file[@ID="' . trim($__node->nodeValue) . '"]/mets:FLocat/@xlink:href');
                        if ($fileList->length) {
                            $arrPhys[trim($_nodeList->item(0)->getAttribute('ORDER'))]['image'] = getImageInfo(trim($fileList->item(0)->nodeValue));
                            break;
                        }
                    }
                }
            }
        }
        return $arrPhys;
    } else {
        return false;
    }
}

function getPage($logID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('/mets:mets/mets:structLink/mets:smLink[@xlink:from="' . $logID . '"]/@xlink:to');

    if ($nodeList->length) {
        $arrPage['imgnumber'] = false;
        $arrPage['page'] = '-';
        foreach ($nodeList as $node) {
            $_nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . trim($node->nodeValue) . '"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="PHYSICAL"]')->item(0));
            if ($_nodeList->length) {
                if ($_nodeList->item(0)->hasAttribute('ORDER')) {
                    if ($arrPage['imgnumber']) {
                        $arrPage['imgnumber'] = min($arrPage['imgnumber'], trim($_nodeList->item(0)->getAttribute('ORDER')));
                    } else {
                        $arrPage['imgnumber'] = trim($_nodeList->item(0)->getAttribute('ORDER'));
                    }
                    if ($_nodeList->item(0)->hasAttribute('ORDERLABEL')) {
                        if ($arrPage['imgnumber'] == trim($_nodeList->item(0)->getAttribute('ORDER'))) {
                            $arrPage['page'] = trim($_nodeList->item(0)->getAttribute('ORDERLABEL'));
                        }
                    }
                }
            }
        }
        return $arrPage;
    } else {
        return false;
    }
}

function getLabelFromDmd($dmdID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('/mets:mets/mets:dmdSec[@ID="' . $dmdID . '"]/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:titleInfo/mods:title');
    if ($nodeList->length && trim($nodeList->item(0)->nodeValue)) {
        return trim($nodeList->item(0)->nodeValue);
    } else {
        $nodeList = $global['xpath']->evaluate('.//mets:div[@DMDID="' . $dmdID . '"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
        if ($nodeList->length) {
            if ($nodeList->item(0)->hasAttribute('LABEL') && trim($nodeList->item(0)->getAttribute('LABEL'))) {
                return trim($nodeList->item(0)->getAttribute('LABEL'));
            } else if ($nodeList->item(0)->hasAttribute('TYPE') && trim($nodeList->item(0)->getAttribute('TYPE'))) {
                return trim($nodeList->item(0)->getAttribute('TYPE'));
            }
        }
    }
    return '---';
}

function getLabelFromLog($logID) {
    global $global;
    $nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . $logID . '"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
    if ($nodeList->length) {
        if ($nodeList->item(0)->hasAttribute('LABEL') && trim($nodeList->item(0)->getAttribute('LABEL'))) {
            return trim($nodeList->item(0)->getAttribute('LABEL'));
        } else if ($nodeList->item(0)->hasAttribute('TYPE') && trim($nodeList->item(0)->getAttribute('TYPE'))) {
            return trim($nodeList->item(0)->getAttribute('TYPE'));
        }
    }
    return '---';
}

function getParents($logID) {
    global $global;
    $arrBM = array();
    //get all parents up to Work
    $arrTmp = array();
    $i = 0;
    while (1) {
        $arrTmp[$i]['logID'] = $logID;
        $arrTmp[$i]['dmdID'] = getDmd2Log($logID);
        // get images and imageinfos from from current 
        if ($i == 0) {
            $arrTmp[$i]['physID'] = getPhys2Log($logID);
        }
        //title
        if ($arrTmp[$i]['dmdID']) {
            $arrTmp[$i]['title'] = getLabelFromDmd($arrTmp[$i]['dmdID']);
            $arrTmp[$i]['author'] = getAuthor2dmd($arrTmp[$i]['dmdID']);
        } else if ($arrTmp[$i]['logID']) {
            $arrTmp[$i]['title'] = getLabelFromLog($arrTmp[$i]['logID']);
        } else {
            $arrTmp[$i]['title'] = '---';
        }
        if ($logID == $global['arrWork']['logID']) {
            break;
        }
        $nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . $logID . '"]', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));

        if ($nodeList->length) {
            if ($nodeList->item(0)->parentNode && $nodeList->item(0)->parentNode->hasAttribute('ID') && trim($nodeList->item(0)->parentNode->getAttribute('ID'))) {

                $logID = trim($nodeList->item(0)->parentNode->getAttribute('ID'));
                $i++;
            } else {
                break;
            }
        } else {
            break;
        }
    }
    $arrBM = array_reverse($arrTmp);
    foreach ($arrBM as $key => $val) {
        $arrBM[$key]['depth'] = $key;
    }
    unset($arrTmp);
    return $arrBM;
}

function getChilds(&$depth, $current, $logID = false) {
    global $global;
    $nodeList = $global['xpath']->evaluate('.//mets:div[@ID="' . $logID . '"]/mets:div', $global['xpath']->evaluate('/mets:mets/mets:structMap[@TYPE="LOGICAL"]')->item(0));
    if ($nodeList->length) {
        $depth++;
        foreach ($nodeList as $node) {
            $arrTmp = array();
            $arrPage = array();
            if ($node->hasAttribute('ID') && trim($node->getAttribute('ID'))) {
                $arrPage = getPage(trim($node->getAttribute('ID')));
                $arrTmp['logID'] = trim($node->getAttribute('ID'));
                if ($node->hasAttribute('DMDID') && trim($node->getAttribute('DMDID'))) {
                    $arrTmp['dmdID'] = trim($node->getAttribute('DMDID'));
                }
                //title
                if ($arrTmp['dmdID']) {
                    $arrTmp['title'] = getLabelFromDmd($arrTmp['dmdID']);
                } else if ($arrTmp['logID']) {
                    $arrTmp['title'] = getLabelFromLog($arrTmp['logID']);
                } else {
                    $arrTmp['title'] = '---';
                }
                $arrTmp['depth'] = $depth;
                $global['arrItext'][$current]['physID'][$arrPage['imgnumber']]['logID'][] = $arrTmp;
                getChilds($depth, $current, $arrTmp['logID']);
            }
        }
        $depth--;
    }
}

function getImageInfo($img) {
    global $global;

    $arrAttr = array();
    $arrAttr['url'] = $img;

    $margin = 0;
    $maxwidth = $global['pageX'] - 2 * $global['marginImage'];
    $maxheight = $global['pageY'] - 2 * $global['marginImage'];

    //exif_read_data kann nur lokale Dateien ohne wrapper (scheme) 
    $arrTmp = parse_url($arrAttr['url']);
    if (!($arrTmp['host'] || $arrTmp['port'] || $arrTmp['user'] || $arrTmp['pass'] || $arrTmp['query'] || $arrTmp['fragment'])) {
        if (is_file($arrTmp['path']) && filesize($arrTmp['path'])) {
            $imgHeader = exif_read_data($arrTmp['path'], 'IFD0', true, false);
        } else {
            $arrAttr['url'] = $global['errorImage'];
            //exif_read_data kann nur lokale Dateien ohne wrapper (scheme) 
            unset($arrTmp);
            $arrTmp = parse_url($global['errorImage']);
            if (!($arrTmp['host'] || $arrTmp['port'] || $arrTmp['user'] || $arrTmp['pass'] || $arrTmp['query'] || $arrTmp['fragment'])) {
                $imgHeader = exif_read_data($arrTmp['path'], 'IFD0', true, false);
            }
        }
    }

    // Komprimierung von mehreren Aufloesungen wird von iText in TIFF nicht unterstuetzt       
    if (is_array($imgHeader['IFD0']['StripOffsets']) && count($imgHeader['IFD0']['StripOffsets']) > 1) {
        exec($global['convert'] . ' ' . $arrTmp['path'] . ' ' . str_replace(array('.tif', '.TIF'), '.jpg', $arrTmp['path']));
        $arrAttr['url'] = str_replace(array('.tif', '.TIF'), '.jpg', $arrAttr['url']);
    }

    // Rotation noetig?                  
    if (is_array($imgHeader['IFD0']) && array_key_exists('Orientation', $imgHeader['IFD0'])) {
        switch ($imgHeader['IFD0']['Orientation']) {
            case 1:
                //$arrAttr['rotation'] = 0;
                //$flip = false;
                break;
            case 2:
                //$arrAttr['rotation'] = 0;
                //$flip = true;
                break;
            case 3:
                $arrAttr['rotation'] = M_PI; // 180
                //$flip = false;
                break;
            case 4:
                $arrAttr['rotation'] = M_PI; // 180
                //$flip = true;
                break;
            case 5:
                $arrAttr['rotation'] = 3 * M_PI / 2; // 270
                //$flip = true;
                break;
            case 6:
                $arrAttr['rotation'] = 3 * M_PI / 2; // 270
                //$flip = false;
                break;
            case 7:
                $arrAttr['rotation'] = M_PI / 2; // 90
                //$flip = true;
                break;
            case 8:
                $arrAttr['rotation'] = M_PI / 2; // 90
                //$flip = false;
                break;
            case 3:
            case 4:
                $arrImg['rotation'] = 180;
                break;
            case 5:
            case 6:
                $arrImg['rotation'] = 270;
                break;
            case 7:
            case 8:
                $arrImg['rotation'] = 90;
                break;
            default;
                $arrImg['rotation'] = 0;
                break;
        }
    }

    //calculateimage dimensions    
    if (is_array($imgHeader['IFD0']) && array_key_exists('XResolution', $imgHeader['IFD0']) && array_key_exists('YResolution', $imgHeader['IFD0'])) {
        eval("\$xres = " . $imgHeader['IFD0']['XResolution'] . ';');
        eval("\$yres = " . $imgHeader['IFD0']['YResolution'] . ';');
        $orgwidth = intval(round(($imgHeader['IFD0']['ImageWidth'] / $xres) * 72));
        $orgheight = intval(round(($imgHeader['IFD0']['ImageLength'] / $yres) * 72));
    } else if (is_array($imgHeader['IFD0']) && array_key_exists('ImageWidth', $imgHeader['IFD0']) && array_key_exists('ImageLength', $imgHeader['IFD0'])) {
        $orgwidth = $imgHeader['IFD0']['ImageWidth'];
        $orgheight = $imgHeader['IFD0']['ImageLength'];
    } else {
        $arrAttr['url'] = $global['baseurl'] . 'fileadmin/scripts/imgpipe.php?url=' . urlencode($arrAttr['url']);
        //try getimagesize
        $arrTmp = @getimagesize($arrAttr['url']);
        $orgwidth = $arrTmp[0];
        $orgheight = $arrTmp[1];
    }

    //in DIN A4 einpassen, verkleinern, drehen
    if ($orgwidth > $orgheight) {
        $factor = max($orgwidth / $maxheight, $orgheight / $maxwidth);
        $arrAttr['rotation'] = M_PI / 2;
    } else {
        $factor = max($orgwidth / $maxwidth, $orgheight / $maxheight);
    }

    if ($factor > 1) {
        $arrAttr['plainwidth'] = intval(round($orgwidth / $factor));
        $arrAttr['plainheight'] = intval(round($orgheight / $factor));
    } else {
        $arrAttr['plainwidth'] = $orgwidth;
        $arrAttr['plainheight'] = $orgheight;
    }
    if ($orgwidth > $orgheight) {
        $arrAttr['absolutey'] = intval(($maxheight - $arrAttr['plainwidth']) / 2) + $global['marginImage'];
        $arrAttr['absolutex'] = intval(($maxwidth - $arrAttr['plainheight']) / 2) + $global['marginImage'];
    } else {
        $arrAttr['absolutey'] = intval(($maxheight - $arrAttr['plainheight']) / 2) + $global['marginImage'];
        $arrAttr['absolutex'] = intval(($maxwidth - $arrAttr['plainwidth']) / 2) + $global['marginImage'];
    }

    if ($global['imgpipe']) {
        $arrAttr['url'] = $global['baseurl'] . 'fileadmin/scripts/imgpipe.php?url=' . urlencode($arrAttr['url']);
    }
    return $arrAttr;
}

function setNSprefix(&$xpath, $node = false) {
    if (!$node) {
        $xqueryList = $xpath->evaluate('*[1]');
        if ($xqueryList->length) {
            setNSprefix($xpath, $xqueryList->item(0));
        }
    }
    if (is_object($node)) {
        if ($node->prefix) {
            $xpath->registerNamespace(strtolower($node->prefix), $node->namespaceURI);
        }
        $xqueryList = $xpath->evaluate('following-sibling::*[name()!="' . $node->nodeName . '"][1]', $node);
        if ($xqueryList->length) {
            setNSprefix($xpath, $xqueryList->item(0));
        }
        if ($node->firstChild) {
            setNSprefix($xpath, $node->firstChild);
        }
        if ($node->attributes->length) {
            foreach ($node->attributes as $attribute) {
                if ($attribute->prefix && !$arrNS[strtolower($attribute->prefix)]) {
                    $xpath->registerNamespace(strtolower($attribute->prefix), $attribute->namespaceURI);
                }
            }
        }
    }
    unset($xqueryList);
    unset($node);
    unset($attribute);
}

function getTitle($strTitle) {
    global $global;
    $title = $global['iText']->createElement('title');
    $text = new DOMText(trim($strTitle));
    $title->appendChild($text);
    $title->setAttribute('size', '0.0001');
    $title->setAttribute('color', 'white');
    return $title;
}

function getImage($arrImg) {
    global $global;
    $image = $global['iText']->createElement('image');
    if (is_array($arrImg)) {
        foreach ($arrImg as $attribute => $value) {
            $image->setAttribute($attribute, trim($value));
        }
    }
    return $image;
}

function getSection($section, $depth, $title = false) {
    global $global;
    if ($section->hasAttribute('depth')) {
        $last = trim($section->getAttribute('depth'));
    } else {
        $last = -1;
    }
    $newsection = $global['iText']->createElement('section');
    $newsection->setAttribute('numberdepth', '0');
    $newsection->setAttribute('depth', trim($depth));
    //the first section
    if ($section === false) {
        return $newsection;
    }

    // new section on same level
    if ($depth == $last) {
        $section->parentNode->appendChild($newsection);
    }

    // new section on lower level
    if ($depth > $last) {
        $section->appendChild($newsection);
    }

    // new section on higher level
    if ($depth < $last) {
        $count = $last - $depth;
        while ($count && $section->parentNode) {
            $node = $section->parentNode;
            $count--;
        }
        $node->parentNode->appendChild($newsection);
    }

    if ($title) {
        $newsection->appendChild($title);
    }
    return $newsection;
}

function enc_str($str) {
    return str_replace('/', '|', trim($str));
}

function dec_str($str) {
    return str_replace('|', '/', trim($str));
}

//#### END FUNCTIONS ##########################################
?>
