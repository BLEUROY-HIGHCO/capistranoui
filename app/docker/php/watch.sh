#!/usr/bin/env bash

VENDOR_DIRECTORY=/var/www/vendor
if [ -d "$VENDOR_DIRECTORY" ]; then
	php bin/console assets:install --symlink --relative
else
	echo "composer install is needed : make composer-install"
fi

exit
