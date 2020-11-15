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
# sshback sever machine
SSH_USERNAME=sshusername
SERVER_PEM=/path/to/server.pem
CLIENT_CRT=/path/to/client.crt
LOCAL_LOOPBACK_PORT=1111
LOCAL_SSL_SERVER_PORT=4443
if [ -z "$(dpkg-query -l socat | grep -o '^ii  socat')" ]; then echo '# apt-get(/yum/pacman) install socat'; exit; fi
/usr/bin/xterm -e "while true; do "\
"socat -d -d openssl-listen:$LOCAL_SSL_SERVER_PORT,reuseaddr,cert='$SERVER_PEM',cafile='$CLIENT_CRT' tcp4-listen:$LOCAL_LOOPBACK_PORT; "\
"sleep 15; "\
"done" &
while true; do ssh -q -p $LOCAL_LOOPBACK_PORT "$SSH_USERNAME"@127.0.0.1; sleep 3; done
