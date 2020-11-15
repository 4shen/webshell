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
# for reverse ssh connections
# sshback client machine: run openssh-server on
# sshback sever machine: run openssh-client on
# NOTE: "Server_Common_Name" must be able to DNS resolve
#       on the client machine, e.g.
# $ host www.servercommonname.com
# www.servercommonname.com has address xxx.xxx.xxx.xxx
NOW="Jan 1 00:00:00 1970 GMT +$(date +%s) seconds"
ONE_YEAR_AGO="Jan 1 00:00:00 1970 GMT +$(( $(date +%s) - 3600 * 24 * 365 )) seconds"

KEYSIZE=4096

Server_Country_Name=''
Server_State_or_Province_Name=''
Server_Locality_Name=''
Server_Organization_Name=''
Server_Organizational_Unit_Name=''
Server_Common_Name='www.servercommonname.com'

Client_Country_Name=''
Client_State_or_Province_Name=''
Client_Locality_Name=''
Client_Organization_Name=''
Client_Organizational_Unit_Name=''
Client_Common_Name='client.common.name'

# If your getting invalid cert errors, the server or client
# may not be at the correct date/time, this will widen the
# time gap
WIDE_TIME_GAP=false
if $WIDE_TIME_GAP; then
	echo -e "sudo date --set=\"$ONE_YEAR_AGO\""
	sudo date --set="$ONE_YEAR_AGO"
fi

FILES_ALREADY_EXIST=0
if [ -e server.pem ]; then 
	echo 'FOUND FILE server.pem'; 
	FILES_ALREADY_EXIST=1
fi
if [ -e server.crt ]; then 
	echo 'FOUND FILE server.crt'; 
	FILES_ALREADY_EXIST=1
fi
if [ -e client.pem ]; then 
	echo 'FOUND FILE client.pem'; 
	FILES_ALREADY_EXIST=1
fi
if [ -e client.crt ]; then 
	echo 'FOUND FILE client.crt'; 
	FILES_ALREADY_EXIST=1
fi
if [ $FILES_ALREADY_EXIST -eq 1 ]; then
	while true; do
		echo 'Do you want to overwrite file(s)?'
		echo '	"rm -f server.pem server.crt client.pem client.crt"'
		echo '(Y/N)'
		read OVERWRITE;
		if [ "$OVERWRITE" = "Y" ]; then
			echo 'rm -f server.pem server.crt client.pem client.crt'
			rm -f server.pem server.crt client.pem client.crt
			echo 'REMOVED.'
			sleep 2;
			break;
		fi
		if [ "$OVERWRITE" = "N" ]; then
			echo 'Move or Rename or Backup Your Certs and Try Again.';
			exit 1;
		fi
	done
fi

# _______
#|     __|.-----.----.--.--.-----.----.
#|__     ||  -__|   _|  |  |  -__|   _|
#|_______||_____|__|  \___/|_____|__|
#
FILENAME=server
echo "[+][+][+][+][+][+] Creating $FILENAME..."
Server_SUBJ='/C='"$Server_Country_Name"'/ST='"$Server_State_or_Province_Name"'/L='"$Server_Locality_Name"'/O='"$Server_Organization_Name"\
'/OU='"$Server_Organizational_Unit_Name"'/CN='"$Server_Common_Name"'/'
openssl genrsa -out $FILENAME.key $KEYSIZE
openssl req -new -key $FILENAME.key -x509 -subj "$Server_SUBJ" -days 3650 -out $FILENAME.crt
cat $FILENAME.key $FILENAME.crt > $FILENAME.pem
chmod 600 $FILENAME.key $FILENAME.pem
rm $FILENAME.key

# ______ __ __               __
#|      |  |__|.-----.-----.|  |_
#|   ---|  |  ||  -__|     ||   _|
#|______|__|__||_____|__|__||____|
#
FILENAME=client

echo "[+][+][+][+][+][+] Creating $FILENAME..."
Client_SUBJ='/C='"$Client_Country_Name"'/ST='"$Client_State_or_Province_Name"'/L='"$Client_Locality_Name"'/O='"$Client_Organization_Name"\
'/OU='"$Client_Organizational_Unit_Name"'/CN='"$Client_Common_Name"'/'
openssl genrsa -out $FILENAME.key $KEYSIZE
openssl req -new -key $FILENAME.key -x509 -subj "$Client_SUBJ" -days 3650 -out $FILENAME.crt
cat $FILENAME.key $FILENAME.crt > $FILENAME.pem
chmod 600 $FILENAME.key $FILENAME.pem
rm $FILENAME.key

echo 'Make sure ntpdate is installed on client and server'
ls -lash server.pem
ls -lash server.crt
ls -lash client.pem
ls -lash client.crt
if $WIDE_TIME_GAP; then
	echo -e "sudo date --set=\"$NOW\""
	sudo date --set="$NOW"
fi
exit 0

