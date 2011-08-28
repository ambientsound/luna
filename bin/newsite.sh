#!/bin/sh
#
# This script generates a LUNA site directory structure.
#
# LUNA content management system
# Copyright (c) 2011, Kim Tore Jensen
# All rights reserved.
#
# Licenced under the three-clause BSD license - see licence.txt.

basedir=$1
shift
lunadir=$1

if [ "$basedir" == "" ] || [ "$lunadir" == "" ]; then
	echo "Usage: $0 base-directory luna-directory"
	exit 1
fi

echo "Setting up a LUNA site in `pwd`/$basedir"
echo "LUNA directory in $lunadir"

mkdir -pv $basedir
mkdir -pv $basedir/data/{smarty,logs}
mkdir -pv $basedir/data/smarty/compile
mkdir -pv $basedir/library
mkdir -pv $basedir/public/{admin,media}
mkdir -pv $basedir/{front,admin}/{models,forms,controllers,configs,i18n,templates}
mkdir -pv $basedir/front/templates/{pages,layouts}

echo "Database config in $basedir/front/configs/database.ini"
[ ! -f "$basedir/front/configs/database.ini" ] &&
echo '[main]
adapter                 = pdo_pgsql
params.host             = 127.0.0.1
params.username         = 
params.password         = 
params.dbname           = 
' > $basedir/front/configs/database.ini

ln -sv ../../front/configs/database.ini $basedir/admin/configs/database.ini
ln -sv $lunadir/public/admin/include $basedir/public/admin/include
ln -sv $lunadir/public/admin/index.php $basedir/public/admin/index.php
ln -sv $lunadir/public/admin/.htaccess $basedir/public/admin/.htaccess
ln -sv $lunadir/public/index.php $basedir/public/index.php
ln -sv $lunadir/public/.htaccess $basedir/public/.htaccess

echo "Done. Make sure the following directories are writable by the web server:"

echo "$basedir/data"
echo "$basedir/data/smarty"
echo "$basedir/data/smarty/compile"
echo "$basedir/data/logs"
