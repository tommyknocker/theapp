;<?php die(); ?>
; App configuration file
; reserved keys: null, yes, no, true, false, on, off, none 
; do not use that chars in keys or values: ?{}|&~![()^"
; section format is [hostname] where dots are replaced by underlines
; [default] section is reserved and will be overwritten by [hostname] section

[default]

db.enabled = true
db.host = "localhost"
db.port = "3306"
db.login = "theapp"
db.password = "theapp"
db.name = "theapp"

daemon.delay = 500