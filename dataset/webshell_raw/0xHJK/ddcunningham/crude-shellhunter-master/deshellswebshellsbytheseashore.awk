#!/bin/awk
#v1.0, Authored by David Cunningham

BEGIN { FS = "\\?><"; bound = 3; logprefixpath = "/var/log/crude-shellhunter"}
{
  #If line matches shell pattern, and is on the first line, and contains only webshell fields (as seen in every example), clear the line..
  if($0 ~ /<\?php if\(!isset\(\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]\)\).*\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]=1\; \} \?><\?php.*?>/ && NR == 1 && NF < 3) 
    {print "======\n""Removed line 1, as it was all shellcode:",FILENAME,". See diff for changes.\n======" >> logprefixpath"/run.log"}
  #If line matches shell pattern, and is on the first line, modify the file in place, and log what was changed.
  else if($0 ~ /<\?php if\(!isset\(\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]\)\).*\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]=1\; \} \?><\?php.*?>/ && NR == 1 && NF >= 3) 
    {print "======\n""Removed shellcode from line 1:",FILENAME,". See diff for changes.\n======" >> logprefixpath"/run.log"; 
      for(i=bound; i<=NF; i++) printf("%s%s%s%s","<", $(i), i<NF ? OFS : "\n", i<NF ? i>bound ? "?>": "" : "") > FILENAME".tmp" }
  #If line matches shell pattern, but is not on the first line, just log it.
  else if($0 ~ /<\?php if\(!isset\(\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]\)\).*\$GLOBALS\[\"\\x61\\156\\x75\\156\\x61\"\]=1\; \} \?><\?php.*?>/) 
    { print "======\n""Found additional shellcode, logging only:",FILENAME,", line",NR".\n\nShellcode is:", $0"\n======" >> logprefixpath"/run.log"; print $0> FILENAME".tmp"}
  else 
    print $0 > FILENAME".tmp"
}

