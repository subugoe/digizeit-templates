###############################################################################
### Global config #############################################################
###############################################################################
config {
	xmlprologue = none
	doctype = xhtml_trans

	no_cache = 0

	admPanel = 1

	removeDefaultJS = 0

    #cache_clearAtMidnight = 1

	#Kommentare ausblenden
	disablePrefixComment = 1

	#wandelte alle CSS in Files um
	inlineStyle2TempFile = 1
	disableImgBorderAttr = 1
	index_enable = 1
	#spamProtectEmailAddresses = 1

	simulateStaticDocuments = 0
	baseURL = {$BASEURL}
	tx_realurl_enable = 1
    #### dr_wiki ####
 	inlineStyle2TempFile = 1
    # the next line is necessary to make the anchors work again    
 	prefixLocalAnchors = output
    #### dr_wiki ####

	# Es wedern nur übersetzte Inhalte angezeigt, oder
	# (Default)inhalte die mit All Languages
	# gekennzeichnet sind

	sys_language_mode = content_fallback
	# Es wedern nur übersetzte Inhalte angezeigt, oder
	# (Default)inhalte die mit All Languages
	# gekennzeichnet sind
	sys_language_overlay = hideNonTranslated

	# default Language (german)
	linkVars = L
	sys_language_uid = 0
	language = de
	locale_all = de_DE.UTF-8
# Stupid solaris config
#	locale_all = de.UTF-8

	htmlTag_langKey = de

    uniqueLinkVars = 1

    tx_piwik {
        piwik_host = http://piwik.gwdg.de/
        piwik_idsite = 142
        #????
        trackGoal = 1
        setDomains = 92.51.150.136,*.digizeitschriften.de,*.digizeitschriften.org,resolver.sub.uni-goettingen.de
        setDownloadClasses = maintitle_pdf,sitepdf
    }
}


# alternative Language (english)
[globalVar=GP:L=2]
	config.sys_language_uid = 2
	config.language = en
	config.locale_all = en_EN.utf-8
[end]


#Language selector
temp.language_selector = PHP_SCRIPT
temp.language_selector.file = fileadmin/scripts/languageSelector.php


#Additional header
page.meta {
    keywords.field = keywords
    keywords.ifEmpty ( 
       DigiZeitschriften,Zeitschriften,Journals,digital,online,Portal,Wissenschaft,Science,Anglistik,Buchwesen,Bibliothekswesen,Erziehungswissenschaften,Geowissenschaften,Germanistik,Geschichte,Kunst,Mathematik,Musikwissenschaft,Naturwissenschaften,Neuere Philologien,Rechtswissenschaften,Religion,Romanistik,Soziologie,Wirtschaftswissenschaften
    )
    description.field = description
    description.ifEmpty ( 
         DigiZeitschriften - Das deutsche digitale Zeitschriftenarchiv - stellt deutsche Kernzeitschriften von großer wissenschaftlicher Bedeutung für den direkten Nutzerzugriff über das WWW bereit. Die Zeitschriften stammen aus folgenden Fachgebieten: Anglistik, Buchwesen, Bibliothekswesen, Erziehungswissenschaften, Geowissenschaften, Germanistik, Geschichte, Kunst, Mathematik, Musikwissenschaft, Naturwissenschaften, Neuere Philologien, Rechtswissenschaften, Religion, Romanistik, Soziologie, Wirtschaftswissenschaften 
    )
    robots = INDEX,FOLLOW

    copyright = DigiZeitschriften e.V
    author = Jochen Kothe    
}

#RSS Autodiscovery
page.headerData {
    10 = TEXT
    10.value = <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports" href="http://www.digizeitschriften.de/rss/standard" />
    20 = TEXT
    20.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Arts" href="http://www.digizeitschriften.de/rss/700" />
    30 = TEXT
    30.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Economics" href="http://www.digizeitschriften.de/rss/330" />
    40 = TEXT
    40.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Education" href="http://www.digizeitschriften.de/rss/370" />
    50 = TEXT
    50.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Egyptology and coptology" href="http://www.digizeitschriften.de/rss/962" />
    60 = TEXT
    60.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: English language and literature" href="http://www.digizeitschriften.de/rss/420" />
    70 = TEXT
    70.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Geology" href="http://www.digizeitschriften.de/rss/550" />
    80 = TEXT
    80.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Germanic language and literature" href="http://www.digizeitschriften.de/rss/430" />
    90 = TEXT
    90.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: History" href="http://www.digizeitschriften.de/rss/900" />
    100 = TEXT
    100.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Law" href="http://www.digizeitschriften.de/rss/340" />
    110 = TEXT
    110.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Librarianship" href="http://www.digizeitschriften.de/rss/020" />
    120 = TEXT
    120.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Mathematics" href="http://www.digizeitschriften.de/rss/510" />
    130 = TEXT
    130.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Music" href="http://www.digizeitschriften.de/rss/780" />
    140 = TEXT
    140.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Oriental Studies" href="http://www.digizeitschriften.de/rss/953" />
    150 = TEXT
    150.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Philology" href="http://www.digizeitschriften.de/rss/400" />
    160 = TEXT
    160.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Philosophy " href="http://www.digizeitschriften.de/rss/100" />
    170 = TEXT
    170.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Religion" href="http://www.digizeitschriften.de/rss/200" />
    180 = TEXT
    180.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Romance language and literature" href="http://www.digizeitschriften.de/rss/440" />
    190 = TEXT
    190.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Sciences" href="http://www.digizeitschriften.de/rss/500" />
    200 = TEXT
    200.value =  <link rel="alternate" type="application/rss+xml" title="DigiZeitschriften - Last imports: Sociology" href="http://www.digizeitschriften.de/rss/300" />

    # Meta Tag fur Google Website Tools
    500 = HTML
    500.value (
        <meta name="google-site-verification" content="sRqPpOhhh-nLRbPb1YBWyGsmO7GMGjeCkhGqolueRj0" />
    )

    # DC Meta tags
    1000 = TEXT
    1000 {
        field = keywords
        keywords.ifEmpty (
        DigiZeitschriften,Zeitschriften,Journals,digital,online,Portal,Wissenschaft,Science,Anglistik,Buchwesen,Bibliothekswesen,Erziehungswissenschaften,Geowissenschaften,Germanistik,Geschichte,Kunst,Mathematik,Musikwissenschaft,Naturwissenschaften,Neuere Philologien,Rechtswissenschaften,Religion,Romanistik,Soziologie,Wirtschaftswissenschaften
        )
        wrap = <meta name="DC.Subject" content="|" />
    }

#   1100 = TEXT
#   1100.dataWrap = <meta name="DC.identifier"
#       content="{getIndpEnv:TYPO3_REQUEST_URL}" scheme="DCTERMS.URI" />
#       #usw.
#   }

}

###############################################################################
###############################################################################
###############################################################################


###############################################################################
### Plugin config #############################################################
###############################################################################

#plugin.tx_drwiki_pi1.templateFile = fileadmin/digizeit/html/digizeit_drwiki_tmpl.html

plugin.tx_drwiki_pi1 {
    _CSS_DEFAULT_STYLE >
    templateFile = fileadmin/html/digizeit_drwiki_tmpl.html
### ACHTUNG: Funktioniert nicht, ist in class.tx_drwiki_pi1.php hardcodiert
#    allowedHTML = <center><a><br><b><h1><h2><h3><h4><h5><h6><img><li><ol><p><strong><table><tr><td><th><u><ul><thead><tbody><tfoot><em><dd><dt><dl><span><div><del><add><i><hr><pre><br><blockquote><address><code><caption><abbr><acronym><cite><dfn><q><ins><sup><sub><kbd><samp><var><tt><small><big>
}

plugin.tx_irfaq_pi1 {
    iconPlus = fileadmin/images/plus.gif
    iconMinus = fileadmin/images/minus.gif
    templateFile = fileadmin/html/digizeit_irfaq_tmpl.html
    _CSS_DEFAULT_STYLE >
}

plugin.tx_ttaddress_pi1 {
    templatePath = fileadmin/html/
    _CSS_DEFAULT_STYLE >
}

#tt_news - Plugin
plugin.tt_news {
    templateFile = fileadmin/html/digizeit_news_tmpl.html
    excludeAlreadyDisplayedNews = 0
    latestLimit = 1
    displaySingle {
        date_stdWrap.strftime= %d.%m.%y
        time_stdWrap.strftime= %d.%m.%y %H:%M
    }
    displayLatest {
        date_stdWrap.strftime= %d.%m.%y
        time_stdWrap.strftime= %d.%m.%y %H:%M
    }
    displayList {
        date_stdWrap.strftime= %d.%m.%y
        time_stdWrap.strftime= %d.%m.%y %H:%M
    }
    _CSS_DEFAULT_STYLE >
    _CSS_DEFAULT_STYLE >
    displayList.subheader_stdWrap {
        append = TEXT
        append.data = register:newsMoreLink
        append.wrap =  <div class=news-list-morelink>|</div>
        append.if.isTrue.field = bodytext
    }
    displayLatest.subheader_stdWrap {
        append = TEXT
        append.data = register:newsMoreLink
        append.wrap =  <div class=news-list-morelink>|</div>
        append.if.isTrue.field = bodytext
    }
}

## korrigiert News-Anzeige nach Update 4.5 -> 4.6
tt_content.list.20.9=CASE
tt_content.list.20.9 {
    key.field = layout
    0 = < plugin.tt_news
}


plugin {
    tx_goobit3_browse {
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_colbrowse_tmpl.html
    }
    tx_goobit3_img {
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_img_tmpl.html
    }
    tx_goobit3_met{
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_met_tmpl.html
    }

    tx_goobit3_pdf {
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_pdf_tmpl.html
    }

    tx_goobit3_search {
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_search_tmpl.html
    }
    tx_goobit3_toc {
        _CSS_DEFAULT_STYLE >
        templatefile = fileadmin/html/goobit3_toc_tmpl.html
    }
}



plugin.tx_felogin_pi1 {
    templateFile = fileadmin/html/digizeit_felogin.html
    _CSS_DEFAULT_STYLE >
    welcomeHeader_stdWrap {
        wrap = <h4>|</h4>
    }
    successHeader_stdWrap {
	    wrap = <h4>|</h4>
    }
    logoutHeader_stdWrap {
	    wrap = <h4>|</h4>
    }
    errorHeader_stdWrap {
	    wrap = <h4>|</h4>
    }
    forgotHeader_stdWrap {
        wrap = <h4>|</h4>
    }

    _LOCAL_LANG.de {
    }
}



###gender, first_name, middle_name, last_name, title, email, phone, mobile, www, address, building, room, birthday, organization, city, zip, region, country, image, fax, description, mainGroup

plugin.tx_ttaddress_pi1 {
  templates.digizeit_abo-address_tmpl {
    last_name.wrap = <h4>|</h4>    
    last_name.required = 1
    image.wrap = |    
    image.required = 1
    address.wrap = <br />|    
    address.required = 1
    zip.wrap = <br />| 
    zip.required = 1
    city.wrap = | 
    city.required = 1
    www.typolink.parameter.field = www
    www.typolink.extTarget = _blank
    www.wrap = <br />|
    www.required = 1
    email.wrap = <br />Email:&nbsp;|
    email.required = 1
    phone.wrap = <br />Tel:&nbsp;|
    phone.required = 1
    fax.wrap = <br />Fax:&nbsp;|
    fax.required = 1
    description.wrap = <br styles="clear: both;">|
    description.required = 1
  } 
}

###############################################################################
###############################################################################
###############################################################################
