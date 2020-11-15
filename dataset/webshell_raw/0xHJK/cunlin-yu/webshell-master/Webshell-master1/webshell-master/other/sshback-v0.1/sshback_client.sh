#!/bin/bash
#              __     ______              __
#.-----.-----.|  |--.|   __ \.---.-.----.|  |--.
#|__ --|__ --||     ||   __ <|  _  |  __||    <
#|_____|_____||__|__||______/|___._|____||__|__|
#Copyright (C) 2014
#
#This program is free software; you can redistribute it and/or
#modify it under the terms of the GNU General Public License
#as published by the Free Software Foundation; either version 2
#of the License, or (at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
# This is for:
# sshback client machine
# To run automatically, add to the /etc/rc.local file
# /path/to/sshback_client.sh &
SERVER_HOSTNAME="www.servercommonname.com"
SERVER_PORT=4443
CLIENT_PEM=/path/to/client.pem
SERVER_CRT=/path/to/server.crt
last=0;
if [ -z "$(dpkg-query -l socat | grep -o '^ii  socat')" ]; then echo '# apt-get(/yum/pacman) install socat'; exit; fi
while true; do
	now=$(date +%s);
	if [ $(($last + 60  )) -le $now ]; then
		socat openssl-connect:"$SERVER_HOSTNAME":"$SERVER_PORT",cert="$CLIENT_PEM",cafile="$SERVER_CRT" tcp4:127.0.0.1:22 2> /dev/null;
		last=$now;
		sleep 1;
	else
		sleep 1;
	fi;
done
