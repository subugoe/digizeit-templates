#Main Pagetitle
temp.maincaption = COA
temp.maincaption {
    10 = HTML
    10.value = <h3>
    20 = TEXT
    20.field = subtitle // title
    30 = HTML
    30.value = </h3>
}
[globalVar = TSFE:id=239]
    temp.maincaption >
[global]

# Default PAGE object:
page = PAGE
page.typeNum = 0
page.config.metaCharset = utf-8
page.config.additionalHeaders = Content-Type:text/html;charset=utf-8

# FE-User to BE-User
page.headerData.10 < plugin.tx_simulatebe_pi1

# Meta tags
page.headerData.999 < plugin.meta

page.includeCSS {
    file1 =  fileadmin/css/digi_layout.css
    file1.rel = stylesheet
    file1.type = text/css
    file1.media = screen,projection

    file2 = fileadmin/css/digi_content.css
    file2.rel = stylesheet
    file2.type = text/css
    file2.media = screen,projection
}
[browser = msie] AND [version= <7]
    page.includeCSS {
        file3 = fileadmin/css/digi_ie6.css
        file3.rel = stylesheet
        file3.type = text/css
        file3.media = screen,projection
    }
[global]

page.bodyTag = <body>

page.10 = TEMPLATE
page.10.template = FILE

# ALTERNATIVES SEITENLAYOUT
#  ***************************************************
[globalVar=TSFE:page|layout=0]
page.10.template.file = fileadmin/html/digizeit_main_tmpl.html
[globalVar=TSFE:page|layout=1]
page.10.template.file = fileadmin/html/digizeit_main_full_tmpl.html
[globalVar=TSFE:page|layout=2]
page.10.template.file = fileadmin/html/digizeit_main_search_tmpl.html
[globalVar=TSFE:page|layout=3]
page.10.template.file = fileadmin/html/digizeit_main_full_toc_tmpl.html
[global]


#[globalVar = TSFE:id=239, TSFE:id=227]
#    page.10.marks.HLINE = TEXT
#    page.10.marks.HLINE.value = <div id="newsicon"></div>
#[else]
    page.10.marks.HLINE = TEXT
    page.10.marks.HLINE.value =
#[end]

temp.printview = TEXT 
temp.printview { 
    # Default language (german)
    value = Druckansicht
    #other languages (english)
    lang {
        en = printable view
    }
    typolink.parameter.data = page:uid
    typolink.additionalParams.insertData=1
    typolink.additionalParams =&type=98
    typolink.ATagParams = target="_blank"
}


page.10 {

    marks {
        #Link tart- to startpage
        START = TEXT
        START {
            # Default language (german)
            value = /
            #other languages (english)
            lang.en = /en/
        }

        # to top link
        TOTOPLINK = TEXT
        TOTOPLINK {
            value = <a href="javascript:window.scrollTo(0,300)" id="totop">nach oben</a>
            lang.en = <a href="javascript:window.scrollTo(0,300)" id="totop">up</a>
/*
            # Default language (german)
            value = nach oben
            #other languages (english)
            lang.en = up
            typolink.parameter.data = TSFE:id
            typolink.addQueryString = 1
            typolink.addQueryString.method = GET,POST
            typolink.addQueryString.exclude = xajax,xajaxr,xajaxargs
            typolink.title = nach oben
            typolink.title.lang.en = up
            typolink.ATagParams = id="totop"
*/
        }

        #DFG-Link
        DFGLINKTEXT = TEXT
        DFGLINKTEXT {
            value = DigiZeitschriften wird durch die Deutsche Forschungsgemeinschaft gefördert.
            lang.en = DigiZeitschriften is supported by Deutsche Forschungsgemeinschaft
        }

        # Introtext suche
        SEARCHINTROTEXT = TEXT
        SEARCHINTROTEXT {
            value = Suchen Sie hier im Archiv nach Zeitschriften.
            lang.en = Search the DigiZeitschriften archive.
        }

        # Direktsuche
        DIREKTSUCHE = TEXT
        DIREKTSUCHE {
            value = Direktsuche
            lang.en = Search
        }

        # Druckansicht
        printview < temp.printview

    }

    # Select only the content between the <body>-tags
    workOnSubpart = DOCUMENT_BODY

    # Substitute the ###menu### subparts with dynamic menu
    subparts.menu < temp.menu

    # Substitute the ###menu### subparts with dynamic menu
    subparts.breadcrumb < temp.breadcrumb

    # Substitute the ###tabmenu### subparts with nothing
    subparts.submenu <  temp.submenu

    # Substitute the ###maincaption### subpart:
    subparts.maincaption < temp.maincaption

    # Substitute the ###maincontent### subpart :
    subparts.maincontent < styles.content.get
#	subparts.maincontent < temp.maincontent

    # Substitute the ###maincaption### subpart:
    subparts.sidebar < styles.content.getRight

    # Substitute the ###footmenu### subparts with dynamic menu
    subparts.footmenu < temp.footmenu

    # Substitute the ###lang_selector### subpart :
    subparts.lang_selector < temp.language_selector
}

[globalVar = TSFE:type=100]
    config.admPanel = 0
    xmlnews = PAGE
    xmlnews {
        typeNum = 100
        10 >
        10 < plugin.tt_news
    #    10.pid_list >
    #    10.pid_list = 88
        10.singlePid = 227
        10.backPid = 239
        10.defaultCode = XML
        config {
            disableAllHeaderCode = 1
            additionalHeaders = Content-type:text/xml
    #        no_cache = 1
            xhtml_cleaning = 0
        }
    }
[end]

 print = PAGE 
 print.typeNum = 98 
# Meta tags
page.headerData.999 < plugin.meta
print.stylesheet = fileadmin/css/digi_print.css 
 print.10 = TEMPLATE 
 print.10 {
    template = FILE 
    template.file = fileadmin/html/digizeit_print_tmpl.html
    workOnSubpart = DOCUMENT_BODY 
    subparts {
        CONTENT < styles.content.get
    }
    marks {
        TOPGFX = IMAGE
        TOPGFX.file = fileadmin/layout/logo_print.jpg
    }
 } 
