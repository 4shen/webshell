#!/usr/bin/env python
# This file is part of Weevely NG.
#
# Copyright(c) 2011-2012 Weevely Developers
# http://code.google.com/p/weevely/
#
# This file may be licensed under the terms of of the
# GNU General Public License Version 2 (the ``GPL'').
#
# Software distributed under the License is distributed
# on an ``AS IS'' basis, WITHOUT WARRANTY OF ANY KIND, either
# express or implied. See the GPL for the specific language
# governing rights and limitations.
#
# You should have received a copy of the GPL along with this
# program. If not, go to http://www.gnu.org/licenses/gpl.html
# or write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.


from core.terminal import Terminal, module_trigger
from core.modules_handler import ModHandler
from core.module import ModuleException
from core.helper import Helper

import sys
    
#print '''
#Weevely 0.6 - Generate and manage stealth PHP backdoors
#              Emilio Pinna 2011-2012            
#'''

print '''      ________                      __ 
     |  |  |  |-----.----.-.--.----'  |--.--.
     |  |  |  |  -__| -__| |  | -__|  |  |  | 
     |________|_____|____|___/|____|__|___  | v0.7
                                      |_____| 
              Stealth tiny web shell 
'''


credits = '''
Website                    
                   http://epinna.github.com/Weevely/

Author                     
                   Emilio Pinna 
                   http://disse.cting.org

Contributors            
                   Raffaele Forte
                   Backbox Linux
                   http://www.backbox.org
                   Simone Margaritelli
                   http://www.evilsocket.net/
'''
   
general_usage = '''[+] Start telnet-like session
    weevely <url> <password> 
  
[+] Run shell command o module
    weevely <url> <password> [ <command> | :<module name> ]  ..

[+] Generate PHP backdoor
    weevely generate <password> [ <path> ] ..  

[+] Show modules help
    weevely show [module name]
    
[+] Show credits
    weevely credits
  
Available generators

%s
Available modules

%s'''  
    
if __name__ == "__main__":

    
    if  len(sys.argv) == 3 and sys.argv[1].startswith('http'):
        
        print "[+] Starting terminal, shell probe may take a while"
        
        url = sys.argv[1]
        password = sys.argv[2]
          
        try:
            Terminal ( ModHandler( url, password ) ).loop()
        except ModuleException, e:
            print e
        except KeyboardInterrupt:
            print '\n[!] Exiting. Bye ^^'
        
    elif len(sys.argv) >= 3 and sys.argv[1].startswith('generate'):
        
        genname = sys.argv[1] 
        password = sys.argv[2]
        
        if genname == 'generate':
            genname = 'generate.php'
        
        args_list = [':%s' % genname ] + sys.argv[3:]
        
        try:
            Terminal (ModHandler(genname, password), True).run_module_cmd(args_list)
        except ModuleException, e:
            print '[!] [%s] %s ' % (e.module, e.error)
            
    elif len(sys.argv)>=2 and sys.argv[1] == 'show':
        modname = None
        if len(sys.argv) == 3:
            modname = sys.argv[2]
        print ModHandler('', '').helps(modname)
        
    elif len(sys.argv) > 3:

        url = sys.argv[1]
        password = sys.argv[2]        
        
        
        if sys.argv[3] == ':show':
            modname = ''
            if len(sys.argv)>4:
                modname = sys.argv[4]
            print ModHandler(url, password).helps(modname)
            
        elif sys.argv[1].startswith('http') or sys.argv[3] == ':set':
        
            try:
                terminal = Terminal (ModHandler(url, password), True)
                
                if sys.argv[3][0] == module_trigger:
                    terminal.run_module_cmd(sys.argv[3:])
                else:
                    terminal.run_line_cmd(' '.join(sys.argv[3:]))
                
            except ModuleException, e:
                print e
            except KeyboardInterrupt:
                print '\n[!] Exiting. Bye ^^'

    elif len(sys.argv)==2 and sys.argv[1] == 'credits':
        print credits
                
        
    else:
        
        print general_usage % ( ModHandler().summary(only_group='generate'), ModHandler().summary(exclude_group='generate'))
        
    
