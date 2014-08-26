#!/bin/sh

curdir=$(cd "${0%/*}" 2>/dev/null; echo "$PWD")

cd $curdir/admin
tar cf - --exclude=swp . | (cd ../../../../administrator/components/com_prenotown; tar xf -)
cp it-IT.com_prenotown*ini ../../../../administrator/language/it-IT/
cp en-GB.com_prenotown*ini ../../../../administrator/language/en-GB/
cd ..
tar cf - --exclude=swp --exclude=admin . | (cd ../../../components/com_prenotown; tar xf -)
cp it-IT.com_prenotown*ini ../../../language/it-IT/
cp en-GB.com_prenotown*ini ../../../language/en-GB/
chown -R apache.apache /var/www/html/prenotown/trezzo/
cp ../plg_xsecauth/xsec.php ../../../plugins/authentication/
cp ../plg_xsecuser/xsec.php ../../../plugins/user/
