# Menu left cObject
temp.submenu = COA_INT
temp.submenu {
    5 = TEXT
    5.value = <ul id="submenu">
    10 = HMENU
    10 {
        entryLevel = {$SUBMENUENTRYLEVEL}
        # First level menu-object, textual
        1 = TMENU
        1 {
            NO.additionalParams.cObject = COA
            NO.additionalParams.cObject {
 
                3 = TEXT
                3.data = GP:pid
                3.if.isTrue.data = GP:pid
                3.wrap = &pid=|

                5 = TEXT
                5.data = GP:PPN
                5.if.isTrue.data = GP:PPN
                5.wrap = &PPN=|
                
                10 = TEXT
                10.data = GP:DMDID
                10.if.isTrue.data = GP:DMDID
                10.wrap = &DMDID=|

                12 = TEXT
                12.data = GP:LOGID
                12.if.isTrue.data = GP:LOGID
                12.wrap = &LOGID=|

                15 = TEXT
                15.data = GP:PHYSID
                15.if.isTrue.data = GP:PHYSID
                15.wrap = &PHYSID=|

                17 = TEXT
                17.data = GP:imagenumber
                17.if.isTrue.data = GP:imagenumber
                17.wrap = &imagenumber=|

                20 = TEXT
                20.data = GP:highlight
                20.if.isTrue.data = GP:highlight
                20.wrap = &highlight=|
                    
            }
            # Normal state properties
            NO.allWrap = |*| <li>|<span class="subnavsep"></span></li> |*| <li>|</li>
    #        NO.allWrap = |*| &nbsp;|&nbsp;<span class="sep"></span> |*| &nbsp;|
    
            ACT = 1
            ACT.additionalParams.cObject = COA
            ACT.additionalParams.cObject {
 
                3 = TEXT
                3.data = GP:pid
                3.if.isTrue.data = GP:pid
                3.wrap = &pid=|

                5 = TEXT
                5.data = GP:PPN
                5.if.isTrue.data = GP:PPN
                5.wrap = &PPN=|
                
                10 = TEXT
                10.data = GP:DMDID
                10.if.isTrue.data = GP:DMDID
                10.wrap = &DMDID=|

                12 = TEXT
                12.data = GP:LOGID
                12.if.isTrue.data = GP:LOGID
                12.wrap = &LOGID=|

                15 = TEXT
                15.data = GP:PHYSID
                15.if.isTrue.data = GP:PHYSID
                15.wrap = &PHYSID=|

                17 = TEXT
                17.data = GP:imagenumber
                17.if.isTrue.data = GP:imagenumber
                17.wrap = &imagenumber=|

                20 = TEXT
                20.data = GP:highlight
                20.if.isTrue.data = GP:highlight
                20.wrap = &highlight=|
                    
            }
    #        ACT.doNotLinkIt = 1
            ACT.ATagParams = class="sub_act"
            ACT.allWrap = |*| <li>|<span class="subnavsep"></span></li> |*| <li>|</li>
    #        ACT.allWrap = |*| &nbsp;|&nbsp;<span class="sep"></span> |*| &nbsp;|
        }
    }
    15 = TEXT
    15.value = </ul>
}

