#!/bin/sh

phpdoc -o HTML:frames:earthli -t docs -dn Prenotown -pp -d admin,assets,models,plg_xsecauth,views -f controller.php,prenotown.php
