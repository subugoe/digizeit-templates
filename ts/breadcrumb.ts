[PIDinRootline = 239]
    temp.breadcrumb = COA
    temp.breadcrumb {
        5 = TEXT
        # Default language (german)
        5 {
            value (
                <div id="welcome">
					<p><strong>Unser Service - Ihr Vorteil:</strong>  DigiZeitschriften ist ein Service f&uuml;r das wissenschaftliche Arbeiten. &Uuml;ber einen kontrollierten Nutzerzugang k&ouml;nnen Studierende und Wissenschaftler auf Kernzeitschriften der deutschen Forschung zugreifen. Der Zugang erfolgt &uuml;ber Bibliotheken und wissenschaftliche Einrichtungen, die DigiZeitschriften subskribiert haben.</p>
					<div id="cnose"></div>
				</div>
            )
            #other languages (english)
            lang {
                en (
                    <div id="welcome">
                        <p><strong>Our service - your benefit:</strong> DigiZeitschriften is a research service. Students and researchers can access the core German research journals via subscribing institutions. Access is possible via libraries and academic institutions which have subscribed to DigiZeitschriften</p>
                        <div id="cnose"></div>
                    </div>
                )
            }
        }
    }
[else]
    temp.breadcrumb = COA
    temp.breadcrumb {
        5 = TEXT
        # Default language (german)
        5 {
            value (
                <div id="breadcrumb">
                    <dl>
                        <dt>Sie sind hier:</dt>
            )
            #other languages (english)
            lang {
                en ( 
                    <div id="breadcrumb">
                        <dl>
                            <dt>You are here:</dt>
                )
            }
        }
        10 = HMENU
        10 {
            special = rootline
            special.range = 2|-1
            1 = TMENU
            1 {
                NO.doNotLinkIt = |*| 0 |*| 1
                NO.allWrap = |*| <dd>|<span class="breadsep">&gt;</span></dd> |*| <dd>|</dd>
            }
        }
        15 = TEXT
        15.value (
                </dl>
                <div id="cnose"></div>
            </div>
        )
    }
[end]
