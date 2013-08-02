
includeLibs.FElogin = fileadmin/scripts/FElogin.php
temp.felogin = USER_INT
temp.felogin {
	userFunc = user_FElogin->main
	storagePid = {$LICENSEUSER}
	item_username.de = Benutzer
	item_username.en = username
	item_password.de = Passwort
	item_password.en = password
	item_register.de = registrieren
	item_register.en = register
	item_lostpass.de = Passwort vergessen?
	item_lostpass.en = Forgot your password?
	item_lang.de = de
	item_lang.en = en
}

page.10.marks.FELOGIN < temp.felogin
