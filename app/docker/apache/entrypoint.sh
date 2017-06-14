#!/usr/bin/env bash

set -e

export ORIGPASSWD=$(cat /etc/passwd | grep www-data)
export ORIG_UID=$(echo ${ORIGPASSWD} | cut -f3 -d:)
export ORIG_GID=$(echo ${ORIGPASSWD} | cut -f4 -d:)

export DEV_UID=${DEV_UID:=${ORIG_UID}}
export DEV_GID=${DEV_GID:=${ORIG_GID}}

usermod -u $DEV_UID www-data
groupmod -g $DEV_GID www-data

/usr/sbin/apache2ctl start

tail -f /dev/null