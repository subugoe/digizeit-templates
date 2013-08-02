# Menu left cObject
temp.menu = HMENU
temp.menu {
#    special = directory
#    special.value = 30
    special = list
    special.value = 239,59,51,290,238,31,32,139
	# First level menu-object, textual
	1 = TMENU
	1 {
		# Normal state properties
		NO.linkWrap = <li> | </li>

		ACT = 1
        ACT.ATagParams = class="act"
		ACT.linkWrap = <li> | </li>
	}
}

