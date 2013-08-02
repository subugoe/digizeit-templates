# Menu left cObject
temp.footmenu = HMENU
temp.footmenu {
    special = directory
    special.value = 215
	# First level menu-object, textual
	1 = TMENU
	1 {
		# Normal state properties
		NO = 1
		NO.allWrap = <li>|</li>
		ACT < .NO
		RO < .NO
	}
}
